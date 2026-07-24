<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuModel;

abstract class BaseAdminController extends BaseController
{
    protected array $viewData = [];

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);

        $user      = service('auth')->user();
        $roleNames = $user ? $user->getGroups() : [];
        $isSuper   = $user && in_array('superadmin', $roleNames, true);

        $this->viewData['menuTree'] = (new MenuModel())->treeForRoles($roleNames, $isSuper);
    }

    /**
     * Render a view within the admin layout with shared data merged in.
     */
    protected function render(string $view, array $data = [], string $title = 'Admin'): string
    {
        $data = array_merge($this->viewData, ['title' => $title], $data);

        return view($view, $data);
    }
}
