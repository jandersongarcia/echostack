<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use Symfony\Component\HttpFoundation\Request;

// Rota home
$router->map('GET', '/', 'App\Controllers\HomeController@index');

// Rota de cadastro
$router->map('POST', '/register', 'App\Controllers\AuthController@register');

