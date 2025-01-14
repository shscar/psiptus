<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        try {
            // Mulai transaksi
            $db->beginTransaction();

            // Ambil data dari form
            $siswa_id = $_POST['student_id'];
            $tanggal_bayar = $_POST['tanggal_bayar'];
            $jenis_bayar = $_POST['jenis_bayar'];
            $no_invoice = 'INV-' . time(); // Generate No. Invoice
            $total_bayar = 0;

            // Hitung total bayar dari jumlah_bayar[]
            if (isset($_POST['jumlah_bayar']) && is_array($_POST['jumlah_bayar'])) {
                foreach ($_POST['jumlah_bayar'] as $jumlah) {
                    $total_bayar += floatval($jumlah);
                }
            }

            // Masukkan data ke tabel riwayat_transaksi_siswa
            $stmt = $db->prepare("INSERT INTO riwayat_transaksi_siswa 
                (siswa_id, no_invoice, tanggal_bayar, jenis_bayar, total_bayar) 
                VALUES (:siswa_id, :no_invoice, :tanggal_bayar, :jenis_bayar, :total_bayar)");
            $stmt->execute([
                ':siswa_id' => $siswa_id,
                ':no_invoice' => $no_invoice,
                ':tanggal_bayar' => $tanggal_bayar,
                ':jenis_bayar' => $jenis_bayar,
                ':total_bayar' => $total_bayar
            ]);

            // Dapatkan ID dari transaksi yang baru dimasukkan
            $riwayat_transaksi_id = $db->lastInsertId();

            // Masukkan data ke tabel riwayat_transaksi_siswa_detail_tarifspp
            if (isset($_POST['item_type']) && is_array($_POST['item_type'])) {
                foreach ($_POST['item_type'] as $key => $type) {
                    $item_id = $_POST['item_id'][$key];
                    $jumlah_bayar = floatval($_POST['jumlah_bayar'][$key]);

                    if ($type === 'tarif_spp') {
                        $stmt = $db->prepare("INSERT INTO riwayat_transaksi_siswa_detail_tarifspp 
                            (riwayat_transaksi_id, tarif_spp_id, jumlah_bayar) 
                            VALUES (:riwayat_transaksi_id, :tarif_spp_id, :jumlah_bayar)");
                        $stmt->execute([
                            ':riwayat_transaksi_id' => $riwayat_transaksi_id,
                            ':tarif_spp_id' => $item_id,
                            ':jumlah_bayar' => $jumlah_bayar
                        ]);
                    }

                    // Masukkan data ke tabel riwayat_transaksi_siswa_detail_pembayaranlain
                    if ($type === 'pembayaran_lainnya') {
                        $stmt = $db->prepare("INSERT INTO riwayat_transaksi_siswa_detail_pembayaranlain 
                            (riwayat_transaksi_id, pembayaran_lainnya_id, jumlah_bayar) 
                            VALUES (:riwayat_transaksi_id, :pembayaran_lain_id, :jumlah_bayar)");
                        $stmt->execute([
                            ':riwayat_transaksi_id' => $riwayat_transaksi_id,
                            ':pembayaran_lain_id' => $item_id,
                            ':jumlah_bayar' => $jumlah_bayar
                        ]);
                    }
                }
            }

            // Commit transaksi
            $db->commit();

            // Redirect atau tampilkan pesan sukses
            echo "<script>
                alert('Data telah berhasil masuk.');
                window.location.href = '/pendapatan/pembayaran-siswa';
            </script>";
        } catch (Exception $e) {
            // Rollback transaksi jika ada kesalahan
            $db->rollBack();
            echo "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    // Delete transaction data
    if ($action == 'delete') {
        $riwayatId = $_POST['id'] ?? null;

        if (!empty($riwayatId)) {
            try {
                // Start a transaction
                $db->beginTransaction();

                // Delete associated detail records
                $deleteTarifStmt = $db->prepare("DELETE FROM riwayat_transaksi_siswa_detail_tarifspp WHERE riwayat_transaksi_id = :id");
                $deleteTarifStmt->execute([':id' => $riwayatId]);

                $deletePembayaranStmt = $db->prepare("DELETE FROM riwayat_transaksi_siswa_detail_pembayaranlain WHERE riwayat_transaksi_id = :id");
                $deletePembayaranStmt->execute([':id' => $riwayatId]);

                // Delete the main transaction record
                $deleteMainStmt = $db->prepare("DELETE FROM riwayat_transaksi_siswa WHERE id = :id");
                $deleteMainStmt->execute([':id' => $riwayatId]);

                if ($deleteMainStmt->rowCount() > 0) {
                    $db->commit(); // Commit the transaction if successful
                    echo "<script>
                        alert('Data berhasil dihapus.');
                        window.location.href = '/pendapatan/pembayaran-siswa';
                    </script>";
                    exit();
                } else {
                    $db->rollBack(); // Rollback if no rows were affected
                    echo "<script>alert('Data tidak ditemukan.');</script>";
                }
            } catch (Exception $e) {
                $db->rollBack(); // Rollback in case of error
                echo "<script>alert('Kesalahan server: " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('Parameter tidak ditemukan.');</script>";
        }
    }
}

// Ambil data siswa dari database untuk digunakan di Select2
$stmt = $db->query("SELECT id, nis, nama_lengkap, kelas_id FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT 
        rts.id AS riwayat_id,
        rtt.id AS detail_tarifspp_id,
        rtp.id AS detail_pembayaranlain_id,
        s.nis,
        s.nama_lengkap,
        k.nama_kelas,
        rts.tanggal_bayar,
        rts.no_invoice,
        rts.jenis_bayar,
        rts.total_bayar,
        
        COUNT(*) AS total_transaksi, 
        SUM(rts.total_bayar) AS total_dana_masuk,

        ts.nama_tarif AS tarif_spp,
        rtt.tarif_spp_id,
        rtt.jumlah_bayar AS jmlb_tfs,
        spl.nama_pembayaran AS pembayaran_lainnya,
        rtp.pembayaran_lainnya_id,
        rtp.jumlah_bayar AS jmlb_pyl,
        ts.nominal AS nominal_spp,
        spl.nominal AS nominal_lainnya,
        (ts.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar_spp,
        (spl.nominal - COALESCE(SUM(drl.jumlah_bayar), 0)) AS kurang_bayar_lainnya
    FROM riwayat_transaksi_siswa rts
    LEFT JOIN siswa s ON rts.siswa_id = s.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp rtt ON rts.id = rtt.riwayat_transaksi_id
    LEFT JOIN tarif_spp ts ON rtt.tarif_spp_id = ts.id
    LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain rtp ON rts.id = rtp.riwayat_transaksi_id
    LEFT JOIN siswa_pembayaran_lainnya spl ON rtp.pembayaran_lainnya_id = spl.id
    LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp drt ON ts.id = drt.tarif_spp_id AND rts.id = drt.riwayat_transaksi_id
    LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain drl ON spl.id = drl.pembayaran_lainnya_id AND rts.id = drl.riwayat_transaksi_id
    GROUP BY rts.id, rtt.id, rtt.tarif_spp_id, rtp.id, rtp.pembayaran_lainnya_id
    ORDER BY rts.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengelompokkan data berdasarkan `riwayat_id`
$combinedResults = [];
foreach ($results as $row) {
    $riwayatId = $row['riwayat_id'];
    if (!isset($combinedResults[$riwayatId])) {
        $combinedResults[$riwayatId] = [
            'riwayat_id' => $row['riwayat_id'],
            'nis' => $row['nis'],
            'nama_lengkap' => $row['nama_lengkap'],
            'nama_kelas' => $row['nama_kelas'],
            'no_invoice' => $row['no_invoice'],
            'tanggal_bayar' => $row['tanggal_bayar'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total_bayar' => $row['total_bayar'],
            'total_transaksi' => $row['total_transaksi'],
            'total_dana_masuk' => $row['total_dana_masuk'],
            'detail_tarifspp_ids' => [],
            'detail_pembayaranlain_ids' => [],
            'items' => [],
        ];
    }
    if ($row['detail_tarifspp_id']) {
        $combinedResults[$riwayatId]['items'][] = [
            'id' => $row['detail_tarifspp_id'],
            'jenis_bayar' => 'TS: ' . $row['tarif_spp'],
            'jumlah_bayar' => number_format($row['jmlb_tfs'], 0, ',', '.'),
            'kurang_bayar' => number_format($row['kurang_bayar_spp'], 0, ',', '.'),
        ];
    }
    if ($row['detail_pembayaranlain_id']) {
        $combinedResults[$riwayatId]['items'][] = [
            'id' => $row['detail_pembayaranlain_id'],
            'jenis_bayar' => 'PL: ' . $row['pembayaran_lainnya'],
            'jumlah_bayar' => number_format($row['jmlb_pyl'], 0, ',', '.'),
            'kurang_bayar' => number_format($row['kurang_bayar_lainnya'], 0, ',', '.'),
        ];
    }

    // Menghitung total transaksi dengan memeriksa apakah $results tidak kosong
    $totalTransaksi = 0; // Inisialisasi variabel totalTransaksi
    if (!empty($results)) {
        foreach ($results as $row) {
            $totalTransaksi += $row['total_transaksi']; // Menjumlahkan total transaksi
        }
    }

    $totaldanamasuk = 0; // Inisialisasi variabel totalTransaksi
    if (!empty($results)) {
        foreach ($results as $row) {
            $totaldanamasuk += $row['total_dana_masuk']; // Menjumlahkan total transaksi
        }
    }

}

// echo '<pre>';
// print_r($combinedResults);
// echo '</pre>';

// Menghitung total siswa dan siswa aktif
$stmt = $db->prepare("
    SELECT 
        COUNT(*) AS total_siswa,
        COUNT(CASE WHEN status = 'Aktif' THEN 1 END) AS total_siswa_aktif
    FROM siswa
");
$stmt->execute();
$menghitungsiswa = $stmt->fetch(PDO::FETCH_ASSOC);
// Menyimpan hasil ke dalam variabel
$totalSiswa = $menghitungsiswa['total_siswa'];
$totalSiswaAktif = $menghitungsiswa['total_siswa_aktif'];

// Menghitung total child dari tabel kelas
$stmtChild = $db->prepare("SELECT COUNT(*) AS total_child FROM kelas");
$stmtChild->execute();
$resultChild = $stmtChild->fetch(PDO::FETCH_ASSOC);
$totalChild = $resultChild['total_child'];

// Menghitung total grub dari tabel tingkat_kelas
$stmtGrub = $db->prepare("SELECT COUNT(*) AS total_grub FROM tingkat_kelas");
$stmtGrub->execute();
$resultGrub = $stmtGrub->fetch(PDO::FETCH_ASSOC);
$totalGrub = $resultGrub['total_grub'];


// Mengakhiri output buffering
ob_end_flush();
?>
<script>
    const combinedResults = <?php echo json_encode($combinedResults); ?>;
</script>

<style>
    td {
        padding: 20px;
        background: #eaeaea;
        max-width: 400px;
        margin: 50px auto;
    }

    .list-circle {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- DataTables CSS/JS Dependencies -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<body>
    <!--begin::App Main-->
    <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Pembayaran Siswa

                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info dropdown-toggle rounded-5"
                                        data-bs-toggle="dropdown" aria-expanded="false">?</button>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <i class="bi bi-dash me-2"></i>
                                            Detail
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-check2 me-2"></i>
                                            Aksi Create
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-x me-2"></i>
                                            Aksi Edit
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-check2 me-2"></i>
                                            Aksi Delete
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-x me-2"></i>
                                            Export
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-x me-2"></i>
                                            Import
                                        </li>
                                        <li class="dropdown-item">
                                            <i class="bi bi-dash me-2"></i>
                                            Print
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </h3>
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

                <!-- card -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-primary shadow-sm">
                                <i class="bi bi-book-fill"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kelas Child/Grub</span>
                                <span class="info-box-number">
                                    <?= $totalChild; ?> / <?= $totalGrub; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-success shadow-sm">
                                <i class="bi bi-people-fill"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Siswa Aktiv/NonAktiv</span>
                                <span class="info-box-number">
                                    <?= $totalSiswaAktif; ?> / <?= $totalSiswa; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-danger shadow-sm">
                                <i class="bi bi-wallet-fill"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Transaksi</span>
                                <span class="info-box-number">
                                    <?= $totalTransaksi; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-bg-warning shadow-sm">
                                <i class="bi bi-cash"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Dana Masuk</span>
                                <span class="info-box-number">
                                    Rp. <?= number_format($totaldanamasuk); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Layouts Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Grade Level </h3>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addDataModal">
                                <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                            </button>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addDataModal">
                                <i class="bi bi-file-earmark-arrow-down pe-1"></i> Export Tags perKelas
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- DataTables -->
                        <table id="DataPembayaranSiswa" class="table table-bordered table-striped">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <?php if (!empty($combinedResults)): ?>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php foreach ($combinedResults as $row): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++; ?></td>
                                            <td>
                                                <div>
                                                    <?= date('d M Y', strtotime($row['tanggal_bayar'])) ?? '-'; ?>
                                                </div>
                                            </td>
                                            <td><?= $row['nama_lengkap'] ?? '-'; ?></td>
                                            <td><?= $row['nama_kelas'] ?? '-'; ?></td>
                                            </td>
                                            <td>
                                                <ul class="list-circle m-0">
                                                    <?php foreach ($row['items'] as $item): ?>
                                                        <li><?= $item['jenis_bayar'] ?? '-'; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                            <td>
                                                <div class="text-start">Rp.
                                                    <?= number_format($row['total_bayar'], 2) ?? '-'; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-link p-0" type="button"
                                                        id="settings-<?= $kat['id'] ?>" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="settings-<?= $kat['id'] ?>">
                                                        <li>
                                                            <button class="dropdown-item" data-bs-toggle="modal"
                                                                data-bs-target="#showModal"
                                                                data-bs-id="<?= $row['riwayat_id']; ?>"
                                                                data-bs-nama_lengkap="<?= $row['nama_lengkap']; ?>"
                                                                data-bs-nis="<?= $row['nis']; ?>"
                                                                data-bs-nama_kelas="<?= $row['nama_kelas']; ?>"
                                                                data-bs-no_invoice="<?= $row['no_invoice']; ?>"
                                                                data-bs-tanggal="<?= $row['tanggal_bayar']; ?>"
                                                                data-bs-jenis_bayar="<?= $row['jenis_bayar']; ?>"
                                                                data-bs-total_bayar="<?= $row['total_bayar']; ?>"
                                                                data-bs-items='<?= json_encode($row['items']); ?>'>
                                                                <i class="bi bi-list-stars me-2"></i>
                                                                Detail
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" data-bs-toggle="modal">
                                                                <i class="bi bi-pencil me-2"></i>
                                                                Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" data-bs-toggle="modal"
                                                                data-bs-target="#deleteModal"
                                                                data-bs-id="<?= $row['riwayat_id']; ?>"
                                                                data-bs-tanggal="<?= $row['tanggal_bayar']; ?>">
                                                                <i class="bi bi-trash me-2"></i>
                                                                Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php else: ?>
                                <p>No data available.</p>
                            <?php endif; ?>

                        </table>
                    </div>
                </div>

                <!-- Add Data Modal -->
                <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataLabel"
                    aria-hidden="false">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form id="addDataForm" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addDataLabel">Tambah Data Siswa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create">
                                    <div class="mb-3 row">
                                        <label for="student_name" class="form-label col-sm-3">Nama Siswa</label>
                                        <div class="col-sm-9">
                                            <select id="student_name" class="form-control select2" name="student_id"
                                                style="width:100%">
                                                <option value="">Pilih Nama Siswa</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?= $student['id']; ?>"
                                                        data-nis="<?= $student['nis']; ?>"
                                                        data-kelas-id=" <?= $student['kelas_id']; ?>">
                                                        <?= $student['nama_lengkap']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="nis_siswa" class="col-sm-3 col-form-label">NIS Siswa</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="nis_siswa" readonly>
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
                                            <input type="date" class="form-control" id="tanggalBayar"
                                                name="tanggal_bayar" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="pembayaran" class="col-sm-3 col-form-label">Pembayaran</label>
                                        <div class="col-sm-9">
                                            <button id="modalspilihtagihan" type="button" class="btn btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#jenisPembayaranModal">
                                                Pilih Jenis Pembayaran
                                            </button>
                                        </div>
                                    </div>

                                    <!-- ... other fields ... -->
                                    <hr>
                                    <!-- Tabel List Item Pengeluaran -->
                                    <div class="form-group">
                                        <table class="table table-striped table-bordered"
                                            id="tabel-list-item-pengeluaran">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Pembayaran</th>
                                                    <th>Tagihan</th>
                                                    <th>Sudah dibayar</th>
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
                                                    <td></td>
                                                    <td class="text-end fw-bold" style="padding-right:17px"
                                                        id="total-bayar">
                                                        0
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" form="addDataForm" class="btn btn-primary">Simpan</button>
                                </div>

                            </form>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="selectedPembayaran" class="d-flex mb-3">
                                    <!-- Selected items will be added here dynamically -->
                                </div>
                                <!-- Tabel Jenis Pembayaran -->
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
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- /.modal-dialog Show/Detail -->
                <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="showModalLabel">Hapus Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h3 class="text-center">Bukti Pembayaran</h3>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <p>Nama : <span id="nama_lengkap"></span></p>
                                        <p>NIS : <span id="nis"></span></p>
                                    </div>
                                    <div class="col-md-6 ms-auto">
                                        <p>No. Invoice : <span id="no_invoice"></span></p>
                                        <p>Tgl. Invoice : <span id="tanggal"></span></p>
                                    </div>
                                </div>
                                <!-- <p>Berikut bukti pemayaran siswa.</p> -->
                                <!-- <p class="fw-bold mt-3">Daftar Item:</p> -->
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pembayaran</th>
                                            <th>Jumlah</th>
                                            <th>Kurang</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td class="fw-bold">Total</td>
                                            <td id="total_bayar"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="fw-bold" id="jenis_bayar">Jenis Pembayaran()</td>
                                            <td id="total_bayar">0</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="fw-bold">Kembali</td>
                                            <td>0</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="fw-bold">Total kurang bayar</td>
                                            <td></td>
                                            <td>0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <form method="">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Print</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- /.modal-dialog delete -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Hapus Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.
                                </p>
                                <p>Daftar Item:</p>
                                <ul class="class-list"></ul>
                            </div>
                            <div class="modal-footer">
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" id="delete-id" name="id">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        $(document).ready(function () {
            $('#DataPembayaranSiswa').DataTable();

            // Nonaktifkan tombol Simpan saat pertama kali dimuat
            toggleSimpanButton();

            // Initialize Select2 with dropdownParent option
            $('#student_name').select2({
                placeholder: 'Pilih Nama Siswa',
                dropdownParent: $('#addDataModal')
            });

            $('#student_name').on('change', function () {
                var selectedValue = $(this).val();
                $('#modalspilihtagihan').prop('disabled', !selectedValue);

                var nis = $('#student_name option:selected').data('nis');
                $('#nis_siswa').val(nis);

                resetSelectedItems(); // Reset items pembayaran jika siswa diganti

                if (selectedValue) {
                    loadJenisPembayaran(selectedValue);
                }
            }).trigger('change');

            $('#jenisPembayaranTable').DataTable();

            function loadJenisPembayaran(id) {
                if (id) {
                    $.ajax({
                        url: 'student_paid_fees_child',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function (response) {
                            const table = $('#jenisPembayaranTable').DataTable();
                            table.clear();

                            let index = 1;
                            response.tarif_spp.forEach(function (item) {
                                const isDisabled = item.kurang_bayar <= 0 ? 'disabled' : '';
                                table.row.add([
                                    index++,
                                    item.nama_tarif,
                                    formatRupiah(item.nominal),
                                    formatRupiah(item.total_dibayar),
                                    formatRupiah(item.kurang_bayar),
                                    `<button class="btn btn-success btn-sm pilihBtn" data-id="${item.item_id}" data-type="${item.type}" ${isDisabled}>+ Pilih</button>`
                                ]).draw();
                            });

                            response.pembayaran_lainnya.forEach(function (item) {
                                const isDisabled = item.kurang_bayar <= 0 ? 'disabled' : '';
                                table.row.add([
                                    index++,
                                    item.nama_pembayaran,
                                    formatRupiah(item.nominal),
                                    formatRupiah(item.total_dibayar),
                                    formatRupiah(item.kurang_bayar),
                                    `<button class="btn btn-success btn-sm pilihBtn" data-id="${item.item_id}" data-type="${item.type}" ${isDisabled}>+ Pilih</button>`
                                ]).draw();
                            });
                        }
                    });
                }
            }

            $('#jenisPembayaranModal').on('hidden.bs.modal', function () {
                $('#addDataModal').modal('show');
            });

            $('#jenisPembayaranModal').on('show.bs.modal', function () {
                $('#addDataModal').modal('hide');
            });

            $('#jenisPembayaranTable').on('click', '.pilihBtn', function () {
                var row = $(this).closest('tr');
                var jenisPembayaran = row.find('td:nth-child(2)').text();
                var tagihan = parseRupiah(row.find('td:nth-child(3)').text());
                var total_dibayar = parseRupiah(row.find('td:nth-child(4)').text());
                var kurang_bayar = tagihan - total_dibayar; // Menghitung nilai kurang_bayar
                var itemId = $(this).data('id');
                var type = $(this).data('type');

                if ($('#selectedPembayaran').find(`[data-jenis="${jenisPembayaran}"]`).length === 0) {
                    $('#selectedPembayaran').append(`
                        <button class="btn btn-outline-success me-2" data-jenis="${jenisPembayaran}">
                            ${jenisPembayaran} (${type}) <span class="removeItem">&times;</span>
                        </button>
                    `);

                    $('#tabel-list-item-pengeluaran tbody').append(`
                        <tr class="row-item-bayar">
                            <td>${$('#tabel-list-item-pengeluaran tbody tr').length + 1}</td>
                            <td>
                                <label for="jenis" class="form-label" name="item_id">${jenisPembayaran}</label>
                                <input type="hidden" name="item_type[]" value="${type}">
                                <input type="hidden" name="item_id[]" value="${itemId}">
                            </td>
                            <td><label for="tagihan" class="form-label">${formatRupiah(tagihan)}</label></td>
                            <td><label for="jumlah" class="form-label">${formatRupiah(total_dibayar)}</label></td>
                            <td>
                                <input type="text" class="form-control jumlah-bayar" name="jumlah_bayar[]" 
                                    data-max="${kurang_bayar}" required placeholder="Masukkan jumlah">
                                <small class="text-danger max-info">Maksimal: ${formatRupiah(kurang_bayar)}</small>
                            </td>
                        </tr>
                    `);

                    calculateTotal();
                } else {
                    alert('Item ini sudah dipilih.');
                }

                toggleSimpanButton();
            });

            // Event listener untuk memformat input jumlah bayar
            $('#tabel-list-item-pengeluaran').on('input', '.jumlah-bayar', function () {
                var maxValue = parseFloat($(this).data('max'));
                var inputValue = parseRupiah($(this).val());

                if (inputValue > maxValue) {
                    alert('Jumlah bayar tidak boleh melebihi kurang bayar.');
                    inputValue = maxValue; // Atur ke nilai maksimal jika melebihi
                }

                $(this).val(formatRupiah(inputValue)); // Format ulang input sebagai Rupiah
            });

            $('#selectedPembayaran').on('click', '.removeItem', function () {
                var jenisPembayaran = $(this).closest('button').data('jenis');
                $(this).closest('button').remove();
                $('#tabel-list-item-pengeluaran tbody .row-item-bayar').filter(function () {
                    return $(this).find('td:nth-child(2) label').text() === jenisPembayaran;
                }).remove();
                $('#tabel-list-item-pengeluaran tbody .row-item-bayar').each(function (index) {
                    $(this).find('td:first-child').text(index + 1);
                });

                calculateTotal();
                toggleSimpanButton();
            });

            $(document).on('input', '.jumlah-bayar', function () {
                calculateTotal();
                toggleSimpanButton();
            });

            function resetSelectedItems() {
                // Clear selected items and table rows
                $('#selectedPembayaran').empty();
                $('#tabel-list-item-pengeluaran tbody').empty();
                calculateTotal();
                toggleSimpanButton();
            }

            function calculateTotal() {
                var totalBayar = 0;
                $('.jumlah-bayar').each(function () {
                    var value = parseRupiah($(this).val()) || 0;
                    totalBayar += value;
                });

                // Menampilkan total dalam format Rupiah
                var formattedTotal = totalBayar === 0 ? '0' : formatRupiah(totalBayar);
                $('#total-bayar').text(formattedTotal);
            }

            function toggleSimpanButton() {
                var hasItems = $('#tabel-list-item-pengeluaran tbody .row-item-bayar').length > 0;
                var allAmountsFilled = true;

                $('.jumlah-bayar').each(function () {
                    if (!$(this).val()) {
                        allAmountsFilled = false;
                        return false;
                    }
                });

                if (hasItems && allAmountsFilled) {
                    $('#addDataForm button[type="submit"]').prop('disabled', false);
                } else {
                    $('#addDataForm button[type="submit"]').prop('disabled', true);
                }
            }
        });

        // modals Detail
        document.addEventListener('DOMContentLoaded', function () {
            // show record
            const showModal = document.getElementById('showModal');
            if (showModal) {
                showModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-bs-id');
                    const nama_lengkap = button.getAttribute('data-bs-nama_lengkap');
                    const nis = button.getAttribute('data-bs-nis');
                    const nama_kelas = button.getAttribute('data-bs-nama_kelas');
                    const no_invoice = button.getAttribute('data-bs-no_invoice');
                    const tanggal = button.getAttribute('data-bs-tanggal');
                    const jenis_bayar = button.getAttribute('data-bs-jenis_bayar');
                    const total_bayar = parseFloat(button.getAttribute('data-bs-total_bayar'));

                    // Update the modal title
                    const modalTitle = showModal.querySelector('.modal-title');
                    modalTitle.textContent = `Data Pembayaran: ${nama_lengkap} ${nama_kelas}`;

                    const classListContainer = showModal.querySelector('.table tbody');
                    classListContainer.innerHTML = ''; // Clear previous content

                    // Retrieve and display associated items
                    const transaction = combinedResults[parseInt(id)];
                    if (transaction && transaction.items.length > 0) {
                        transaction.items.forEach((item, index) => {
                            const row = document.createElement('tr');

                            // Create and append cells
                            const indexCell = document.createElement('th');
                            indexCell.scope = 'row';
                            indexCell.textContent = index + 1;
                            row.appendChild(indexCell);

                            const nameCell = document.createElement('td');
                            nameCell.textContent = item.jenis_bayar || 'N/A'; // Nama Pembayaran
                            row.appendChild(nameCell);

                            const paidCell = document.createElement('td');
                            const jumlahBayar = item.jumlah_bayar;
                            paidCell.textContent = jumlahBayar ||
                                '0'; // Jumlah yang dibayarkan
                            row.appendChild(paidCell);

                            const remainingCell = document.createElement('td');
                            const kurangBayar = item.kurang_bayar;
                            remainingCell.textContent = kurangBayar ||
                                '0'; // Jumlah kurang
                            row.appendChild(remainingCell);

                            // Append the row to the table body
                            classListContainer.appendChild(row);
                        });
                    } else {
                        const noItemRow = document.createElement('tr');
                        const noItemCell = document.createElement('td');
                        noItemCell.colSpan = 4; // Span across all columns
                        noItemCell.className = 'text-center';
                        noItemCell.textContent = 'Tidak ada item terkait';

                        noItemRow.appendChild(noItemCell);
                        classListContainer.appendChild(noItemRow);
                    }

                    // Mengisi konten modal dengan data yang didapat
                    document.getElementById('nama_lengkap').textContent = nama_lengkap;
                    document.getElementById('nis').textContent = nis;
                    document.getElementById('no_invoice').textContent = no_invoice;
                    document.getElementById('tanggal').textContent = formatTanggal(tanggal);
                    // document.getElementById('jenis_bayar').textContent = jenis_bayar;
                    // Modifikasi untuk menampilkan jenis pembayaran
                    const jenisBayarText = jenis_bayar == 1 ? "Tunai" : jenis_bayar == 2 ? "Transfer" :
                        "Tidak Diketahui";
                    document.getElementById('jenis_bayar').textContent = jenisBayarText;
                    document.getElementById('total_bayar').textContent = formatRupiah(total_bayar);

                    // // Debugging output
                    // console.log(
                    //     `ID: ${id}, 
                    //         nama lengkap: ${nama_lengkap}, 
                    //         NIS: ${nis}, 
                    //         nama kelas: ${nama_kelas}, 
                    //         no invoice: ${no_invoice}, 
                    //         Tanggal: ${tanggal}, 
                    //         jenis bayar: ${jenis_bayar}, 
                    //         total bayar: ${total_bayar}, 
                    //         Item: ${transaction ? transaction.items.map(item => item.id).join(', ') : 'No items'}`
                    // );
                });
            }
        });

        // Delete Modal
        document.addEventListener('DOMContentLoaded', function () {
            // delete record
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-bs-id');
                    const tanggalBayar = button.getAttribute('data-bs-tanggal');

                    // Update the modal title
                    const modalTitle = deleteModal.querySelector('.modal-title');
                    modalTitle.textContent = `Hapus Data Tagihan: ${tanggalBayar}`;

                    // Populate the form with the id
                    const form = deleteModal.querySelector('#deleteForm');
                    form.querySelector('#delete-id').value = id;

                    // Get the list of payment items for the selected transaction
                    const classListContainer = deleteModal.querySelector('.class-list');
                    classListContainer.innerHTML = ''; // Clear previous content

                    // Retrieve and display associated items
                    const transaction = combinedResults[parseInt(id)];
                    if (transaction && transaction.items.length > 0) {
                        transaction.items.forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.textContent = item.jenis_bayar;
                            classListContainer.appendChild(listItem);
                        });
                    } else {
                        const noItem = document.createElement('li');
                        noItem.textContent = 'Tidak ada item terkait';
                        classListContainer.appendChild(noItem);
                    }

                    // Debugging output
                    // console.log(
                    //     `ID: ${id}, Tanggal Bayar: ${tanggalBayar}, Item: ${transaction ? transaction.items.map(item => item.id).join(', ') : 'No items'}`
                    // );
                });
            }
        });

        // Konten print
        document.addEventListener('DOMContentLoaded', function () {
            const printButton = document.querySelector('.btn-danger'); // Tombol print
            const modalBody = document.querySelector('#showModal .modal-body'); // Konten modal-body

            printButton.addEventListener('click', function () {
                // Ambil konten dari modal-body
                const printContent = modalBody.innerHTML;

                // Buat jendela baru untuk mencetak
                const printWindow = window.open('', '', 'height=600,width=800');

                // Tambahkan konten ke jendela baru
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Cetak Bukti Pembayaran</title>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
                    </head>
                    <body>
                        <div class="container mt-3">
                            ${printContent}
                        </div>
                    </body>
                    </html>
                `);

                // Tunggu hingga konten dimuat
                printWindow.document.close();
                printWindow.focus();

                // Cetak halaman
                printWindow.print();

                // Tutup jendela setelah cetak
                printWindow.close();
            });
        });
    </script>

</body>

</html>