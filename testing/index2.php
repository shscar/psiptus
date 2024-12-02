<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Ambil data siswa dari database untuk digunakan di Select2
$stmt = $db->query("SELECT id, nis, nama_lengkap, kelas_id FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = $_POST['nis'];

    // Ambil informasi kelas dari siswa
    $kelasQuery = $db->prepare("SELECT kelas_id FROM siswa WHERE nis = :nis");
    $kelasQuery->execute(['nis' => $nis]);
    $kelas = $kelasQuery->fetch(PDO::FETCH_ASSOC);

    if ($kelas) {
        $kelas_id = $kelas['kelas_id'];

        // Ambil tarif SPP terkait kelas
        $tarifSPPQuery = $db->prepare("
            SELECT ts.id, ts.nama_tarif, ts.nominal 
            FROM tarif_spp ts 
            JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id 
            WHERE tsk.kelas_id = :kelas_id AND ts.status_aktif = 1
        ");
        $tarifSPPQuery->execute(['kelas_id' => $kelas_id]);
        $tarifSPP = $tarifSPPQuery->fetchAll(PDO::FETCH_ASSOC);

        // Ambil pembayaran lainnya terkait kelas
        $pembayaranLainnyaQuery = $db->prepare("
            SELECT spl.id, spl.nama_pembayaran, spl.nominal 
            FROM siswa_pembayaran_lainnya spl 
            JOIN siswa_pembayaran_lainnya_kelas splk ON spl.id = splk.siswa_pembayaran_lainnya_id 
            WHERE splk.kelas_id = :kelas_id AND spl.status_aktif = 1
        ");
        $pembayaranLainnyaQuery->execute(['kelas_id' => $kelas_id]);
        $pembayaranLainnya = $pembayaranLainnyaQuery->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'tarif_spp' => $tarifSPP,
            'pembayaran_lainnya' => $pembayaranLainnya,
        ]);
        exit;
    }
}

// Mengakhiri output buffering
ob_end_flush();
?>

<!-- Tambahkan stylesheet dan JavaScript untuk AdminLTE 4 dan Select2 -->
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet">
</main>

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
                            student paid fee
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
                            <form id="studentForm">
                                <!-- Dropdown untuk memilih siswa dengan Select2 -->
                                <div class="mb-3 row">
                                    <label for="student_name" class="col-sm-3 col-form-label">Nama Siswa</label>
                                    <div class="col-sm-9">
                                        <select id="student_name" class="form-control select2" name="student_name">
                                            <option value="">Pilih Nama Siswa</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?= $student['nis']; ?>"
                                                    data-kelas-id="<?= $student['kelas_id']; ?>">
                                                    <?= $student['nama_lengkap']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Input untuk menampilkan NIS setelah pemilihan siswa -->
                                <div class="mb-3 row">
                                    <label for="nis_siswa" class="col-sm-3 col-form-label">NIS Siswa</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="nis_siswa" name="nis_siswa"
                                            readonly>
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
                                        <input type="date" class="form-control" id="tanggalBayar">
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label for="pembayaran" class="col-sm-3 col-form-label">Pembayaran</label>
                                    <div class="col-sm-9">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#jenisPembayaranModal" id="openModal2">
                                            + Add Item
                                        </button>
                                    </div>
                                </div>

                                <!-- ... other fields ... -->
                                <hr>
                                <!-- Tabel List Item Pengeluaran -->
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
                                            <!-- Row akan ditambahkan secara dinamis oleh JavaScript -->
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
                                                    id="total-bayar">
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
                                        <sth>Dibayar</sth>
                                        <th>Kurang</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layouts Table -->
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
                </div>
            </div>

        </div>
    </div>
</main>
<!--end::App Main-->

