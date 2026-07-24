<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Guards the /admin area: user must be logged in AND have the
 * `admin.access` permission (via their Shield group).
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');

        if (! $auth->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please sign in first.');
        }

        $user = $auth->user();
        if (! $user->can('admin.access')) {
            return redirect()->to('/')->with('error', 'You do not have permission to access the admin area.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
