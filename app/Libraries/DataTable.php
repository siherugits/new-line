<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;

/**
 * Reusable server-side processor for DataTables.
 *
 * Feed it a query builder plus which SQL expressions are orderable (by
 * column index) and searchable, and it applies the DataTables request
 * (search / order / paging) and returns the rows + filtered count.
 *
 * Usage in a controller:
 *
 *   $dt = new DataTable(
 *       $model->builder(),            // or db->table(...)
 *       [0 => 'id', 1 => 'name'],     // orderable: colIndex => sql expr
 *       ['name', 'description'],      // searchable sql exprs
 *   );
 *   [$rows, $filtered] = $dt->process($this->request->getGet());
 */
class DataTable
{
    public function __construct(
        private BaseBuilder $builder,
        private array $orderColumns,
        private array $searchColumns,
    ) {
    }

    /**
     * Apply the DataTables request and return [rows, filteredCount].
     *
     * @param array $req DataTables request params ($_GET / $_POST).
     *
     * @return array{0: list<array<string,mixed>>, 1: int}
     */
    public function process(array $req): array
    {
        $search = trim($req['search']['value'] ?? '');
        $start  = (int) ($req['start'] ?? 0);
        $length = (int) ($req['length'] ?? 10);

        // Search across the configured columns.
        if ($search !== '' && $this->searchColumns !== []) {
            $this->builder->groupStart();
            foreach ($this->searchColumns as $i => $col) {
                $i === 0
                    ? $this->builder->like($col, $search)
                    : $this->builder->orLike($col, $search);
            }
            $this->builder->groupEnd();
        }

        // Filtered count (rows matching the search, before paging).
        $filtered = (clone $this->builder)->countAllResults(false);

        // Ordering.
        $colIdx = (int) ($req['order'][0]['column'] ?? 0);
        $dir    = strtolower($req['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $expr   = $this->orderColumns[$colIdx] ?? array_values($this->orderColumns)[0] ?? null;
        if ($expr !== null) {
            $this->builder->orderBy($expr, $dir);
        }

        // Paging (length -1 = all).
        if ($length > 0) {
            $this->builder->limit($length, $start);
        }

        $rows = $this->builder->get()->getResultArray();

        return [$rows, $filtered];
    }
}
