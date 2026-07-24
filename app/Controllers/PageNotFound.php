<?php

namespace App\Controllers;

use App\Models\MenuModel;

/**
 * Friendly handler for unknown URLs. Instead of a raw 404 page, logged-in
 * users get a "page not available yet" notice inside the admin layout — used
 * for menus whose target page hasn't been built yet.
 */
class PageNotFound extends BaseController
{
    public function index()
    {
        $auth = service('auth');
        $uri  = uri_string();

        // Not logged in: keep the normal 404 behaviour.
        if (! $auth->loggedIn()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $user      = $auth->user();
        $roleNames = $user->getGroups();
        $isSuper   = in_array('superadmin', $roleNames, true);

        $data = [
            'title'    => 'Halaman belum tersedia',
            'uri'      => $uri,
            'menuTree' => (new MenuModel())->treeForRoles($roleNames, $isSuper),
        ];

        // Keep the 404 status code but render a friendly view.
        return $this->response
            ->setStatusCode(404)
            ->setBody(view('admin/not_available', $data));
    }
}
