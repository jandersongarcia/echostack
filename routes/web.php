<?php

// Aqui você já tem acesso ao $router que foi criado no bootstrap.
// Então apenas adicionamos as rotas:

$router->map('GET', '/', 'App\Controllers\HomeController@index');
