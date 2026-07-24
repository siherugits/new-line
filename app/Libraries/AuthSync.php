<?php

namespace App\Libraries;

use Config\Database;
use Throwable;

/**
 * Pushes the dynamic roles / permissions stored in the database into
 * Shield's Config\AuthGroups instance at runtime, so that Shield's own
 * authorization ($user->can(), group filters, etc.) stays in sync with
 * what admins configure through the UI.
 */
class AuthSync
{
    public static function apply(): void
    {
        try {
            $db = Database::connect();

            // Bail out quietly if the admin tables aren't migrated yet
            // (e.g. during `spark migrate`).
            if (! $db->tableExists('roles') || ! $db->tableExists('permissions')) {
                return;
            }

            /** @var \Config\AuthGroups $config */
            $config = config('AuthGroups');

            // ---- Groups ----
            $roles  = $db->table('roles')->orderBy('id', 'ASC')->get()->getResultArray();
            $groups = [];
            foreach ($roles as $r) {
                $groups[$r['name']] = [
                    'title'       => $r['title'],
                    'description' => (string) ($r['description'] ?? ''),
                ];
            }
            if ($groups !== []) {
                $config->groups = $groups;
            }

            // ---- Permissions ----
            $perms       = $db->table('permissions')->orderBy('name', 'ASC')->get()->getResultArray();
            $permissions = [];
            foreach ($perms as $p) {
                $permissions[$p['name']] = (string) ($p['description'] ?? '');
            }
            if ($permissions !== []) {
                $config->permissions = $permissions;
            }

            // ---- Matrix (role -> [permission names]) ----
            $rows = $db->table('role_permissions rp')
                ->select('r.name AS role, p.name AS perm')
                ->join('roles r', 'r.id = rp.role_id')
                ->join('permissions p', 'p.id = rp.permission_id')
                ->get()->getResultArray();

            $matrix = [];
            foreach (array_keys($groups) as $roleName) {
                $matrix[$roleName] = [];
            }
            foreach ($rows as $row) {
                $matrix[$row['role']][] = $row['perm'];
            }
            if ($matrix !== []) {
                $config->matrix = $matrix;
            }
        } catch (Throwable $e) {
            // Never let auth-sync break the whole app; log and move on.
            log_message('error', 'AuthSync failed: {msg}', ['msg' => $e->getMessage()]);
        }
    }
}
