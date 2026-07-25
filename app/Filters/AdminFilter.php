<?php

namespace App\Filters;

use App\Models\MenuModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Guards the /admin area: user must be logged in AND have at least one
 * menu checked for one of their roles (superadmin always allowed).
 * Access is driven purely by the menu "Visible to roles" checkboxes.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');

        if (! $auth->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please sign in first.');
        }

        $user      = $auth->user();
        $roleNames = $user->getGroups();
        $isSuper   = in_array('superadmin', $roleNames, true);

        if (! (new MenuModel())->hasAnyMenu($roleNames, $isSuper)) {
            $auth->logout();

            return redirect()->to('/login')
                ->with('error', 'Akun ini tidak memiliki menu yang dapat diakses.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
