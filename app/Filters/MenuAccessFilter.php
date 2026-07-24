<?php

namespace App\Filters;

use App\Models\MenuModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Enforces the per-menu role checkboxes (menu_access) at the server level,
 * so a user can only open a page if one of their roles is checked for the
 * menu that points to that URL. Runs after AdminFilter (which already
 * requires login + admin.access).
 */
class MenuAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');

        if (! $auth->loggedIn()) {
            return redirect()->to('/login');
        }

        $user      = $auth->user();
        $roleNames = $user->getGroups();
        $isSuper   = in_array('superadmin', $roleNames, true);

        $uri = trim(uri_string(), '/');

        // The admin dashboard is the landing page for anyone with admin.access
        // (and the redirect target below), so never guard it — would loop.
        if ($uri === 'admin') {
            return;
        }

        if (! (new MenuModel())->canAccessUri($roleNames, $isSuper, $uri)) {
            return redirect()->to('/admin')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
