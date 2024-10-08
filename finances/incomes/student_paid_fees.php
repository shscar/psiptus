<!-- DataTables CSS -->
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" /> -->

<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" /> -->
<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Ambil data tagihan dan pembayaran siswa
$stmt = $db->prepare("
    SELECT spl.nama_pembayaran, spl.nominal AS nilai_tagihan, 
        IFNULL(SUM(ps.jumlah_bayar), 0) AS dibayar, 
        (spl.nominal - IFNULL(SUM(ps.jumlah_bayar), 0)) AS kurang
    FROM siswa_pembayaran_lainnya spl
    LEFT JOIN pembayaran_spp ps ON spl.id = ps.tarif_spp_id
    WHERE spl.status_aktif = 1
    GROUP BY spl.id, spl.nama_pembayaran, spl.nominal
");
$stmt->execute();
$tagihanData = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Mengakhiri output buffering
ob_end_flush();
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Pembayaran Siswa</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            student fee
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

            <!-- Modal Structure -->
            <div class="modal fade" id="createDataModal" tabindex="-1" aria-labelledby="createDataModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createDataModalLabel">Create Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3 row">
                                    <label for="namaSiswa" class="col-sm-3 col-form-label">Nama Siswa</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="namaSiswa"
                                                placeholder="Astari Budi Handayani">
                                            <button class="btn btn-outline-secondary" type="button" id="cariSiswaBtn">
                                                <i class="bi bi-search"></i>
                                                Search
                                            </button>
                                            <!-- <button class="btn btn-success" type="button" id="tambahSiswaBtn">+
                                                Tambah</button> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="noInvoice" class="col-sm-3 col-form-label">No. Invoice</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="noInvoice"
                                            placeholder="Digenerate otomatis oleh sistem" readonly>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="tanggalBayar" class="col-sm-3 col-form-label">Tanggal Bayar</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" id="tanggalBayar" value="2024-09-26">
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="pembayaran" class="col-sm-3 col-form-label">Pembayaran</label>
                                    <div class="col-sm-9">
                                        <!-- <button class="btn btn-success" type="button" id="addItemBtn">
                                            + Add Item
                                        </button> -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#jenisPembayaranModal" id="openModal2">
                                            + Add Item
                                        </button>
                                    </div>
                                </div>

                                <!-- ... other fields ... -->
                                <hr>
                                <div class="form-group">
                                    <table class="table table-striped table-bordered" id="tabel-list-item-pengeluaran">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Pembayaran</th>
                                                <th>Tagihan</th>
                                                <th>Jumlah Bayar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="row-item-bayar">
                                                <td>1</td>
                                                <td>
                                                    <label for="jenis" class="form-label">SPP Februari 2023</label>
                                                </td>
                                                <td>
                                                    <label for="tagihan" class="form-label">350.000</label>
                                                </td>
                                                <td>
                                                    <input type="jumlah_bayar" class="form-control"
                                                        name="jumlah_bayar[]" required>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr id="row-total-bayar">
                                                <td></td>
                                                <td colspan="2">
                                                    <div class="d-flex justify-content-between">
                                                        Total
                                                        <select name="jenis_bayar" class="form-select"
                                                            style="width:auto">
                                                            <option value="1">Tunai</option>
                                                            <option value="2">Transfer</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold" style="padding-right:17px"
                                                    id="total-item-nilai-bayar">
                                                    0
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Item Modal (Jenis Pembayaran) -->
            <div class="modal fade" id="jenisPembayaranModal" tabindex="-1" aria-labelledby="jenisPembayaranLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="jenisPembayaranLabel">Pilih Jenis Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Area for selected items -->
                            <div id="selectedPembayaran" class="d-flex mb-3">
                                <!-- Selected items will be added here dynamically -->
                            </div>

                            <!-- DataTables -->
                            <table id="jenisPembayaranTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Nilai Tagihan</th>
                                        <th>Dibayar</th>
                                        <th>Kurang</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>SPP Januari 2023</td>
                                        <td>350.000</td>
                                        <td>0</td>
                                        <td>350.000</td>
                                        <td><button class="btn btn-success btn-sm pilihBtn">+ Pilih</button></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>SPP Februari 2023</td>
                                        <td>350.000</td>
                                        <td>0</td>
                                        <td>350.000</td>
                                        <td><button class="btn btn-success btn-sm pilihBtn">+ Pilih</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Grade Level </h3>
                    <button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal"
                        data-bs-target="#createDataModal">
                        <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                    </button>

                </div>
                <div class="card-body">

                    <!-- DataTables -->
                    <table id="content" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Jenis Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Alberd</td>
                                <td>XI Teknik</td>
                                <td>25 Sep 2024</td>
                                <td>
                                    <ul>
                                        <li>ATS</li>
                                        <li>UKK</li>
                                    </ul>
                                </td>
                                <td>
                                    <button class="btn btn-success">Detail</button>
                                    <button class="btn btn-warning">Edit</button>
                                    <button class="btn btn-danger">Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Nightcore</td>
                                <td>XII Bisnis</td>
                                <td>23 Sep 2024</td>
                                <td>
                                    <ul>
                                        <li>Daftar Ulang</li>
                                        <li>Buku</li>
                                        <li>Seragam</li>
                                    </ul>
                                </td>
                                <td>
                                    <button class="btn btn-success">Detail</button>
                                    <button class="btn btn-warning">Edit</button>
                                    <button class="btn btn-danger">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
    // document.getElementById('jenisPembayaranTable').addEventListener('click', function () {
    //     var pembayaranModal = new bootstrap.Modal(document.getElementById('jenisPembayaranModal'));
    //     pembayaranModal.show();
    // });

    $(document).ready(function () {
        // Initialize DataTable
        $('#jenisPembayaranTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });

        // Initialize DataTable
        $('#content').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });

        // Ketika tombol untuk membuka modal kedua diklik
        document.getElementById('openModal2').addEventListener('click', function () {
            // Sembunyikan modal pertama, tapi jangan ditutup
            var createDataModal = new bootstrap.Modal(document.getElementById('createDataModal'));
            createDataModal.hide();

            // Tampilkan modal kedua
            var jenisPembayaranModal = new bootstrap.Modal(document.getElementById('jenisPembayaranModal'));
            jenisPembayaranModal.show();
        });

        // Ketika modal kedua ditutup
        document.getElementById('jenisPembayaranModal').addEventListener('hidden.bs.modal', function () {
            // Buka kembali modal pertama setelah modal kedua ditutup
            var createDataModal = new bootstrap.Modal(document.getElementById('createDataModal'));
            createDataModal.show();
        });


        // Handle "Pilih" button click
        $('#jenisPembayaranTable').on('click', '.pilihBtn', function () {
            // Get row data
            var row = $(this).closest('tr');
            var jenisPembayaran = row.find('td:nth-child(2)').text();

            // Check if already selected
            if ($('#selectedPembayaran').find(`[data-jenis="${jenisPembayaran}"]`).length === 0) {
                // Add selected item to "selectedPembayaran" div
                $('#selectedPembayaran').append(`
                    <button class="btn btn-outline-success me-2" data-jenis="${jenisPembayaran}">
                        ${jenisPembayaran} <span class="removeItem">&times;</span>
                    </button>
                `);
            }

            // Remove selected item on click
            $('#selectedPembayaran').on('click', '.removeItem', function () {
                $(this).closest('button').remove();
            });
        });
    });
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>