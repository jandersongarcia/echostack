<?php

define('DIR',str_replace('app\api','',__DIR__));

require_once DIR . '/vendor/autoload.php';

// Carrega o bootstrap da aplicação
$app = require_once DIR . '/bootstrap/app.php';

// Despacha o roteador
$app->run();
