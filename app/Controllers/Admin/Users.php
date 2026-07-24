<?php

namespace App\Controllers\Admin;

use App\Models\RoleModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Users extends BaseAdminController
{
    private function roleChoices(): array
    {
        return (new RoleModel())->orderBy('title', 'ASC')->findAll();
    }

    public function index(): string
    {
        $users = (new UserModel())->orderBy('id', 'ASC')->findAll();

        return $this->render('admin/users/index', ['users' => $users], 'Users');
    }

    public function new(): string
    {
        return $this->render('admin/users/form', [
            'user'      => null,
            'roles'     => $this->roleChoices(),
            'userRoles' => [],
        ], 'New User');
    }

    public function create()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = new UserModel();
        $user  = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ]);
        $users->save($user);

        $user = $users->findById($users->getInsertID());
        if ($this->request->getPost('active')) {
            $user->activate();
        }
        $this->syncGroups($user, (array) $this->request->getPost('roles'));

        return redirect()->to('admin/users')->with('message', 'User created.');
    }

    public function edit(int $id)
    {
        $user = (new UserModel())->findById($id);
        if (! $user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        return $this->render('admin/users/form', [
            'user'      => $user,
            'roles'     => $this->roleChoices(),
            'userRoles' => $user->getGroups(),
        ], 'Edit User');
    }

    public function update(int $id)
    {
        $users = new UserModel();
        $user  = $users->findById($id);
        if (! $user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        $rules = [
            'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'email'    => 'required|valid_email',
        ];
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username = $this->request->getPost('username');
        $user->email    = $this->request->getPost('email');
        if ($this->request->getPost('password')) {
            $user->password = $this->request->getPost('password');
        }
        $users->save($user);

        // activation
        if ($this->request->getPost('active')) {
            $user->activate();
        } else {
            $users->update($user->id, ['active' => 0]);
        }

        $this->syncGroups($user, (array) $this->request->getPost('roles'));

        return redirect()->to('admin/users')->with('message', 'User updated.');
    }

    public function delete(int $id)
    {
        $users = new UserModel();
        $user  = $users->findById($id);
        if (! $user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }
        if ($user->id === service('auth')->id()) {
            return redirect()->to('admin/users')->with('error', 'You cannot delete your own account.');
        }
        $users->delete($id);

        return redirect()->to('admin/users')->with('message', 'User deleted.');
    }

    /**
     * Make the user's Shield groups match exactly the selected role names.
     */
    private function syncGroups(User $user, array $roleNames): void
    {
        $current = $user->getGroups() ?? [];
        $roleNames = array_values(array_filter($roleNames));

        foreach (array_diff($current, $roleNames) as $remove) {
            $user->removeGroup($remove);
        }
        foreach (array_diff($roleNames, $current) as $add) {
            $user->addGroup($add);
        }
    }
}
