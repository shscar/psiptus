<?php
// config/router.php

$routes = [
    '/' => '/dashboard/index.php',
    '/test' => '/test/index.php',
    '/login' => '/dashboard/login.php',
    '/guru-karyawan' => '/users/guru-karyawan.php',
    '/user' => '/users/administrator/index.php',
    '/student/peserta' => '/students/peserta/index.php',

    // crud siswa
    '/siswa' => '/students/peserta_didik.php',
    '/siswa/tambah-siswa' => '/students/tambah_siswa.php',
    '/siswa/edit-siswa' => '/students/update_siswa.php',

    // crud tahun ajaran
    'tahun-ajaran' => '/akademiks/tahun_ajaran/index.php',
    'tahun-ajaran/create' => '/akademiks/tahun_ajaran/create.php',
    
];

// Mendapatkan URI saat ini
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// Fungsi untuk menentukan apakah link atau menu dropdown sedang aktif
function isActive($route, $requestUri) {
    return $route === $requestUri ? 'active' : '';
}

// Fungsi untuk memeriksa apakah salah satu rute dalam array aktif
function isAnyActive($routes, $requestUri) {
    foreach ($routes as $route) {
        if (isActive($route, $requestUri) === 'active') {
            return true;
        }
    }
    return false;
}

// Mengatur rute yang ditemukan
if (array_key_exists($requestUri, $routes)) {
    require __DIR__ . '/../' . $routes[$requestUri];
} else {
    // Jika rute tidak ditemukan, arahkan ke halaman 404
    http_response_code(404);
    require __DIR__ . '/../layouts/error.php';
}
