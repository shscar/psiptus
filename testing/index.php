<?php
// Memulai buffering
ob_start();
include __DIR__ . '/../layouts/master.php';

// Aktifkan error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = Database::getInstance()->getConnection();

// Query untuk mengambil data dari relasi tabel
$stmt = $db->prepare("SELECT 
    pd.id AS pengeluaran_id,
    pd.tanggal_pengeluaran,
    pd.sumber_dana,
    pd.pihak_terlibat,
    pd.ket_pengeluaran,
    pd.jenis_bayar,
    pd.total,
    pd.status,
    pdi.id AS item_id,
    pdi.use_kategori,
    pdi.nama_pengeluaran,
    pdi.item,
    pdi.satuan,
    pdi.harga,
    pdi.nominal,
    pdi.komite,
    pdi.bosda,
    pdi.jumlah,
    pdb.id AS bukti_id,
    pdb.file_path,
    dkp.judul AS kategori_judul
FROM pengeluaran_dana pd
LEFT JOIN pengeluaran_dana_item pdi ON pd.id = pdi.pengeluaran_dana_id
LEFT JOIN pengeluaran_dana_bukti pdb ON pd.id = pdb.pengeluaran_id
LEFT JOIN detail_kategori_pengeluaran dkp ON pdi.nama_pengeluaran = dkp.id AND pdi.use_kategori = true
ORDER BY pd.tanggal_pengeluaran DESC");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengelompokkan data berdasarkan `pengeluaran_id`
$combinedResults = [];
foreach ($results as $row) {
    $pengeluaranId = $row['pengeluaran_id'];

    // Jika pengeluaran_id belum ada, tambahkan ke dalam hasil
    if (!isset($combinedResults[$pengeluaranId])) {
        $combinedResults[$pengeluaranId] = [
            'pengeluaran_id' => $row['pengeluaran_id'],
            'tanggal_pengeluaran' => $row['tanggal_pengeluaran'],
            'sumber_dana' => $row['sumber_dana'],
            'pihak_terlibat' => $row['pihak_terlibat'],
            'ket_pengeluaran' => $row['ket_pengeluaran'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total' => $row['total'],
            'status' => $row['status'],
            'items' => [],
            'bukti_files' => []
        ];
    }

    // Tambahkan item pengeluaran jika ada
    if ($row['item_id']) {
        $namaPengeluaran = $row['use_kategori'] && is_numeric($row['nama_pengeluaran'])
            ? $row['kategori_judul']
            : $row['nama_pengeluaran'];

        $combinedResults[$pengeluaranId]['items'][] = [
            'id' => $row['item_id'],
            'nama_pengeluaran' => $namaPengeluaran,
            'item' => $row['item'],
            'satuan' => $row['satuan'],
            'harga' => $row['harga'],
            'nominal' => $row['nominal'],
            'komite' => $row['komite'],
            'bosda' => $row['bosda'],
            'jumlah' => $row['jumlah']
        ];
    }

    // Tambahkan bukti pengeluaran jika ada
    if ($row['bukti_id']) {
        $combinedResults[$pengeluaranId]['bukti_files'][] = [
            'id' => $row['bukti_id'],
            'file_path' => 'assets/images/dana_pengeluaran/' . basename($row['file_path'])
        ];
    }
}

// Mengubah array terstruktur menjadi array numerik untuk kemudahan iterasi
$combinedResults = array_values($combinedResults);

// Menampilkan hasil untuk debugging
// echo '<pre>';
// print_r($combinedResults);
// echo '</pre>';

// query untuk mengambil data tabel "detail_kategori_pengeluaran"
$stmt = $db->prepare("SELECT * FROM detail_kategori_pengeluaran ORDER BY id DESC");
$stmt->execute();
$detail_kategori_pengeluaran = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        try {
            // Mulai transaksi
            $db->beginTransaction();

            // Data utama untuk tabel pengeluaran_dana
            $tanggal_pengeluaran = $_POST['tanggal_pengeluaran'] ?? null;
            $sumber_dana = $_POST['sumber_dana'] ?? null;
            $pihak_terlibat = $_POST['pihak_terlibat'] ?? null;
            $ket_pengeluaran = $_POST['ket_pengeluaran'] ?? null;
            $jenis_bayar = $_POST['jenis_bayar'] ?? null;

            // Validasi input
            if (empty($tanggal_pengeluaran) || empty($sumber_dana) || empty($jenis_bayar)) {
                throw new Exception("Data utama tidak lengkap.");
            }

            // Hitung total jumlah dari item
            $total = 0;
            foreach ($_POST['jumlah'] as $jumlah) {
                $total += (float) $jumlah;
            }

            // Insert ke tabel pengeluaran_dana
            $stmt = $db->prepare("
            INSERT INTO pengeluaran_dana (tanggal_pengeluaran, sumber_dana, pihak_terlibat, ket_pengeluaran, jenis_bayar, total, created_at, updated_at) 
            VALUES (:tanggal_pengeluaran, :sumber_dana, :pihak_terlibat, :ket_pengeluaran, :jenis_bayar, :total, NOW(), NOW())
        ");
            $stmt->execute([
                ':tanggal_pengeluaran' => $tanggal_pengeluaran,
                ':sumber_dana' => $sumber_dana,
                ':pihak_terlibat' => $pihak_terlibat,
                ':ket_pengeluaran' => $ket_pengeluaran,
                ':jenis_bayar' => $jenis_bayar,
                ':total' => $total
            ]);

            $pengeluaran_dana_id = $db->lastInsertId();

            // Insert ke tabel pengeluaran_dana_item
            foreach ($_POST['nama_pengeluaran'] as $index => $nama_pengeluaran) {
                $use_kategori = isset($_POST['use_kategori'][$index]) && $_POST['use_kategori'][$index] === 'on' ? 1 : 0;

                $item = (int) ($_POST['item'][$index] ?? 0);
                $satuan = $_POST['satuan'][$index] ?? null;
                $harga = (float) ($_POST['harga'][$index] ?? 0);
                $nominal = (float) ($_POST['nominal'][$index] ?? 0);
                $komite = (float) ($_POST['komite'][$index] ?? 0);
                $bosda = (float) ($_POST['bosda'][$index] ?? 0);
                $jumlah = (float) ($_POST['jumlah'][$index] ?? 0);

                if (empty($nama_pengeluaran)) {
                    throw new Exception("Nama pengeluaran tidak boleh kosong pada baris $index.");
                }

                $stmt = $db->prepare("
                INSERT INTO pengeluaran_dana_item (pengeluaran_dana_id, use_kategori, nama_pengeluaran, item, satuan, harga, nominal, komite, bosda, jumlah, created_at, updated_at) 
                VALUES (:pengeluaran_dana_id, :use_kategori, :nama_pengeluaran, :item, :satuan, :harga, :nominal, :komite, :bosda, :jumlah, NOW(), NOW())
            ");
                $stmt->execute([
                    ':pengeluaran_dana_id' => $pengeluaran_dana_id,
                    ':use_kategori' => $use_kategori,
                    ':nama_pengeluaran' => $nama_pengeluaran,
                    ':item' => $item,
                    ':satuan' => $satuan,
                    ':harga' => $harga,
                    ':nominal' => $nominal,
                    ':komite' => $komite,
                    ':bosda' => $bosda,
                    ':jumlah' => $jumlah
                ]);
            }

            // Handle upload file untuk tabel pengeluaran_dana_bukti
            if (!empty($_FILES['bukti_pengeluaran']['name'][0])) {
                $uploadDir = 'assets/images/dana_pengeluaran/';

                foreach ($_FILES['bukti_pengeluaran']['tmp_name'] as $index => $tmpName) {
                    $fileName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($_FILES['bukti_pengeluaran']['name'][$index], PATHINFO_EXTENSION);
                    $filePath = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $filePath)) {
                        $stmt = $db->prepare("
                        INSERT INTO pengeluaran_dana_bukti (pengeluaran_id, file_path, created_at, updated_at) 
                        VALUES (:pengeluaran_id, :file_path, NOW(), NOW())
                    ");
                        $stmt->execute([
                            ':pengeluaran_id' => $pengeluaran_dana_id,
                            ':file_path' => $fileName
                        ]);
                    }
                }
            }

            // Commit transaksi
            $db->commit();
            // Redirect or show success message
            echo "<script>
                alert('Data pengeluaran berhasil ditambah.');
                window.location.href = '/pengeluaran/detail-pengeluaran';
            </script>";
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $db->rollBack();
            // Redirect or show message
            echo "<script>
                alert(" . json_encode(['status' => 'error', 'message' => $e->getMessage()]) . ");
                window.location.href = '/pengeluaran/detail-pengeluaran';
            </script>";
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // Delete Record
    if ($action == 'delete') {
        try {
            // Ambil ID dari POST request
            $pengeluaranId = $_POST['id'] ?? null;

            if (!$pengeluaranId || !is_numeric($pengeluaranId)) {
                throw new Exception('ID pengeluaran tidak valid.');
            }

            // Mulai transaksi
            $db->beginTransaction();

            // Hapus data dari tabel pengeluaran_dana_bukti
            $stmt = $db->prepare("SELECT file_path FROM pengeluaran_dana_bukti WHERE pengeluaran_id = :id");
            $stmt->bindParam(':id', $pengeluaranId, PDO::PARAM_INT);
            $stmt->execute();
            $files = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($files as $file) {
                $filePath = __DIR__ . '/../assets/images/dana_pengeluaran/' . $file; // Pastikan path file benar
                if (file_exists($filePath)) {
                    unlink($filePath); // Hapus file dari sistem
                }
            }

            // Hapus data dari tabel pengeluaran_dana_bukti
            $stmt = $db->prepare("DELETE FROM pengeluaran_dana_bukti WHERE pengeluaran_id = :id");
            $stmt->bindParam(':id', $pengeluaranId, PDO::PARAM_INT);
            $stmt->execute();

            // Hapus data dari tabel pengeluaran_dana_item
            $stmt = $db->prepare("DELETE FROM pengeluaran_dana_item WHERE pengeluaran_dana_id = :id");
            $stmt->bindParam(':id', $pengeluaranId, PDO::PARAM_INT);
            $stmt->execute();

            // Hapus data dari tabel pengeluaran_dana
            $stmt = $db->prepare("DELETE FROM pengeluaran_dana WHERE id = :id");
            $stmt->bindParam(':id', $pengeluaranId, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaksi
            $db->commit();

            // Redirect atau kirim respons sukses
            echo "<script>
                alert('Data pengeluaran berhasil diapus.');
                window.location.href = '/pengeluaran/detail-pengeluaran';
            </script>";
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            die('Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // Approved Record
    if ($action == 'approval') {
        try {
            // Ambil ID dan status dari POST request
            $approvalId = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$approvalId || !is_numeric($approvalId)) {
                throw new Exception('ID tidak valid.');
            }

            if (!$status || !in_array($status, [1, 2])) {
                throw new Exception('Status tidak valid.');
            }

            // Mulai transaksi
            $db->beginTransaction();

            // Query untuk memperbarui status di database
            $stmt = $db->prepare("UPDATE pengeluaran_dana SET status = :status WHERE id = :id");
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id', $approvalId, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaksi
            $db->commit();

            if ($stmt->execute()) {
                // Redirect atau kirim respons sukses
                echo "<script>
                    alert('Status berhasil diperbarui.');
                    window.location.href = '/test';
                </script>";
                exit;
            } else {
                // Berikan p
                echo "<script>
                    alert('Gagal memperbarui status.');
                    window.location.href = '/test';
                </script>";
                exit;
            }
        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            $db->rollBack();
            die('Gagal memperbarui status: ' . $e->getMessage());
        }
    }

}

// Mengakhiri buffering
ob_end_flush();
?>

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

<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        Pengeluaran Pembiayaan Sekolah

                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle rounded-5"
                                    data-bs-toggle="dropdown" aria-expanded="false">?</button>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Detail
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Approval
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
                                        <i class="bi bi-dash me-2"></i>
                                        Export
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-x me-2"></i>
                                        Import
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
                            expnd
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- App Content -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Grade Level </h3>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-info btn-sm"
                            onclick="document.location='/pengeluaran/kategori-pengeluaran'">
                            <i class="bi pe-1"></i>Kategori
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                        </button>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- <h3 class="card-title text-danger">Edit Detail sedang maintenance</h3> -->
                        <div class="col-md-12">
                            <?php if (!empty($combinedResults)): ?>
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Pihak Terlibat</th>
                                            <th>Item Pengeluaran</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combinedResults as $index => $row): ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td>
                                                    <div>
                                                        <?= date('d M Y', strtotime($row['tanggal_pengeluaran'])) ?? '-'; ?>
                                                    </div>
                                                </td>
                                                <td><?= $row['ket_pengeluaran'] ?? '-'; ?></td>
                                                <td><?= $row['pihak_terlibat'] ?? '-'; ?></td>
                                                <td>
                                                    <ul class="list-circle m-0">
                                                        <?php foreach ($row['items'] as $item): ?>
                                                            <li><?= $item['nama_pengeluaran']; ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                                <td>
                                                    <div class="text-start">Rp.
                                                        <?= number_format($row['total'], 2) ?? '-'; ?>
                                                    </div>
                                                </td>
                                                <td class="project-state">
                                                    <?php
                                                    $status = htmlspecialchars($row['status']) ?: '-';
                                                    switch ($status) {
                                                        case '1':
                                                            $badgeClass = 'badge bg-success';
                                                            $statusText = 'Accept';
                                                            break;
                                                        case '2':
                                                            $badgeClass = 'badge bg-danger';
                                                            $statusText = 'Decline';
                                                            break;
                                                        default:
                                                            $badgeClass = 'badge bg-warning';
                                                            $statusText = 'Pending';
                                                    }
                                                    ?>
                                                    <span class="<?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
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
                                                                    data-bs-target="#detailModal"
                                                                    data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                                    data-bs-keterangan="<?= $row['ket_pengeluaran']; ?>"
                                                                    data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                                    data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                                    data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                                    data-bs-total="<?= $row['total']; ?>"
                                                                    data-items='<?= json_encode($row['items']); ?>'
                                                                    data-bukti_files='<?= json_encode($row['bukti_files']); ?>'>
                                                                    <i class="bi bi-list-stars me-2"></i>
                                                                    Detail
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item" data-bs-toggle="modal"
                                                                    data-bs-target="#editModal"
                                                                    data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                                    data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                                    data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                                    data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                                    data-bs-total="<?= $row['total']; ?>"
                                                                    data-items='<?= json_encode($row['items']); ?>'>
                                                                    <i class="bi bi-filetype-exe me-2"></i>
                                                                    Export
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteModal"
                                                                    data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                                    data-bs-keterangan="<?= $row['ket_pengeluaran']; ?>"
                                                                    data-nama_pengeluaran="<?= $row['items'][0]['nama_pengeluaran'] ?? 'Tidak Ada'; ?>"
                                                                    data-items='<?= json_encode($row['items']); ?>'>
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
                                </table>
                            <?php else: ?>
                                <p>No data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Tambah Pengeluaran Dana</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create">
                            <div class="row">
                                <!-- Left Card for input -->
                                <div class="col-md-7">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-4 mb-3">
                                                <label for="tanggal_pengeluaran" class="form-label">Tanggal</label>
                                                <input type="date" class="form-control" id="tanggal_pengeluaran"
                                                    name="tanggal_pengeluaran" required>
                                            </div>
                                            <div class="form-group col-8 mb-3">
                                                <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                                <input type="text" class="form-control" id="sumber_dana"
                                                    name="sumber_dana" placeholder="Contoh: Dana BOS">
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="pihak_terlibat" class="form-label">Pihak Terlibat</label>
                                                <input type="text" class="form-control" id="pihak_terlibat"
                                                    name="pihak_terlibat" placeholder="Contoh: Bagian Keuangan"
                                                    required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="ket_pengeluaran" class="form-label">Keterangan</label>
                                                <input type="text" class="form-control" id="ket_pengeluaran"
                                                    name="ket_pengeluaran" placeholder="" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Card for image -->
                                <div class="col-md-5" style="max-height: 243px; overflow-y: auto;">
                                    <div class="form-group mb-3">
                                        <label for="bukti_pengeluaran" class="form-label">Unggah Bukti Pengeluaran
                                            (Opsional)</label>
                                        <input type="file" class="form-control" id="bukti_pengeluaran"
                                            name="bukti_pengeluaran[]" accept=".jpg,.jpeg,.png" multiple>
                                    </div>
                                    <div class="card">
                                        <div id="image-preview-container" class="d-flex flex-wrap gap-2 p-2"></div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" id="tabel-list-item-pengeluaran">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th style="min-width:200px">Pengeluaran</th>
                                                <th style="min-width:130px">Jumlah Item</th>
                                                <th style="min-width:130px">Satuan</th>
                                                <th style="min-width:130px">Harga</th>
                                                <th style="min-width:130px">Nominal</th>
                                                <th style="min-width:130px">Komite</th>
                                                <th style="min-width:130px">Bosda</th>
                                                <!-- <th>Keterangan</th> -->
                                                <th style="min-width:130px">Jumlah</th>
                                                <th style="width:20px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="row-item-bayar">
                                                <td>1</td>
                                                <td>
                                                    <div class="form-group form-kategori">
                                                        <!-- Switch Checkbox -->
                                                        <div class="form-check form-switch mb-2">
                                                            <input type="checkbox"
                                                                class="form-check-input toggle-select"
                                                                id="useselectkategori1" name="use_kategori[]">
                                                            <label class="form-check-label" for="useselectkategori1">Use
                                                                Kategori</label>
                                                        </div>

                                                        <!-- Input Text -->
                                                        <div class="input-container">
                                                            <input type="text"
                                                                class="form-control nama-pengeluaran-input"
                                                                name="nama_pengeluaran[]" placeholder="Nama Pengeluaran"
                                                                required>
                                                        </div>

                                                        <!-- Select Dropdown -->
                                                        <div class="select-container" style="display: none;">
                                                            <select class="form-select detail-kategori-select"
                                                                name="nama_pengeluaran[]">
                                                                <option selected disabled value="">Pilih pengeluaran
                                                                </option>
                                                                <?php foreach ($detail_kategori_pengeluaran as $dkp): ?>
                                                                    <option value="<?php echo $dkp['id']; ?>">
                                                                        <?php echo $dkp['judul']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control item" name="item[]"
                                                        placeholder="Item" min="1" required>
                                                </td>
                                                <td>
                                                    <select class="form-select mb-3" name="satuan[]" required>
                                                        <option selected disabled value="">Pilih</option>
                                                        <option value="rim">Rim</option>
                                                        <option value="lembar">Lembar</option>
                                                        <option value="soal">Soal</option>
                                                        <option value="ruang">Ruang</option>
                                                        <option value="kali">Kali</option>
                                                        <option value="pack">Pack</option>
                                                        <option value="dus">Dus</option>
                                                        <option value="box">Box</option>
                                                        <option value="buah">Buah</option>
                                                        <option value="bendel">Bendel</option>
                                                        <option value="siswa">Siswa</option>
                                                        <option value="orang">orang</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control harga" name="harga[]"
                                                        placeholder="harga" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control nominal" name="nominal[]"
                                                        placeholder="Rp 0.00" min="0" disabled>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control komite" name="komite[]"
                                                        placeholder="Komite" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control bos" name="bosda[]"
                                                        placeholder="Bosda" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control jumlah" name="jumlah[]"
                                                        placeholder="0.00" min="0" disabled>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-success add-row">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr id="row-total-bayar">
                                                <td></td>
                                                <td colspan="4">
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
                                                <td></td>
                                                <td></td>
                                                <td class="text-end fw-bold" id="total-item-nilai-bayar">0</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Delete -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Hapus Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.</p>
                        <h6>Item Pengeluaran Terkait:</h6>
                        <ul id="item-list"></ul>
                    </div>
                    <div class="modal-footer">
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" id="delete-id" name="id">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail -->
        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModalLabel">Detail Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="container-fluid">
                            <div>
                                <strong>Keterangan:</strong>
                                <span id="modal-keterangan">-</span>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-8">
                                    <div class="row">
                                        <div class="col-3">
                                            <p class="fw-bold">Tanggal</p>
                                            <p class="fw-bold">Sumber Dana</p>
                                            <p class="fw-bold">Pihak Terlibat</p>
                                            <p class="fw-bold">Total</p>
                                        </div>
                                        <div class="col-9">
                                            <p>: <span id="modal-sumber-dana">-</span></p>
                                            <p>: <span id="modal-tanggal">-</span></p>
                                            <p>: <span id="modal-pihak-terlibat">-</span></p>
                                            <p>: <span id="modal-total">-</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-3" style="max-height: 180px; overflow-y: auto;">
                                    <h6>Bukti Pengeluaran:</h6>
                                    <div class="card">
                                        <div id="bukti-container" class="d-flex flex-wrap">
                                            <!-- Evidence files will be dynamically added here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Item Pengeluaran Terkait:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Pengeluaran</th>
                                                    <th>Barang/Item</th>
                                                    <th>Satuan</th>
                                                    <th>H-Satuan</th>
                                                    <th>Nilai Bayar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="item-table-body">
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada item terkait</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            </script>
                        </div>
                        <div class="modal-footer">
                            <form id="approvalForm" method="POST">
                                <input type="hidden" name="action" value="approval">
                                <input type="hidden" id="approval-id" name="id">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success" name="status" value="1">Accept</button>
                                <button type="submit" class="btn btn-danger" name="status" value="2">Decline</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- App Content -->
    </div>
</main>
<!-- App Main -->

<!-- Inisialisasi DataTables -->
<script>
    $(document).ready(function () {
        $('#datatable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const tableBody = document.querySelector('#tabel-list-item-pengeluaran tbody');
        const totalAmountDisplay = document.getElementById('total-item-nilai-bayar');
        const previewContainer = document.getElementById('image-preview-container');
        const fileInput = document.getElementById('bukti_pengeluaran');

        // Image preview with delete functionality
        fileInput.addEventListener('change', handleImagePreview);

        function handleImagePreview(event) {
            const files = Array.from(event.target.files);
            previewContainer.innerHTML = ''; // Clear previous previews

            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => createImagePreview(e.target.result, index);
                    reader.readAsDataURL(file);
                }
            });
        }

        function createImagePreview(src, index) {
            const container = document.createElement('div');
            container.className = 'position-relative d-inline-block';

            container.innerHTML = `
                <img src="${src}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                <button class="btn btn-sm btn-danger position-absolute top-0 end-0" style="z-index: 1;">&times;</button>
            `;

            container.querySelector('button').addEventListener('click', () => removeImagePreview(index));
            previewContainer.appendChild(container);
        }

        function removeImagePreview(index) {
            const files = Array.from(fileInput.files);
            files.splice(index, 1);

            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;

            handleImagePreview({
                target: {
                    files: fileInput.files
                }
            });
        }

        // Add new row to the table
        document.querySelector('.add-row').addEventListener('click', () => {
            addNewRow();
            updateTotal();
        });

        function addNewRow() {
            const rowCount = tableBody.rows.length;
            const newRow = document.createElement('tr');
            newRow.className = 'row-item-bayar';

            newRow.innerHTML = `
                <td>${rowCount + 1}</td>
                <td>
                    <div class="form-group form-kategori">
                        <!-- Switch Checkbox -->
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input toggle-select" id="useselectkategori${rowCount}" name="use_kategori[${rowCount}]">
                            <label class="form-check-label" for="useselectkategori${rowCount}">Use Kategori</label>
                        </div>

                        <!-- Input Text -->
                        <div class="input-container">
                            <input type="text" class="form-control nama-pengeluaran-input"
                                name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
                        </div>

                        <!-- Select Dropdown -->
                        <div class="select-container" style="display: none;">
                            <select class="form-select detail-kategori-select" name="nama_pengeluaran[]">
                                <option selected disabled value="">Pilih pengeluaran
                                </option>
                                <?php foreach ($detail_kategori_pengeluaran as $dkp): ?>
                                                                                                                        <option value="<?php echo $dkp['id']; ?>">
                                                                                                                        <?php echo $dkp['judul']; ?>
                                                                                                                        </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </td>
                <td><input type="number" class="form-control item" name="item[]" placeholder="Item" min="1" required></td>
                <td>
                    <select class="form-select mb-3" name="satuan[]" required>
                        <option selected disabled value="">Pilih</option>
                        <option value="rim">Rim</option>
                        <option value="lembar">Lembar</option>
                        <option value="soal">Soal</option>
                        <option value="ruang">Ruang</option>
                        <option value="kali">Kali</option>
                        <option value="pack">Pack</option>
                        <option value="dus">Dus</option>
                        <option value="box">Box</option>
                        <option value="buah">Buah</option>
                        <option value="bendel">Bendel</option>
                        <option value="siswa">Siswa</option>
                        <option value="orang">orang</option>
                    </select>
                </td>
                <td><input type="number" class="form-control harga" name="harga[]" placeholder="Harga" min="0" required></td>
                <td><input type="number" class="form-control nominal" name="nominal[]" placeholder="Rp 0.00" min="0" disabled></td>
                <td><input type="number" class="form-control komite" name="komite[]" placeholder="Komite" min="0" required></td>
                <td><input type="number" class="form-control bos" name="bosda[]" placeholder="Bosda" min="0" required></td>
                <td><input type="number" class="form-control jumlah" name="jumlah[]" placeholder="0.00" min="0" disabled></td>
                <td><button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-dash-lg"></i></button></td>
            `;

            tableBody.appendChild(newRow);
            updateRowNumbers();
        }

        tableBody.addEventListener('click', (e) => {
            if (e.target.closest('.remove-row')) {
                e.target.closest('.row-item-bayar').remove();
                updateRowNumbers();
                updateTotal();
            }
            // else if (e.target.classList.contains('toggle-select')) {
            //     const row = e.target.closest('.row-item-bayar');
            //     toggleInputDisplay(e.target.checked, row);
            // }
        });
        tableBody.addEventListener('input', function (e) {
            if (e.target.classList.contains('jumlah')) {
                updateTotal();
            }
        });

        tableBody.addEventListener('input', (e) => {
            if (e.target.matches('.item, .harga')) calculateNominal(e.target.closest('tr'));
            if (e.target.matches('.komite, .bos')) calculateJumlah(e.target.closest('tr'));
        });

        function calculateNominal(row) {
            const item = parseFloat(row.querySelector('.item').value) || 0;
            const harga = parseFloat(row.querySelector('.harga').value) || 0;
            const nominal = item * harga;
            row.querySelector('.nominal').value = nominal.toFixed(2);
            updateTotal();
        }

        function calculateJumlah(row) {
            const komite = parseFloat(row.querySelector('.komite').value) || 0;
            const bos = parseFloat(row.querySelector('.bos').value) || 0;
            const jumlah = komite + bos;
            row.querySelector('.jumlah').value = jumlah.toFixed(2);
            updateTotal();
        }

        function updateRowNumbers() {
            document.querySelectorAll('.row-item-bayar').forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        function updateTotal() {
            const total = Array.from(document.querySelectorAll('.jumlah'))
                .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
            totalAmountDisplay.textContent = total.toLocaleString('id-ID');
        }

        // Fungsi untuk mengatur tampilan elemen berdasarkan status checkbox
        function toggleInputDisplay(isChecked, formGroup) {
            const textInputContainer = formGroup.querySelector('.input-container');
            const selectInputContainer = formGroup.querySelector('.select-container');
            const textInput = formGroup.querySelector('.nama-pengeluaran-input');
            const selectInput = formGroup.querySelector('.detail-kategori-select');

            if (isChecked) {
                textInputContainer.style.display = 'none';
                selectInputContainer.style.display = 'block';
                textInput.disabled = true;
                selectInput.disabled = false;
                textInput.required = false;
                selectInput.required = true;
            } else {
                textInputContainer.style.display = 'block';
                selectInputContainer.style.display = 'none';
                textInput.disabled = false;
                selectInput.disabled = true;
                textInput.required = true;
                selectInput.required = false;
            }
        }

        // Event listener global untuk menangani semua checkbox dinamis
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('toggle-select')) {
                const formGroup = e.target.closest('.form-kategori');
                toggleInputDisplay(e.target.checked, formGroup);

                // Update nilai use_kategori agar sesuai dengan row
                const index = Array.from(document.querySelectorAll('.toggle-select')).indexOf(e.target);
                e.target.name = `use_kategori[${index}]`;
            }
        });

        // Fungsi untuk menginisialisasi semua checkbox yang ada di awal
        function initializeCheckboxes() {
            document.querySelectorAll('.toggle-select').forEach((checkbox, index) => {
                const formGroup = checkbox.closest('.form-kategori');
                toggleInputDisplay(checkbox.checked, formGroup);

                // Set name attribute agar sesuai dengan indeks
                checkbox.name = `use_kategori[${index}]`;
            });
        }

        // Panggil fungsi untuk menginisialisasi saat halaman dimuat
        initializeCheckboxes();

    });

    // untuk mengaktifkan form nominal dan jumlah
    document.querySelector('form').addEventListener('submit', (e) => {
        // Mengaktifkan input yang dinonaktifkan
        const nominalInputs = document.querySelectorAll('.nominal');
        const jumlahInputs = document.querySelectorAll('.jumlah');

        nominalInputs.forEach(input => {
            input.disabled = false;
        });

        jumlahInputs.forEach(input => {
            input.disabled = false;
        });
    });

    // Detail
    document.addEventListener('DOMContentLoaded', function () {
        // Handling Detail Modal
        const detailModal = document.getElementById('detailModal');
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                // Fetch attributes from the clicked button
                const pengeluaranId = button.getAttribute('data-bs-id');
                const keterangan = button.getAttribute('data-bs-keterangan');
                const tanggalPengeluaran = button.getAttribute('data-bs-tanggal_pengeluaran');
                const pihakTerlibat = button.getAttribute('data-bs-pihak_terlibat');
                const sumberDana = button.getAttribute('data-bs-sumber_dana');
                const total = button.getAttribute('data-bs-total');
                const items = JSON.parse(button.getAttribute('data-items'));
                const buktiFiles = JSON.parse(button.getAttribute('data-bukti_files'));

                // Update modal title
                const modalTitle = detailModal.querySelector('.modal-title');
                modalTitle.textContent = `Detail Data Pengeluaran: ${keterangan}`;

                // Format tanggal menjadi "01 Desember 2001"
                const formattedDate = formatTanggal(tanggalPengeluaran);

                // Update modal content
                detailModal.querySelector('#modal-keterangan').textContent = keterangan || '-';
                detailModal.querySelector('#modal-tanggal').textContent = formattedDate || '-';
                detailModal.querySelector('#modal-pihak-terlibat').textContent = pihakTerlibat || '-';
                detailModal.querySelector('#modal-sumber-dana').textContent = sumberDana || '-';
                detailModal.querySelector('#modal-total').textContent = total || '-';

                // Populate item list
                const itemTableBody = detailModal.querySelector('#item-table-body');
                itemTableBody.innerHTML = '';
                if (items && items.length > 0) {
                    items.forEach((item, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${item.nama_pengeluaran}</td>
                            <td>${item.item}</td>
                            <td>${item.satuan}</td>
                            <td>${item.harga}</td>
                            <td>${item.jumlah}</td>
                        `;
                        itemTableBody.appendChild(row);
                    });
                } else {
                    itemTableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada item terkait</td>
                        </tr>
                    `;
                }

                // Populate evidence files image of bukti pengeluaran
                const buktiContainer = detailModal.querySelector('#bukti-container');
                buktiContainer.innerHTML = '';
                if (buktiFiles && buktiFiles.length > 0) {
                    buktiFiles.forEach((bukti) => {
                        const img = document.createElement('img');
                        img.src = bukti.file_path;
                        img.alt = `Bukti ${bukti.id}`;
                        img.classList.add('img-thumbnail');
                        img.style.maxWidth = '45%';
                        img.style.height = 'auto';
                        img.style.objectFit = 'cover';
                        img.style.margin = '5px';
                        buktiContainer.appendChild(img);
                    });
                } else {
                    buktiContainer.innerHTML =
                        '<p class="text-muted p-2">Tidak ada bukti pengeluaran tersedia</p>';
                }

                // Update hidden input in the form
                const approvalForm = detailModal.querySelector('#approvalForm');
                approvalForm.querySelector('#approval-id').value = pengeluaranId;

                // Debugging output
                // console.log(
                //     `ID: ${pengeluaranId}, 
                // Keterangan: ${keterangan}, 
                // Tanggal Pengeluaran: ${tanggalPengeluaran}, 
                // Pihak Terlibat: ${pihakTerlibat}, 
                // Sumber Dana: ${sumberDana}, 
                // Total: ${total}, 
                // Items: ${items ? items.length : 0}`
                // );

            });
        }
    });

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

    // delete
    document.addEventListener('DOMContentLoaded', function () {
        // Handling Delete
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const pengeluaranId = button.getAttribute('data-bs-id');
                const keterangan = button.getAttribute('data-bs-keterangan');
                const items = JSON.parse(button.getAttribute('data-items'));
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Hapus Data Pengeluaran: ${keterangan}`;

                // Populate item list in modal body
                const itemList = deleteModal.querySelector('#item-list');
                itemList.innerHTML = '';
                items.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.textContent =
                        `${item.nama_pengeluaran} - Jumlah Harga ${item.jumlah} unit (${item.item})`;
                    itemList.appendChild(listItem);
                });

                const deleteForm = deleteModal.querySelector('#deleteForm');
                deleteForm.querySelector('#delete-id').value = pengeluaranId;

            });
        }
    });
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>