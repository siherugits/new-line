<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (service('auth')->loggedIn()) {
            return redirect()->to('admin');
        }

        return redirect()->to('login');
    }
}
