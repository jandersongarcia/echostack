<?php

session_start();

// CORS Headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

define('DIR', realpath(__DIR__ . '/../../'));

require_once DIR . '/vendor/autoload.php';

// Carrega o bootstrap da aplicação
$app = require_once DIR . '/core/Bootstrap/app.php';

// Despacha o roteador
$app->run();
