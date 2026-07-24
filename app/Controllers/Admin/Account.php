<?php

namespace App\Controllers\Admin;

use CodeIgniter\Shield\Models\UserModel;

class Account extends BaseAdminController
{
    /**
     * Show the change-password form.
     */
    public function password(): string
    {
        return $this->render('admin/account/password', [], 'Ganti Password');
    }

    /**
     * Handle the change-password submission for the currently logged-in user.
     */
    public function updatePassword()
    {
        $user = service('auth')->user();

        $minLength = config('Auth')->minimumPasswordLength;

        $rules = [
            'current_password' => 'required',
            'new_password'     => "required|min_length[{$minLength}]|differs[current_password]",
            'pass_confirm'     => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Verify the current password.
        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('current_password'),
        ];
        $result = auth()->check($credentials);

        if (! $result->isOK()) {
            return redirect()->back()->withInput()
                ->with('error', 'Password saat ini salah.');
        }

        // Save the new password.
        $users          = new UserModel();
        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        return redirect()->to('admin/account/password')
            ->with('message', 'Password berhasil diubah.');
    }
}
