<?php
require_once dirname(__FILE__) . '/zyte.autoload.php';
date_default_timezone_set('Europe/Oslo');
error_reporting(E_ALL);

use Zyte\Zyte as Zyte;

# CREATE PAGE OBJECT
$zyte = new Zyte($crossOrigin);

# ROUTES

# LOGIN / LOGOUT
$zyte->post([
  'route' => '/login/{type:^[a-z]+$}',
  'controller' => 'LoginController'
]);
$zyte->get([
  'route' => '/logout/{type:^[a-z]+$}',
  'controller' => 'LogoutController'
]);

# REGISTRATION
$zyte->post([
  'route' => '/register',
  'controller' => 'RegisterController'
]);
$zyte->put([
  'route' => '/register/{id:^[0-9]+$}/{code:^[a-zA-Z0-9]+$}',
  'controller' => 'VerifyRegisterController'
]);

# AUTHENTICATION
$zyte->get([
  'route' => '/auth/{type:^[a-z]+$}/[company:^[a-zA-Z-]+$]',
  'controller' => 'AuthController'
]);

# PASSWORD RECOVERY  / CHANGE
$zyte->post([
  'route' => '/password/{action:^[a-zA-Z-]+$}',
  'controller' => 'PasswordController'
]);

# PROJECTS
$zyte->get([
  'route' => '/projects/{start:^[0-9-]+$}/{end:^[0-9-]+$}',
  'controller' => 'ProjectsController'
]);
$zyte->route([
  'route' => '/project/{id:^[0-9]+$}',
  'method' => ['GET', 'PUT', 'POST', 'DELETE'],
  'controller' => 'ProjectController'
]);
$zyte->get([
  'route' => '/plans/{start:^[0-9-]+$}/{end:^[0-9-]+$}',
  'controller' => 'PlansController'
]);
$zyte->route([
  'route' => '/plan/{id:^[0-9]+$}',
  'method' => ['GET', 'PUT', 'POST', 'DELETE'],
  'controller' => 'PlanController'
]);

# RENDER PAGE
$zyte->render();
