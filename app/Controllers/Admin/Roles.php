<?php

namespace App\Controllers\Admin;

use App\Models\PermissionModel;
use App\Models\RoleModel;

class Roles extends BaseAdminController
{
    public function index(): string
    {
        $roles = (new RoleModel())->orderBy('id', 'ASC')->findAll();
        $model = new RoleModel();

        $permCounts = [];
        foreach ($roles as $r) {
            $permCounts[$r['id']] = count($model->permissions((int) $r['id']));
        }

        return $this->render('admin/roles/index', [
            'roles'      => $roles,
            'permCounts' => $permCounts,
        ], 'Roles');
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
