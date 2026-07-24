<?php

namespace App\Controllers\Admin;

use App\Models\PermissionModel;

class Permissions extends BaseAdminController
{
    public function index(): string
    {
        $permissions = (new PermissionModel())->orderBy('name', 'ASC')->findAll();

        return $this->render('admin/permissions/index', ['permissions' => $permissions], 'Permissions');
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
