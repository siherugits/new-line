<?php

namespace App\Controllers\Admin;

use App\Libraries\DataTable;
use App\Models\PermissionModel;
use CodeIgniter\HTTP\ResponseInterface;

class Permissions extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('admin/permissions/index', [], 'Permissions');
    }

    public function data(): ResponseInterface
    {
        $model = new PermissionModel();
        $dt    = new DataTable(
            $model->builder(),
            [0 => 'id', 1 => 'name', 2 => 'description'],
            ['name', 'description'],
        );
        [$rows, $filtered] = $dt->process($this->request->getGet());

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'id'          => (int) $r['id'],
                'name'        => '<code>' . esc($r['name']) . '</code>',
                'description' => esc($r['description'] ?? ''),
                'actions'     => $this->rowActions((int) $r['id']),
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getGet('draw') ?? 0),
            'recordsTotal'    => $model->countAllResults(),
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    private function rowActions(int $id): string
    {
        return '<div class="text-end">'
            . '<a href="' . site_url('admin/permissions/' . $id . '/edit') . '" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a> '
            . '<form action="' . site_url('admin/permissions/' . $id . '/delete') . '" method="post" class="d-inline" onsubmit="return confirm(\'Delete this permission?\');">'
            . csrf_field()
            . '<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>'
            . '</form></div>';
    }

    public function new(): string
    {
        return $this->render('admin/permissions/form', ['permission' => null], 'New Permission');
    }

    public function create()
    {
        if (! $this->validate((new PermissionModel())->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        (new PermissionModel())->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('admin/permissions')->with('message', 'Permission created.');
    }

    public function edit(int $id)
    {
        $permission = (new PermissionModel())->find($id);
        if (! $permission) {
            return redirect()->to('admin/permissions')->with('error', 'Permission not found.');
        }

        return $this->render('admin/permissions/form', ['permission' => $permission], 'Edit Permission');
    }

    public function update(int $id)
    {
        $model = new PermissionModel();
        if (! $model->find($id)) {
            return redirect()->to('admin/permissions')->with('error', 'Permission not found.');
        }
        $rules         = $model->getValidationRules();
        $rules['name'] = "required|max_length[100]|is_unique[permissions.name,id,{$id}]";
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $model->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('admin/permissions')->with('message', 'Permission updated.');
    }

    public function delete(int $id)
    {
        $model = new PermissionModel();
        if (! $model->find($id)) {
            return redirect()->to('admin/permissions')->with('error', 'Permission not found.');
        }
        $model->delete($id); // cascades role_permissions

        return redirect()->to('admin/permissions')->with('message', 'Permission deleted.');
    }
}
