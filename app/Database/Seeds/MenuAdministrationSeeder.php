<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Groups the admin menus (Users, Roles, Permissions, Menus) under a single
 * top-level "Administration" parent. Idempotent: safe to run repeatedly.
 *
 *   php spark db:seed MenuAdministrationSeeder
 */
class MenuAdministrationSeeder extends Seeder
{
    /** Titles of the menus to nest under Administration. */
    private const CHILDREN = ['Users', 'Roles', 'Permissions', 'Menus'];

    public function run(): void
    {
        $now   = date('Y-m-d H:i:s');
        $menus = $this->db->table('menus');

        // 1. Ensure the "Administration" parent exists (top level).
        $parent = $menus->where('title', 'Administration')
            ->where('parent_id', null)
            ->get()->getRowArray();

        if ($parent === null) {
            $menus->insert([
                'parent_id'  => null,
                'title'      => 'Administration',
                'url'        => '',            // parent is just a dropdown toggle
                'icon'       => 'gear',
                'sort_order' => 2,             // after Dashboard (1)
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $parentId = (int) $this->db->insertID();
        } else {
            $parentId = (int) $parent['id'];
        }

        // 2. Move the child menus under Administration (only the top-level ones).
        $order = 1;
        foreach (self::CHILDREN as $title) {
            $child = $menus->where('title', $title)
                ->where('parent_id', null)
                ->get()->getRowArray();

            if ($child === null) {
                continue; // not present or already nested — skip
            }

            $menus->where('id', $child['id'])->update([
                'parent_id'  => $parentId,
                'sort_order' => $order++,
                'updated_at' => $now,
            ]);
        }

        // 3. Give the parent the same role access as its children (union),
        //    so anyone who can see a child can see the Administration menu.
        $childIds = $menus->select('id')
            ->where('parent_id', $parentId)
            ->get()->getResultArray();
        $childIds = array_column($childIds, 'id');

        if ($childIds !== []) {
            $access   = $this->db->table('menu_access');
            $roleRows = $access->select('role_id')
                ->whereIn('menu_id', $childIds)
                ->groupBy('role_id')
                ->get()->getResultArray();

            foreach ($roleRows as $row) {
                $exists = $access->where('menu_id', $parentId)
                    ->where('role_id', $row['role_id'])
                    ->countAllResults() > 0;
                if (! $exists) {
                    $access->insert(['menu_id' => $parentId, 'role_id' => $row['role_id']]);
                }
            }
        }
    }
}
