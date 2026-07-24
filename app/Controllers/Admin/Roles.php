<?php

namespace App\Controllers\Admin;

use App\Libraries\DataTable;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;

class Roles extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('admin/roles/index', [], 'Roles');
    }

    public function data(): ResponseInterface
    {
        $model   = new RoleModel();
        $builder = $model->builder()
            ->select('roles.*, (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = roles.id) AS perm_count', false);

        $dt = new DataTable(
            $builder,
            [0 => 'id', 1 => 'name', 2 => 'title', 3 => 'description', 4 => 'perm_count'],
            ['name', 'title', 'description'],
        );
        [$rows, $filtered] = $dt->process($this->request->getGet());

        $data = [];
        foreach ($rows as $r) {
            $badge = $r['is_system'] ? ' <span class="badge bg-info text-dark ms-1">system</span>' : '';
            $data[] = [
                'id'          => (int) $r['id'],
                'name'        => '<code>' . esc($r['name']) . '</code>' . $badge,
                'title'       => '<span class="fw-semibold">' . esc($r['title']) . '</span>',
                'description' => esc($r['description'] ?? ''),
                'permissions' => '<span class="badge bg-secondary">' . (int) $r['perm_count'] . '</span>',
                'actions'     => $this->rowActions((int) $r['id'], (bool) $r['is_system']),
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getGet('draw') ?? 0),
            'recordsTotal'    => $model->countAllResults(),
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    private function rowActions(int $id, bool $isSystem): string
    {
        $html = '<div class="text-end">'
            . '<a href="' . site_url('admin/roles/' . $id . '/edit') . '" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a> ';

        if (! $isSystem) {
            $html .= '<form action="' . site_url('admin/roles/' . $id . '/delete') . '" method="post" class="d-inline" onsubmit="return confirm(\'Delete this role?\');">'
                . csrf_field()
                . '<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>'
                . '</form>';
        }

        return $html . '</div>';
    }

    public function new(): string
    {
        return $this->render('admin/roles/form', [
            'role'        => null,
            'permissions' => (new PermissionModel())->orderBy('name', 'ASC')->findAll(),
            'rolePerms'   => [],
        ], 'New Role');
    }

    public function create()
    {
        $rules = (new RoleModel())->getValidationRules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new RoleModel();
        $model->insert([
            'name'        => $this->request->getPost('name'),
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'is_system'   => 0,
        ]);
        $id = $model->getInsertID();
        $model->syncPermissions($id, array_map('intval', (array) $this->request->getPost('permissions')));

        return redirect()->to('admin/roles')->with('message', 'Role created.');
    }

    public function edit(int $id)
    {
        $model = new RoleModel();
        $role  = $model->find($id);
        if (! $role) {
            return redirect()->to('admin/roles')->with('error', 'Role not found.');
        }

        return $this->render('admin/roles/form', [
            'role'        => $role,
            'permissions' => (new PermissionModel())->orderBy('name', 'ASC')->findAll(),
            'rolePerms'   => array_column($model->permissions($id), 'name'),
        ], 'Edit Role');
    }

    public function update(int $id)
    {
        $model = new RoleModel();
        $role  = $model->find($id);
        if (! $role) {
            return redirect()->to('admin/roles')->with('error', 'Role not found.');
        }

        $rules = $model->getValidationRules(['except' => []]);
        // {id} placeholder for is_unique
        $this->validator = \Config\Services::validation();
        $rules['name']   = "required|alpha_dash|max_length[100]|is_unique[roles.name,id,{$id}]";
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
        ];
        // system roles keep their machine name locked
        if (! $role['is_system']) {
            $data['name'] = $this->request->getPost('name');
        }
        $model->update($id, $data);
        $model->syncPermissions($id, array_map('intval', (array) $this->request->getPost('permissions')));

        return redirect()->to('admin/roles')->with('message', 'Role updated.');
    }

    public function delete(int $id)
    {
        $model = new RoleModel();
        $role  = $model->find($id);
        if (! $role) {
            return redirect()->to('admin/roles')->with('error', 'Role not found.');
        }
        if ($role['is_system']) {
            return redirect()->to('admin/roles')->with('error', 'System roles cannot be deleted.');
        }
        $model->delete($id); // cascades role_permissions & menu_access

        return redirect()->to('admin/roles')->with('message', 'Role deleted.');
    }
}
