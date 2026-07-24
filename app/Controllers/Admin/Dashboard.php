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

        // URLs the current user is allowed to see (from their menu tree),
        // so dashboard cards only show for menus they actually have access to.
        $allowedUrls = $this->collectUrls($this->viewData['menuTree'] ?? []);

        return $this->render('admin/dashboard', [
            'stats'       => $stats,
            'allowedUrls' => $allowedUrls,
        ], 'Dashboard');
    }

    /**
     * Flatten a menu tree into a set of normalized URLs.
     */
    private function collectUrls(array $items): array
    {
        $urls = [];
        foreach ($items as $item) {
            if (! empty($item['url'])) {
                $urls[trim((string) $item['url'], '/')] = true;
            }
            if (! empty($item['children'])) {
                $urls += $this->collectUrls($item['children']);
            }
        }

        return $urls;
    }
}
