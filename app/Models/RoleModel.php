<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name', 'title', 'description', 'is_system'];

    protected $validationRules = [
        'name'  => 'required|alpha_dash|max_length[100]|is_unique[roles.name,id,{id}]',
        'title' => 'required|max_length[150]',
    ];

    /**
     * Returns permission names attached to a role.
     */
    public function permissions(int $roleId): array
    {
        return $this->db->table('role_permissions rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();
    }

    /**
     * Sync permissions for a role (array of permission ids).
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();

        if ($permissionIds === []) {
            return;
        }

        $rows = [];
        foreach (array_unique($permissionIds) as $pid) {
            $rows[] = ['role_id' => $roleId, 'permission_id' => (int) $pid];
        }
        $this->db->table('role_permissions')->insertBatch($rows);
    }
}