<!-- DataTables JS -->
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
    $(document).ready(function () {

        $('#jenisPembayaranTable').DataTable();

        // Inisialisasi Select2 untuk data siswa
        $('#createDataModal').on('shown.bs.modal', function () {
            $('#student_name').select2({
                placeholder: 'Pilih Nama Siswa',
                dropdownParent: $('#createDataModal')
            });
        });

        // Initialize modals
        var createDataModal = new bootstrap.Modal(document.getElementById('createDataModal'));
        var jenisPembayaranModal = new bootstrap.Modal(document.getElementById('jenisPembayaranModal'));

        // Open second modal and hide the first
        document.getElementById('openModal2').addEventListener('click', function () {
            createDataModal.hide();
            jenisPembayaranModal.show();
        });

        // Handle closing of second modal
        document.getElementById('jenisPembayaranModal').addEventListener('hidden.bs.modal', function () {
            createDataModal.show();
        });

        // Fetch and populate data in the second modal
        $('#student_name').on('change', function () {
            const nis = $(this).val();
            $('#nis_siswa').val(nis);

            if (nis) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        nis: nis
                    },
                    dataType: 'json',
                    success: function (response) {
                        const table = $('#jenisPembayaranTable').DataTable();
                        table.clear();

                        let index = 1;
                        response.tarif_spp.forEach(function (item) {
                            table.row.add([
                                index++,
                                item.nama_tarif,
                                item.nominal,
                                '0',
                                item.nominal,
                                '<button class="btn btn-success btn-sm pilihBtn">+ Pilih</button>'
                            ]).draw();
                        });

                        response.pembayaran_lainnya.forEach(function (item) {
                            table.row.add([
                                index++,
                                item.nama_pembayaran,
                                item.nominal,
                                '0',
                                item.nominal,
                                '<button class="btn btn-success btn-sm pilihBtn">+ Pilih</button>'
                            ]).draw();
                        });
                    }
                });
            }
        });

        $(document).on('click', '.pilihBtn', function () {
            const row = $(this).closest('tr');
            const jenis = row.find('td:eq(1)').text();
            alert(`Jenis Pembayaran "${jenis}" telah dipilih.`);
        });
    });

    // Handle "Pilih" button click in the jenisPembayaranTable
    $('#jenisPembayaranTable').on('click', '.pilihBtn', function () {
        var row = $(this).closest('tr');
        var jenisPembayaran = row.find('td:nth-child(2)').text();
        var tagihan = row.find('td:nth-child(3)').text();
        var dibayar = row.find('td:nth-child(4)').text();
        var kurang = row.find('td:nth-child(5)').text();

        // Check if already selected to prevent duplicates
        if ($('#selectedPembayaran').find(`[data-jenis="${jenisPembayaran}"]`).length === 0) {
            $('#selectedPembayaran').append(`
            <button class="btn btn-outline-success me-2" data-jenis="${jenisPembayaran}">
                ${jenisPembayaran} <span class="removeItem">&times;</span>
            </button>
        `);

            // Append to row-item-bayar in the main table
            $('#tabel-list-item-pengeluaran tbody').append(`
            <tr class="row-item-bayar">
                <td>${$('#tabel-list-item-pengeluaran tbody tr').length + 1}</td>
                <td><label for="jenis" class="form-label">${jenisPembayaran}</label></td>
                <td><label for="tagihan" class="form-label">${tagihan}</label></td>
                <td><input type="number" class="form-control" name="jumlah_bayar[]" required></td>
            </tr>
        `);
        }
    });

    // Remove selected item on click
    $('#selectedPembayaran').on('click', '.removeItem', function () {
        var jenisPembayaran = $(this).closest('button').data('jenis');
        $(this).closest('button').remove();

        // Remove corresponding row in the main table
        $('#tabel-list-item-pengeluaran tbody .row-item-bayar').filter(function () {
            return $(this).find('td:nth-child(2) label').text() === jenisPembayaran;
        }).remove();

        // Reorder row numbers
        $('#tabel-list-item-pengeluaran tbody .row-item-bayar').each(function (index) {
            $(this).find('td:first-child').text(index + 1);
        });
    });
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>