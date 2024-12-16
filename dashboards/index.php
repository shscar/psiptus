<?php

// $content = __DIR__ . '/index_content.php';
include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// menghitung total julah siswa
$stmt = $db->prepare("SELECT COUNT(*) AS total FROM siswa");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalSiswa = $result['total'];

// menghitung jumlah dana bos yang masuk
$stmt = $db->prepare("SELECT SUM(nominal) AS total_bos_masuk FROM pemasukan_dana_bos");
$stmt->execute();
$totalBosMasuk = $stmt->fetchColumn();

// menghitung total dari total_jumlah
$stmt = $db->query("SELECT SUM(total) AS total_dana_keluar FROM pengeluaran_dana");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalDanaKeluar = $result['total_dana_keluar'] ? $result['total_dana_keluar'] : 0;

?>

<!-- apexcharts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous">
<!-- jsvectormap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous">

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Dashboard
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-primary shadow-sm">
                            <i class="bi bi-gear-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Traffic</span>
                            <span class="info-box-number">
                                10
                                <small>%</small>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-success shadow-sm">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pemasukan D</span>
                            <span class="info-box-number">
                                Rp. <?php echo number_format($totalBosMasuk, 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- fix for small devices only -->
                <!-- <div class="clearfix hidden-md-up"></div> -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-danger shadow-sm">
                            <i class="bi bi-cart-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Belanja/Pengeluaran</span>
                            <span class="info-box-number">
                                Rp <?php echo number_format($totalDanaKeluar, 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-warning shadow-sm">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Members</span>
                            <span class="info-box-number">
                                <?php echo $totalSiswa; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!--begin::Row-->
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">

                        <div class="card-header">
                            <h5 class="card-title">Monthly Recap Report</h5>
                            <div class="card-tools">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-tool dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-wrench"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" role="menu">
                                        <a href="#" class="dropdown-item">Action</a>
                                        <a href="#" class="dropdown-item">Another action</a>
                                        <a href="#" class="dropdown-item"> Something else here</a>
                                        <a class="dropdown-divider"></a>
                                        <a href="#" class="dropdown-item">Separated link</a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <!-- <button type="button" class="btn btn-tool" data-lte-toggle="card-remove">
                                    <i class="bi bi-x-lg"></i>
                                </button> -->
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <div id="sales-chart"></div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end"> <span class="text-success"> <i
                                                class="bi bi-caret-up-fill"></i> 17%
                                        </span>
                                        <h5 class="fw-bold mb-0">$35,210.43</h5> <span class="text-uppercase">TOTAL
                                            REVENUE</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end"> <span class="text-info"> <i
                                                class="bi bi-caret-left-fill"></i> 0%
                                        </span>
                                        <h5 class="fw-bold mb-0">$10,390.90</h5> <span class="text-uppercase">TOTAL
                                            COST</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end"> <span class="text-success"> <i
                                                class="bi bi-caret-up-fill"></i> 20%
                                        </span>
                                        <h5 class="fw-bold mb-0">$24,813.53</h5> <span class="text-uppercase">TOTAL
                                            PROFIT</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center"> <span class="text-danger"> <i
                                                class="bi bi-caret-down-fill"></i> 18%
                                        </span>
                                        <h5 class="fw-bold mb-0">1200</h5> <span class="text-uppercase">GOAL
                                            COMPLETIONS</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div> <!-- /.col -->
                <!-- </div> -->
                <!--end::Row-->
                <!--begin::Row-->
                <!-- <div class="row"> -->
                <div class="col-md-4">
                    <!-- PRODUCT LIST -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <!-- <button type="button" class="btn btn-tool" data-lte-toggle="card-remove">
                                    <i class="bi bi-x-lg"></i>
                                </button> -->
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="px-2">

                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2"> <img src="../assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50"> </div>
                                    <div class="col-10"> <a href="javascript:void(0)" class="fw-bold">
                                            Samsung TV
                                            <span class="badge text-bg-warning float-end">
                                                $1800
                                            </span> </a>
                                        <div class="text-truncate">
                                            Samsung 32" 1080p 60Hz LED Smart HDTV.
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2"> <img src="../assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50"> </div>
                                    <div class="col-10"> <a href="javascript:void(0)" class="fw-bold">
                                            Bicycle
                                            <span class="badge text-bg-info float-end">
                                                $700
                                            </span> </a>
                                        <div class="text-truncate">
                                            26" Mongoose Dolomite Men's 7-speed, Navy Blue.
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2"> <img src="../assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50"> </div>
                                    <div class="col-10"> <a href="javascript:void(0)" class="fw-bold">
                                            Xbox One
                                            <span class="badge text-bg-danger float-end">
                                                $350
                                            </span> </a>
                                        <div class="text-truncate">
                                            Xbox One Console Bundle with Halo Master Chief
                                            Collection.
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2"> <img src="../assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50"> </div>
                                    <div class="col-10"> <a href="javascript:void(0)" class="fw-bold">
                                            PlayStation 4
                                            <span class="badge text-bg-success float-end">
                                                $399
                                            </span> </a>
                                        <div class="text-truncate">
                                            PlayStation 4 500GB Console (PS4)
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2"> <img src="../assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50"> </div>
                                    <div class="col-10"> <a href="javascript:void(0)" class="fw-bold">
                                            PlayStation 4
                                            <span class="badge text-bg-success float-end">
                                                $399
                                            </span> </a>
                                        <div class="text-truncate">
                                            PlayStation 4 500GB Console (PS4)
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- <div class="card-footer text-center">
                            <a href="javascript:void(0)" class="uppercase">
                                View All Products
                            </a>
                        </div> -->
                    </div>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->


<!-- OPTIONAL SCRIPTS -->
<!-- apexcharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<script>
    // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
    // IT'S ALL JUST JUNK FOR DEMO
    // ++++++++++++++++++++++++++++++++++++++++++

    const visitors_chart_options = {
        series: [{
            name: "High - 2023",
            data: [100, 120, 170, 167, 180, 177, 160],
        },
        {
            name: "Low - 2023",
            data: [60, 80, 70, 67, 80, 77, 100],
        },
        ],
        chart: {
            height: 200,
            type: "line",
            toolbar: {
                show: false,
            },
        },
        colors: ["#0d6efd", "#adb5bd"],
        stroke: {
            curve: "smooth",
        },
        grid: {
            borderColor: "#e7e7e7",
            row: {
                colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
                opacity: 0.5,
            },
        },
        legend: {
            show: false,
        },
        markers: {
            size: 1,
        },
        xaxis: {
            categories: ["22th", "23th", "24th", "25th", "26th", "27th", "28th"],
        },
    };

    const visitors_chart = new ApexCharts(
        document.querySelector("#visitors-chart"),
        visitors_chart_options
    );
    visitors_chart.render();

    const sales_chart_options = {
        series: [{
            name: "Net Profit",
            data: [44, 55, 57, 56, 61, 58, 63, 60, 66],
        },
        {
            name: "Revenue",
            data: [76, 85, 101, 98, 87, 105, 91, 114, 94],
        },
        {
            name: "Free Cash Flow",
            data: [35, 41, 36, 26, 45, 48, 52, 53, 41],
        },
        ],
        chart: {
            type: "bar",
            height: 200,
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "55%",
                endingShape: "rounded",
            },
        },
        legend: {
            show: false,
        },
        colors: ["#0d6efd", "#20c997", "#ffc107"],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 2,
            colors: ["transparent"],
        },
        xaxis: {
            categories: [
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
            ],
        },
        fill: {
            opacity: 1,
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "$ " + val + " thousands";
                },
            },
        },
    };

    const sales_chart = new ApexCharts(
        document.querySelector("#sales-chart"),
        sales_chart_options
    );
    sales_chart.render();
</script>
<!--end::Script-->