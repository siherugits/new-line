<?php

namespace App\Controllers\Admin;

use App\Models\MenuModel;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use CodeIgniter\Shield\Models\UserModel;

class Dashboard extends BaseAdminController
{
    public function index(): string
    {
        $stats = [
            'users'       => (new UserModel())->countAllResults(),
            'roles'       => (new RoleModel())->countAllResults(),
            'permissions' => (new PermissionModel())->countAllResults(),
            'menus'       => (new MenuModel())->countAllResults(),
        ];

        return $this->render('admin/dashboard', ['stats' => $stats], 'Dashboard');
    }
}
