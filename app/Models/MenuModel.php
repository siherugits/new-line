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
     * Decide whether a user (by role names) may access the page at $uri,
     * based on the menu_access checkboxes.
     *
     * Rules:
     *  - Superadmin may access everything.
     *  - If no active menu maps to this URL, it's not a menu-guarded page
     *    (e.g. it's already protected by another filter) -> allow.
     *  - Otherwise the user must have at least one of the roles that are
     *    checked for that menu.
     *
     * @param string[] $roleNames
     */
    public function canAccessUri(array $roleNames, bool $isSuper, string $uri): bool
    {
        if ($isSuper) {
            return true;
        }

        $uri = trim($uri, '/');

        // Find active menus whose URL matches this request.
        $menus = $this->db->table('menus')
            ->select('id')
            ->where('is_active', 1)
            ->whereIn('url', [$uri, '/' . $uri])
            ->get()->getResultArray();

        if ($menus === []) {
            return true; // URL is not tied to any menu — not guarded here
        }

        $menuIds = array_column($menus, 'id');

        // Resolve the user's role names to ids.
        $roleIds = $roleNames === [] ? [] : array_column(
            $this->db->table('roles')->select('id')->whereIn('name', $roleNames)->get()->getResultArray(),
            'id'
        );

        if ($roleIds === []) {
            return false;
        }

        // Allow if any matching menu is checked for any of the user's roles.
        $count = $this->db->table('menu_access')
            ->whereIn('menu_id', $menuIds)
            ->whereIn('role_id', $roleIds)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Whether the given roles have at least one menu checked for them.
     * Used to decide if a user may enter the admin area at all
     * (superadmin always may).
     *
     * @param string[] $roleNames
     */
    public function hasAnyMenu(array $roleNames, bool $isSuper = false): bool
    {
        if ($isSuper) {
            return true;
        }

        $roleIds = $roleNames === [] ? [] : array_column(
            $this->db->table('roles')->select('id')->whereIn('name', $roleNames)->get()->getResultArray(),
            'id'
        );

        if ($roleIds === []) {
            return false;
        }

        return $this->db->table('menu_access ma')
            ->join('menus m', 'm.id = ma.menu_id')
            ->where('m.is_active', 1)
            ->whereIn('ma.role_id', $roleIds)
            ->countAllResults() > 0;
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
