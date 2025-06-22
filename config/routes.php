<?php
// config/router.php

$routes = [
    '/dashboard' => '/dashboards/index.php',
    '/' => '/dashboards/login.php',
    '/logout' => '/dashboards/logout.php',
    '/test' => '/testing/index.php',
    '/test2' => '/testing/index2.php',
    '/test3' => '/testing/index3.php',
    '/test4' => '/testing/test4.php',
    '/test5' => '/testing/test5.php',
    // '' => '/dashboard/login.php',

    '/guru-staff' => '/teachers_employees/index.php',
    // '/user' => '/users/administrator/index.php',
    // '/student/peserta' => '/students/peserta/index.php',

    // akademik
    // crud siswa
    '/siswa' => '/academics/students/index.php',
    '/siswa/tambah-siswa' => '/academics/students/create.php',
    '/siswa/edit-siswa' => '/academics/students/update.php',
    '/siswa/file-upload' => '/academics/students/file_exc_upload.php',
    '/siswa/file/example-data-siswa' => '/assets/dummy/example-data-siswa.xlsx',

    // export data
    '/export-data-siswa' => '/academics/students/export_siswa.php',
    '/export_siswa_kelas' => '/academics/students/export_siswa_kelas.php',

    // lain-lain
    '/tahun-ajaran' => '/academics/school_years/index.php',
    '/tingkat-kelas' => '/academics/classrooms/grade_lv.php',
    '/kelas' => '/academics/classrooms/index.php',

    // invoice
    // pendapatan
    '/pendapatan/pembayaran-siswa' => '/finances/incomes/student_paid_fees.php',
    '/pendapatan/tagihan-spp-siswa' => '/finances/incomes/spp_student_bills.php',
    '/pendapatan/tagihan-lain-siswa' => '/finances/incomes/other_student_bills.php',
    '/pendapatan/pemasukan' => '/finances/incomes/revenues.php',
    // child
    '/child-update-tarif' => '/finances/incomes/child_tarif_spp_kelas_realtime.php',
    '/pendapatan/taglist-data-kelas' => '/finances/incomes/taglist_data_kelas.php',
    '/pendapatan/student_paid_fees_child' => '/finances/incomes/student_paid_fees_child.php',

    // pengeluaran
    '/pengeluaran/kategori-pengeluaran' => '/finances/outlays/expens_category.php',
    '/pengeluaran/detail-pengeluaran' => '/finances/outlays/expends.php',
    '/pengeluaran/file-pengeluaran' => '/assets/images/dana_pengeluaran/',
    // child
    '/child-expens-category' => '/finances/outlays/child_expens_category.php',

];

// Mendapatkan URI saat ini
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// Fungsi untuk menentukan apakah link atau menu dropdown sedang aktif
function isActive($route, $currentUri)
{
    if (is_array($route)) {
        return in_array($currentUri, $route) ? 'active' : '';
    }
    return $currentUri === $route ? 'active' : '';
}

// Fungsi untuk memeriksa apakah salah satu rute dalam array aktif
function isAnyActive($routes, $currentUri)
{
    foreach ($routes as $key => $route) {
        // Check if the current route is in an array (like siswa-i)
        if (is_array($route)) {
            if (isAnyActive($route, $currentUri)) {
                return true;
            }
        } elseif ($currentUri === $route) {
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