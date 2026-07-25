<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Adds the "Tema" menu under "Administration" if it doesn't exist yet.
 * Idempotent: safe to run on databases that were already seeded.
 *
 *   php spark db:seed MenuThemeSeeder
 */
class MenuThemeSeeder extends Seeder
{
    public function run(): void
    {
        $now   = date('Y-m-d H:i:s');
        $menus = $this->db->table('menus');

        // Already present? Nothing to do.
        $existing = $menus->where('url', 'admin/theme')->get()->getRowArray();
        if ($existing !== null) {
            return;
        }

        // Find the "Administration" parent (top level) to nest under.
        $parent = $menus->where('title', 'Administration')
            ->where('parent_id', null)
            ->get()->getRowArray();
        $parentId = $parent !== null ? (int) $parent['id'] : null;

        // Place it after the last sibling.
        $lastRow   = $menus->selectMax('sort_order')
            ->where('parent_id', $parentId)
            ->get()->getRowArray();
        $lastOrder = (int) ($lastRow['sort_order'] ?? 0);

        $menus->insert([
            'parent_id'  => $parentId,
            'title'      => 'Tema',
            'url'        => 'admin/theme',
            'icon'       => 'palette',
            'sort_order' => $lastOrder + 1,
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $menuId = (int) $this->db->insertID();

        // Grant access to superadmin (same as configured on the source DB).
        $roleIds = array_column(
            $this->db->table('roles')
                ->select('id')
                ->whereIn('name', ['superadmin'])
                ->get()->getResultArray(),
            'id'
        );

        foreach ($roleIds as $rid) {
            $this->db->table('menu_access')->insert([
                'menu_id' => $menuId,
                'role_id' => (int) $rid,
            ]);
        }
    }
}
