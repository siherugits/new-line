<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $auth = service('auth');

        if (! $auth->loggedIn()) {
            return redirect()->to('login');
        }

        // Only send admins to the admin panel. Users without admin access
        // would otherwise bounce between "/" and "/admin" (redirect loop).
        if ($auth->user()->can('admin.access')) {
            return redirect()->to('admin');
        }

        // Logged-in but no admin rights: log out and show a message,
        // rather than looping.
        $auth->logout();

        return redirect()->to('login')
            ->with('error', 'Akun ini tidak memiliki akses ke area admin.');
    }
}
