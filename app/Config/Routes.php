<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Register all Shield auth routes except its own "login" group, which we
// redefine below so the captcha can be verified before logging in.
service('auth')->routes($routes, ['except' => ['login']]);

// SVG captcha image endpoint.
$routes->get('captcha', 'CaptchaController::index');

// Custom login routes (captcha-aware controller extending Shield's).
$routes->get('login', 'Auth\LoginController::loginView', ['as' => 'login']);
$routes->post('login', 'Auth\LoginController::loginAction');

$routes->group('admin', ['filter' => ['admin', 'menuaccess'], 'namespace' => 'App\Controllers\Admin'], static function ($routes): void {
    $routes->get('/', 'Dashboard::index');

    // Account (self-service)
    $routes->get('account/password', 'Account::password');
    $routes->post('account/password', 'Account::updatePassword');

    // Theme (global appearance)
    $routes->get('theme', 'Theme::index');
    $routes->post('theme', 'Theme::update');
    $routes->post('theme/reset', 'Theme::reset');

    // Users
    $routes->get('users', 'Users::index');
    $routes->get('users/data', 'Users::data');
    $routes->get('users/new', 'Users::new');
    $routes->post('users', 'Users::create');
    $routes->get('users/(:num)/edit', 'Users::edit/$1');
    $routes->post('users/(:num)', 'Users::update/$1');
    $routes->post('users/(:num)/delete', 'Users::delete/$1');

    // Roles
    $routes->get('roles', 'Roles::index');
    $routes->get('roles/data', 'Roles::data');
    $routes->get('roles/new', 'Roles::new');
    $routes->post('roles', 'Roles::create');
    $routes->get('roles/(:num)/edit', 'Roles::edit/$1');
    $routes->post('roles/(:num)', 'Roles::update/$1');
    $routes->post('roles/(:num)/delete', 'Roles::delete/$1');

    // Permissions
    $routes->get('permissions', 'Permissions::index');
    $routes->get('permissions/data', 'Permissions::data');
    $routes->get('permissions/new', 'Permissions::new');
    $routes->post('permissions', 'Permissions::create');
    $routes->get('permissions/(:num)/edit', 'Permissions::edit/$1');
    $routes->post('permissions/(:num)', 'Permissions::update/$1');
    $routes->post('permissions/(:num)/delete', 'Permissions::delete/$1');

    // Menus OK
    $routes->get('menus', 'Menus::index');
    $routes->get('menus/data', 'Menus::data');
    $routes->get('menus/new', 'Menus::new');
    $routes->post('menus', 'Menus::create');
    $routes->get('menus/(:num)/edit', 'Menus::edit/$1');
    $routes->post('menus/(:num)', 'Menus::update/$1');
    $routes->post('menus/(:num)/delete', 'Menus::delete/$1');
});
