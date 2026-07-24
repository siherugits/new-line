<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['parent_id', 'title', 'url', 'icon', 'sort_order', 'is_active'];

    protected $validationRules = [
        'title' => 'required|max_length[150]',
        'url'   => 'permit_empty|max_length[255]',
    ];

    /**
     * Role ids that can access a menu.
     */
    public function accessRoleIds(int $menuId): array
    {
        return $this->db->table('menu_access')
            ->select('role_id')
            ->where('menu_id', $menuId)
            ->get()->getResultArray();
    }

    /**
     * Sync which roles can see a menu.
     */
    public function syncAccess(int $menuId, array $roleIds): void
    {
        $this->db->table('menu_access')->where('menu_id', $menuId)->delete();

        if ($roleIds === []) {
            return;
        }

        $rows = [];
        foreach (array_unique($roleIds) as $rid) {
            $rows[] = ['menu_id' => $menuId, 'role_id' => (int) $rid];
        }
        $this->db->table('menu_access')->insertBatch($rows);
    }

    /**
     * Build the menu tree visible to a set of role names.
     * Superadmin (passed as $isSuper) sees everything.
     *
     * @param string[] $roleNames
     */
    public function treeForRoles(array $roleNames, bool $isSuper = false): array
    {
        // resolve role names -> ids
        $roleIds = [];
        if (! $isSuper && $roleNames !== []) {
            $roleIds = array_column(
                $this->db->table('roles')->select('id')->whereIn('name', $roleNames)->get()->getResultArray(),
                'id'
            );
        }

        $builder = $this->db->table('menus m')->select('m.*')->where('m.is_active', 1);

        if (! $isSuper) {
            // only menus that have an access row matching one of the user's roles
            $builder->join('menu_access ma', 'ma.menu_id = m.id')
                ->whereIn('ma.role_id', $roleIds === [] ? [0] : $roleIds)
                ->groupBy('m.id');
        }

        $rows = $builder->orderBy('m.sort_order', 'ASC')->orderBy('m.id', 'ASC')->get()->getResultArray();

        // group rows by parent id, then build the tree recursively (n-level)
        $byParent = [];
        foreach ($rows as $r) {
            $pid                = (string) ($r['parent_id'] ?? 0 ?: 0);
            $byParent[$pid][]   = $r;
        }

        $build = static function ($parentId) use (&$build, &$byParent): array {
            $items = $byParent[(string) $parentId] ?? [];
            foreach ($items as &$item) {
                $item['children'] = $build($item['id']);
            }
            return $items;
        };

        return $build(0);
    }
}
