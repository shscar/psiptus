<!--begin::Third Party Plugin(OverlayScrollbars)-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css"
    integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous">
<!--end::Third Party Plugin(OverlayScrollbars)-->

<?php

// Menu aktif untuk dropdown
$dropdownRoutes = [
    'sid-01' => [
        '/tahun-ajaran',
        '/kelas',
        '/tingkat-kelas',
        'siswa-i' => ['/siswa', '/siswa/tambah-siswa', '/siswa/edit-siswa']
    ],
    'sid-02' => [
        '/pendapatan/pembayaran-siswa',
        '/pendapatan/tagihan-spp-siswa',
        '/pendapatan/tagihan-lain-siswa',
    ],
    'sid-03' => [
        '/pendapatan/pemasukan-lain',
    ],
    'sid-04' => [
        '/pendapatan/pemasukan',
        '/pengeluaran/kategori-pengeluaran',
        '/pengeluaran/detail-pengeluaran'
    ],
];

$activeDropdown = null;
$isActiveFound = false;

foreach ($dropdownRoutes as $key => $routes) {
    if (isAnyActive($routes, $requestUri)) {
        $activeDropdown = $key;
        $isActiveFound = true;
        break;
    }
}

?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="./index.html" class="brand-link">
            <img src="../assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">Admin</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link <?php echo isActive('/dashboard', $requestUri); ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/guru-staff" class="nav-link <?php echo isActive('/guru-staff', $requestUri); ?>">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Guru dan Staff</p>
                    </a>
                </li>
                <li class="nav-item dropdown <?php echo $activeDropdown === 'sid-01' ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $activeDropdown === 'sid-01' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-mortarboard"></i>
                        <p>
                            Akademik
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview"
                        style="<?php echo $activeDropdown === 'sid-01' ? 'display: block;' : 'display: none;'; ?>">
                        <li class="nav-item">
                            <a href="/siswa"
                                class="nav-link ms-3 <?php echo isActive($dropdownRoutes['sid-01']['siswa-i'], $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Data Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/tahun-ajaran"
                                class="nav-link ms-3 <?php echo isActive('/tahun-ajaran', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Tahun Ajaran</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/tingkat-kelas"
                                class="nav-link ms-3 <?php echo isActive('/tingkat-kelas', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Grub Kelas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/kelas" class="nav-link ms-3 <?php echo isActive('/kelas', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Kelas</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown <?php echo $activeDropdown === 'sid-02' ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $activeDropdown === 'sid-02' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>
                            Tagihan Siswa
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview"
                        style="<?php echo $activeDropdown === 'sid-02' ? 'display: block;' : 'display: none;'; ?>">
                        <li class="nav-item">
                            <a href="/pendapatan/tagihan-spp-siswa"
                                class="nav-link ms-3 <?php echo isActive('/pendapatan/tagihan-spp-siswa', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Tagihan SPP Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/pendapatan/tagihan-lain-siswa"
                                class="nav-link ms-3 <?php echo isActive('/pendapatan/tagihan-lain-siswa', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Tagihan Lain Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/pendapatan/pembayaran-siswa"
                                class="nav-link ms-3 <?php echo isActive('/pendapatan/pembayaran-siswa', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Pembayaran Siswa</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- <li class="nav-item dropdown <?php echo $activeDropdown === 'sid-03' ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $activeDropdown === 'sid-03' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>
                            Pendapatan
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview"
                        style="<?php echo $activeDropdown === 'sid-03' ? 'display: block;' : 'display: none;'; ?>">

                        <li class="nav-item">
                            <a href="/pendapatan/pemasukan-lain"
                                class="nav-link ms-3 <?php echo isActive('/pendapatan/pemasukan-lain', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Pendapatan lain-lain</p>
                            </a>
                        </li>
                    </ul>
                </li> -->
                <li class="nav-item dropdown <?php echo $activeDropdown === 'sid-04' ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $activeDropdown === 'sid-04' ? 'active' : ''; ?>">
                        <i class="nav-icon bi bi-cash-stack"></i>
                        <p>
                            Pembiayaan
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview"
                        style="<?php echo $activeDropdown === 'sid-04' ? 'display: block;' : 'display: none;'; ?>">
                        <li class="nav-item">
                            <a href="/pendapatan/pemasukan"
                                class="nav-link ms-3 <?php echo isActive('/pendapatan/pemasukan', $requestUri); ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Pendapatan Dana</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/pengeluaran/detail-pengeluaran"
                                class="nav-link ms-3 <?php echo (isActive('/pengeluaran/detail-pengeluaran', $requestUri) || isActive('/pengeluaran/kategori-pengeluaran', $requestUri)) ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Pengeluaran</p>
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-cart3"></i>
                                <p>Belanja</p>
                            </a>
                        </li> -->
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="/test" target="_blank" class="nav-link">
                        <i class="nav-icon bi bi-arrow-bar-right"></i>
                        <p>Maintance/test</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>


<!--begin::Third Party Plugin(OverlayScrollbars)-->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"
    integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script>

<!--begin::OverlayScrollbars Configure-->
<script>
    const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
    const Default = {
        scrollbarTheme: "os-theme-light",
        scrollbarAutoHide: "leave",
        scrollbarClickScroll: true,
    };
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (
            sidebarWrapper &&
            typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
        ) {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }
    });
</script>
<!--end::OverlayScrollbars Configure-->