<?php

ob_start();
ini_set('session.cookie_lifetime', 86400); // 1 hari
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

if ($_SESSION['role'] !== 'super_admin') {
    header("Location: /");
    exit();
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Dashboard</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css"
        integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous" />

    <link rel="stylesheet" href="../assets/css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php
        include 'navbar.php';
        include 'sidebar.php';
        include 'footer.php';
        ?>

        <!-- </div> -->
        <!--end::App Wrapper-->
        <!--begin::Script-->
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script>
        <!--end::Required Plugin(popperjs for Bootstrap 5)-->
        <!--begin::Required Plugin(Bootstrap 5)-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script>
        <!--end::Required Plugin(Bootstrap 5)-->
        <!--begin::Required Plugin(AdminLTE)-->
        <script src="../assets/js/adminlte.js"></script>
        <!--end::Required Plugin(AdminLTE)-->

        <script>
            // Fungsi untuk format Rupiah
            function formatRupiah(value) {
                if (!value) return '';
                return new Intl.NumberFormat('id-ID', {
                    // style: 'decimal',
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    // maximumFractionDigits: 0
                }).format(value);
            }

            // Fungsi untuk menghapus format Rupiah dari string dan mengubahnya menjadi angka
            function parseRupiah(value) {
                if (!value) return 0;
                return parseFloat(value.replace(/[^0-9,-]+/g, '').replace(',', '.')) || 0;
            }


            // Fungsi format mata uang 2
            function formatCurrency(value) {
                if (value === undefined || value === null) {
                    return '-';
                }
                return Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            // Fungsi untuk memformat tanggal
            function formatTanggal(tanggal) {
                const options = {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                };
                const date = new Date(tanggal);
                return date.toLocaleDateString('id-ID', options);
            }
        </script>
</body>
<!--end::Body-->

</html>