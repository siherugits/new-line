<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use Config\AuthGroups;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        /** @var AuthGroups $cfg */
        $cfg = config('AuthGroups');

        // ---- Roles from Shield's default groups ----
        $roleTable = $this->db->table('roles');
        $roleIds   = [];
        foreach ($cfg->groups as $name => $info) {
            $existing = $roleTable->where('name', $name)->get()->getRowArray();
            if ($existing) {
                $roleIds[$name] = (int) $existing['id'];
                continue;
            }
            $roleTable->insert([
                'name'        => $name,
                'title'       => $info['title'] ?? ucfirst($name),
                'description' => $info['description'] ?? '',
                'is_system'   => in_array($name, ['superadmin', 'user'], true) ? 1 : 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $roleIds[$name] = (int) $this->db->insertID();
        }

        // ---- Permissions from Shield defaults ----
        $permTable = $this->db->table('permissions');
        $permIds   = [];
        foreach ($cfg->permissions as $name => $desc) {
            $existing = $permTable->where('name', $name)->get()->getRowArray();
            if ($existing) {
                $permIds[$name] = (int) $existing['id'];
                continue;
            }
            $permTable->insert([
                'name'        => $name,
                'description' => $desc,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $permIds[$name] = (int) $this->db->insertID();
        }

        // ---- Matrix -> role_permissions ----
        $rpTable = $this->db->table('role_permissions');
        foreach ($cfg->matrix as $roleName => $patterns) {
            if (! isset($roleIds[$roleName])) {
                continue;
            }
            $expanded = $this->expandPatterns($patterns, array_keys($permIds));
            foreach ($expanded as $permName) {
                if (! isset($permIds[$permName])) {
                    continue;
                }
                $exists = $rpTable->where([
                    'role_id'       => $roleIds[$roleName],
                    'permission_id' => $permIds[$permName],
                ])->countAllResults() > 0;
                if (! $exists) {
                    $rpTable->insert([
                        'role_id'       => $roleIds[$roleName],
                        'permission_id' => $permIds[$permName],
                    ]);
                }
            }
        }

        // ---- Default admin user ----
        $users = new UserModel();
        if (! $users->where('username', 'admin')->first()) {
            $user = new User([
                'username' => 'admin',
                'email'    => 'admin@example.com',
                'password' => 'admin12345',
            ]);
            $users->save($user);
            $user = $users->findById($users->getInsertID());
            $user->activate();
            $user->addGroup('superadmin');
        }

        // ---- Default topbar menus ----
        $this->seedMenus($roleIds, $now);
    }

    /**
     * Expand Shield-style patterns like "admin.*" against the known permission list.
     */
    private function expandPatterns(array $patterns, array $allPermNames): array
    {
        $result = [];
        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1); // keep trailing dot
                foreach ($allPermNames as $p) {
                    if (str_starts_with($p, $prefix)) {
                        $result[] = $p;
                    }
                }
            } else {
                $result[] = $pattern;
            }
        }
        return array_unique($result);
    }

    private function seedMenus(array $roleIds, string $now): void
    {
        $menuTable = $this->db->table('menus');
        if ($menuTable->countAllResults() > 0) {
            return; // already seeded
        }

        $accessTable = $this->db->table('menu_access');
        $adminRoles  = array_values(array_intersect_key(
            $roleIds,
            array_flip(['superadmin', 'admin', 'developer'])
        ));

        $items = [
            ['title' => 'Dashboard', 'url' => 'admin', 'icon' => 'speedometer2', 'order' => 1],
            ['title' => 'Users', 'url' => 'admin/users', 'icon' => 'people', 'order' => 2],
            ['title' => 'Roles', 'url' => 'admin/roles', 'icon' => 'shield-lock', 'order' => 3],
            ['title' => 'Permissions', 'url' => 'admin/permissions', 'icon' => 'key', 'order' => 4],
            ['title' => 'Menus', 'url' => 'admin/menus', 'icon' => 'list', 'order' => 5],
        ];

        foreach ($items as $it) {
            $menuTable->insert([
                'parent_id'  => null,
                'title'      => $it['title'],
                'url'        => $it['url'],
                'icon'       => $it['icon'],
                'sort_order' => $it['order'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $menuId = (int) $this->db->insertID();
            foreach ($adminRoles as $rid) {
                $accessTable->insert(['menu_id' => $menuId, 'role_id' => $rid]);
            }
        }
    }
}
