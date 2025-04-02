<?php
/**
 * API Routes
 * 
 * This file defines all the API routes and maps them to controller actions
 */

use Api\Core\Router;

/**
 * @var Router $router
 */

// Patient routes
$router->get('patients', 'Api\\Controllers\\PatientController', 'index');
$router->get('patients/show', 'Api\\Controllers\\PatientController', 'show');
$router->post('patients', 'Api\\Controllers\\PatientController', 'store');
$router->put('patients', 'Api\\Controllers\\PatientController', 'update');
$router->delete('patients', 'Api\\Controllers\\PatientController', 'destroy');

// User Registration routes
$router->get('register', 'Api\\Controllers\\SysRegisterController', 'index');
$router->get('register/show', 'Api\\Controllers\\SysRegisterController', 'show');
$router->post('register', 'Api\\Controllers\\SysRegisterController', 'store');
$router->put('register', 'Api\\Controllers\\SysRegisterController', 'update');
$router->delete('register', 'Api\\Controllers\\SysRegisterController', 'destroy');

// User Management routes
$router->get('users', 'Api\\Controllers\\SysUserController', 'index');
$router->get('users/show', 'Api\\Controllers\\SysUserController', 'show');
$router->put('users', 'Api\\Controllers\\SysUserController', 'update');
$router->put('users/toggle-active', 'Api\\Controllers\\SysUserController', 'toggleActive');
$router->post('users/assign-role', 'Api\\Controllers\\SysUserController', 'assignRole');
$router->post('users/remove-role', 'Api\\Controllers\\SysUserController', 'removeRole');
$router->post('users/associate-person', 'Api\\Controllers\\SysUserController', 'associateWithPerson');

// Person Management routes
$router->get('persons', 'Api\\Controllers\\RhPersonController', 'index');
$router->get('persons/show', 'Api\\Controllers\\RhPersonController', 'show');
$router->post('persons', 'Api\\Controllers\\RhPersonController', 'store');
$router->put('persons', 'Api\\Controllers\\RhPersonController', 'update');
$router->delete('persons', 'Api\\Controllers\\RhPersonController', 'destroy');
$router->get('persons/search', 'Api\\Controllers\\RhPersonController', 'search');
$router->get('persons/active', 'Api\\Controllers\\RhPersonController', 'getActive');