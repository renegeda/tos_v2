<?php
// Простейший роутер
$request = parse_url($_SERVER['REQUEST_URI']);
$path = trim($request['path'], '/');

// Маршруты
$routes = [
    'tso' => 'login.php',
    'tso/' => 'login.php',
    'tso/dashboard' => 'dashboard.php'
];

if (isset($routes[$path])) {
    require __DIR__.'/'.$routes[$path];
} else {
    http_response_code(404);
    require __DIR__.'/404.php';
}