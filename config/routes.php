<?php
// config/router.php

$routes = [
    '/dashboard' => '/dashboards/index.php',
    '/test' => '/testing/index.php',
    // '/login' => '/dashboard/login.php',

    // '/guru-staff' => '/users/guru-karyawan.php',
    // '/user' => '/users/administrator/index.php',
    // '/student/peserta' => '/students/peserta/index.php',

    // akademik
    // crud siswa
    '/siswa' => '/academics/students/index.php',
    '/siswa/tambah-siswa' => '/academics/students/create.php',
    '/siswa/edit-siswa' => '/students/update_siswa.php',

    // lain-lain
    '/tahun-ajaran' => '/academics/school_years/index.php',
    '/tingkat-kelas' => '/academics/classrooms/grade_lv.php',
    '/kelas' => '/academics/classrooms/index.php',

    // invoice
    // pendapatan
    '/pendapatan/pembayaran-siswa' => '/finances/incomes/student_paid_fees.php',
    '/pendapatan/tagihan-spp-siswa' => '/finances/incomes/spp_student_bills.php',
    '/pendapatan/tagihan-lain-siswa' => '/finances/incomes/other_student_bills.php',
    '/pendapatan/pemasukan-bos' => '/finances/incomes/revenues_bos.php',
    '/pendapatan/pemasukan-lain' => '/finances/incomes/revenues_other.php',

    // pengeluaran
    '/pengeluaran/kategori-pengeluaran' => '/finances/outlays/expens_category.php',
    '/pengeluaran/detail-pengeluaran' => '/finances/outlays/expends.php',
    '/child-expens-category' => '/finances/outlays/child_expens_category.php',

];

// Mendapatkan URI saat ini
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// Fungsi untuk menentukan apakah link atau menu dropdown sedang aktif
function isActive($route, $requestUri)
{
    return $route === $requestUri ? 'active' : '';
}

// Fungsi untuk memeriksa apakah salah satu rute dalam array aktif
function isAnyActive($routes, $requestUri)
{
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
    require __DIR__ . '/../layouts/error_404.php';
}