<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default settings
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// --------------------------------------------------------------------
// Public Storefront Routes
// --------------------------------------------------------------------
$routes->get('/', 'Home::index');
$routes->get('category/(:segment)', 'Products::category/$1');
$routes->get('product/(:segment)', 'Products::detail/$1');
$routes->get('search', 'Products::search');
$routes->get('cart', 'Products::cart');
$routes->get('checkout', 'Products::checkout');

// --------------------------------------------------------------------
// Authentication Routes
// --------------------------------------------------------------------
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// --------------------------------------------------------------------
// Admin Section (Protected by AuthFilter)
// --------------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Admin\Shoes::index');
    $routes->get('shoes', 'Admin\Shoes::index');
    $routes->get('shoes/new', 'Admin\Shoes::new');
    $routes->get('shoes/create', 'Admin\Shoes::new');
    $routes->post('shoes/create', 'Admin\Shoes::create');
    $routes->get('stock', 'Admin\Shoes::stock');
    $routes->post('stock/receive', 'Admin\Shoes::receiveStock');
    $routes->get('shoes/edit/(:num)', 'Admin\Shoes::edit/$1');
    $routes->post('shoes/update/(:num)', 'Admin\Shoes::update/$1');
    $routes->post('shoes/delete/(:num)', 'Admin\Shoes::delete/$1');
    $routes->get('shoes/delete/(:num)', 'Admin\Shoes::delete/$1'); // Fallback helper
});
