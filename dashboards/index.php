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
$stmt = $db->prepare("SELECT SUM(nominal) AS total_nominal FROM pemasukan_dana_bos");
$stmt->execute();
$total = $stmt->fetchColumn();

// menghitung total dari total_jumlah
$stmt = $db->query("SELECT SUM(total_jumlah) AS total FROM pengeluaran_dana");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalJumlah = $result['total'] ? $result['total'] : 0;


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
                                Rp. <?php echo number_format($total, 2, ',', '.'); ?>
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
                                Rp <?php echo number_format($totalJumlah, 2, ',', '.'); ?>
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
                            <div class="card-tools"> <button type="button" class="btn btn-tool"
                                    data-lte-toggle="card-collapse"> <i data-lte-icon="expand"
                                        class="bi bi-plus-lg"></i> <i data-lte-icon="collapse"
                                        class="bi bi-dash-lg"></i> </button>
                                <div class="btn-group"> <button type="button" class="btn btn-tool dropdown-toggle"
                                        data-bs-toggle="dropdown"> <i class="bi bi-wrench"></i> </button>
                                    <div class="dropdown-menu dropdown-menu-end" role="menu"> <a href="#"
                                            class="dropdown-item">Action</a> <a href="#" class="dropdown-item">Another
                                            action</a> <a href="#" class="dropdown-item">
                                            Something else here
                                        </a> <a class="dropdown-divider"></a> <a href="#"
                                            class="dropdown-item">Separated link</a> </div>
                                </div> <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"> <i
                                        class="bi bi-x-lg"></i> </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-9">
                                    <p class="text-center"> <strong>Sales: 1 Jan, 2023 - 30 Jul, 2023</strong> </p>
                                    <div id="sales-chart"></div>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-center"> <strong>Goal Completion</strong> </p>
                                    <div class="progress-group">
                                        Add Products to Cart
                                        <span class="float-end"><b>160</b>/200</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-primary" style="width: 80%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        Complete Purchase
                                        <span class="float-end"><b>310</b>/400</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-danger" style="width: 75%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group"> <span class="progress-text">Visit Premium Page</span>
                                        <span class="float-end"><b>480</b>/800</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-success" style="width: 60%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        Send Inquiries
                                        <span class="float-end"><b>250</b>/500</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-warning" style="width: 50%"></div>
                                        </div>
                                    </div>
                                </div>
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
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove">
                                    <i class="bi bi-x-lg"></i>
                                </button>
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

                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" class="uppercase">
                                View All Products
                            </a>
                        </div>
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
    const sales_chart_options = {
        series: [{
            name: "Digital Goods",
            data: [28, 48, 40, 19, 86, 27, 90],
        },
        {
            name: "Electronics",
            data: [65, 59, 80, 81, 56, 55, 40],
        },
        ],
        chart: {
            height: 180,
            type: "area",
            toolbar: {
                show: false,
            },
        },
        legend: {
            show: false,
        },
        colors: ["#0d6efd", "#20c997"],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
        },
        xaxis: {
            type: "datetime",
            categories: [
                "2023-01-01",
                "2023-02-01",
                "2023-03-01",
                "2023-04-01",
                "2023-05-01",
                "2023-06-01",
                "2023-07-01",
            ],
        },
        tooltip: {
            x: {
                format: "MMMM yyyy",
            },
        },
    };

    const sales_chart = new ApexCharts(
        document.querySelector("#sales-chart"),
        sales_chart_options,
    );
    sales_chart.render();

    //---------------------------
    // - END MONTHLY SALES CHART -
    //---------------------------

    function createSparklineChart(selector, data) {
        const options = {
            series: [{
                data
            }],
            chart: {
                type: "line",
                width: 150,
                height: 30,
                sparkline: {
                    enabled: true,
                },
            },
            colors: ["var(--bs-primary)"],
            stroke: {
                width: 2,
            },
            tooltip: {
                fixed: {
                    enabled: false,
                },
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: function (seriesName) {
                            return "";
                        },
                    },
                },
                marker: {
                    show: false,
                },
            },
        };

        const chart = new ApexCharts(document.querySelector(selector), options);
        chart.render();
    }

    const table_sparkline_1_data = [
        25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54,
    ];
    const table_sparkline_2_data = [
        12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 44,
    ];
    const table_sparkline_3_data = [
        15, 46, 21, 59, 33, 15, 34, 42, 56, 19, 64,
    ];
    const table_sparkline_4_data = [
        30, 56, 31, 69, 43, 35, 24, 32, 46, 29, 64,
    ];
    const table_sparkline_5_data = [
        20, 76, 51, 79, 53, 35, 54, 22, 36, 49, 64,
    ];
    const table_sparkline_6_data = [
        5, 36, 11, 69, 23, 15, 14, 42, 26, 19, 44,
    ];
    const table_sparkline_7_data = [
        12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 74,
    ];

    createSparklineChart("#table-sparkline-1", table_sparkline_1_data);
    createSparklineChart("#table-sparkline-2", table_sparkline_2_data);
    createSparklineChart("#table-sparkline-3", table_sparkline_3_data);
    createSparklineChart("#table-sparkline-4", table_sparkline_4_data);
    createSparklineChart("#table-sparkline-5", table_sparkline_5_data);
    createSparklineChart("#table-sparkline-6", table_sparkline_6_data);
    createSparklineChart("#table-sparkline-7", table_sparkline_7_data);

    //-------------
    // - PIE CHART -
    //-------------

    const pie_chart_options = {
        series: [700, 500, 400, 600, 300, 100],
        chart: {
            type: "donut",
        },
        labels: ["Chrome", "Edge", "FireFox", "Safari", "Opera", "IE"],
        dataLabels: {
            enabled: false,
        },
        colors: [
            "#0d6efd",
            "#20c997",
            "#ffc107",
            "#d63384",
            "#6f42c1",
            "#adb5bd",
        ],
    };

    const pie_chart = new ApexCharts(
        document.querySelector("#pie-chart"),
        pie_chart_options,
    );
    pie_chart.render();

    //-----------------
    // - END PIE CHART -
    //-----------------
</script>
<!--end::Script-->