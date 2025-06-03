<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use Symfony\Component\HttpFoundation\Request;

// Rota home
$router->map('GET', '/v1/', 'App\Controllers\HomeController@index');

// Rota de cadastro
$router->map('POST', '/v1/register', 'App\Controllers\AuthController@register');
