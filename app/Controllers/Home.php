<?php

namespace App\Controllers;

use App\Models\MenuModel;

class Home extends BaseController
{
    public function index()
    {
        $auth = service('auth');

        if (! $auth->loggedIn()) {
            return redirect()->to('login');
        }

        $user      = $auth->user();
        $roleNames = $user->getGroups();
        $isSuper   = in_array('superadmin', $roleNames, true);

        // Anyone who has at least one visible menu may enter the admin area.
        if ((new MenuModel())->hasAnyMenu($roleNames, $isSuper)) {
            return redirect()->to('admin');
        }

        // No menu at all: log out rather than bounce in a redirect loop.
        $auth->logout();

        return redirect()->to('login')
            ->with('error', 'Akun ini tidak memiliki menu yang dapat diakses.');
    }
}
