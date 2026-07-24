<?php

namespace App\Models;

use Config\Database;

/**
 * Server-side data provider for the Users DataTable. Handles paging,
 * ordering and searching at the database level, joining Shield's
 * identity (email) and group (roles) tables.
 */
class UserGridModel
{
    private $db;

    /** Column index (from DataTables) -> orderable SQL expression. */
    private const ORDER_COLUMNS = [
        0 => 'u.id',
        1 => 'u.username',
        2 => 'email',
        3 => 'roles',
        4 => 'u.active',
    ];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Base builder: users + email identity + concatenated roles.
     */
    private function baseBuilder()
    {
        $isPg = $this->db->DBDriver === 'Postgre';

        // Aggregate the user's roles into a comma list. GROUP_CONCAT is
        // MySQL-only; PostgreSQL uses STRING_AGG and quotes the reserved
        // word "group" with double quotes (backticks are MySQL-only).
        $rolesExpr = $isPg
            ? 'STRING_AGG(DISTINCT g."group", \',\' ORDER BY g."group") AS roles'
            : 'GROUP_CONCAT(DISTINCT g.`group` ORDER BY g.`group` SEPARATOR \',\') AS roles';

        $builder = $this->db->table('users u')
            ->select("u.id, u.username, u.active, ident.secret AS email, {$rolesExpr}", false)
            ->join(
                'auth_identities ident',
                "ident.user_id = u.id AND ident.type = 'email_password'",
                'left'
            )
            ->join('auth_groups_users g', 'g.user_id = u.id', 'left');

        // PostgreSQL requires every non-aggregated selected column in GROUP BY;
        // MySQL is happy grouping by the primary key alone.
        return $isPg
            ? $builder->groupBy('u.id, u.username, u.active, ident.secret')
            : $builder->groupBy('u.id');
    }

    /**
     * Total number of users (unfiltered).
     */
    public function total(): int
    {
        return $this->db->table('users')->countAllResults();
    }

    /**
     * Returns [rows, filteredCount] for the given DataTables request.
     *
     * @param array $req The DataTables request params ($_GET / $_POST).
     */
    public function datatable(array $req): array
    {
        $search = trim($req['search']['value'] ?? '');
        $start  = (int) ($req['start'] ?? 0);
        $length = (int) ($req['length'] ?? 10);

        $builder = $this->baseBuilder();

        // Global search across username + email.
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('ident.secret', $search)
                ->groupEnd();
        }

        // Filtered count (distinct users matching the search).
        $countBuilder = clone $builder;
        $filtered     = count($countBuilder->get()->getResultArray());

        // Ordering.
        $orderColIdx = (int) ($req['order'][0]['column'] ?? 0);
        $orderDir    = strtolower($req['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $orderExpr   = self::ORDER_COLUMNS[$orderColIdx] ?? 'u.id';
        $builder->orderBy($orderExpr, $orderDir);

        // Paging (length = -1 means "all").
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $rows = $builder->get()->getResultArray();

        return [$rows, $filtered];
    }
}
