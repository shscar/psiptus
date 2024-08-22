<?php
// config/router.php

$routes = [
    '/' => '/dashboard/index.php',
    '/login' => '/dashboard/login.php',
];

// Mendapatkan URI yang diminta
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Mengatur rute yang ditemukan
if (array_key_exists($requestUri, $routes)) {
    require __DIR__ . '/../' . $routes[$requestUri];
} else {
    // Jika rute tidak ditemukan, arahkan ke halaman 404
    http_response_code(404);
    require __DIR__ . '/../layouts/error.php';
}
