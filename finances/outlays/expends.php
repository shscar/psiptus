<?php
// Memulai buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Query untuk mengambil data pengeluaran dan item pengeluaran
$stmt = $db->prepare("SELECT 
            pd.id AS pengeluaran_id,
            pd.tanggal_pengeluaran,
            pd.bukti_pengeluaran,
            pd.pihak_terlibat,
            pd.sumber_dana,
            pd.jenis_bayar,
            pd.total_jumlah,
            ipd.nama_pengeluaran,
            ipd.keterangan AS item_keterangan,
            ipd.jumlah_barang,
            ipd.nilai_bayar
        FROM pengeluaran_dana pd
        LEFT JOIN item_pengeluaran_dana ipd ON pd.id = ipd.pengeluaran_id
        ORDER BY pd.tanggal_pengeluaran DESC
    ");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gabungkan data ke dalam array terstruktur
$combinedResults = [];
foreach ($results as $row) {
    $pengeluaranId = $row['pengeluaran_id'];

    // Cek apakah pengeluaran_id sudah ada di array $combinedResults
    if (!isset($combinedResults[$pengeluaranId])) {
        // Jika belum ada, buat entry baru di array
        $combinedResults[$pengeluaranId] = [
            'pengeluaran_id' => $row['pengeluaran_id'],
            'tanggal_pengeluaran' => $row['tanggal_pengeluaran'],
            'bukti_pengeluaran' => $row['bukti_pengeluaran'],
            'pihak_terlibat' => $row['pihak_terlibat'],
            'sumber_dana' => $row['sumber_dana'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total_jumlah' => $row['total_jumlah'],
            'items' => [] // Array kosong untuk item pengeluaran
        ];
    }

    // Tambahkan item pengeluaran ke dalam array items
    $combinedResults[$pengeluaranId]['items'][] = [
        'nama_pengeluaran' => $row['nama_pengeluaran'],
        'item_keterangan' => $row['item_keterangan'],
        'jumlah_barang' => $row['jumlah_barang'],
        'nilai_bayar' => $row['nilai_bayar']
    ];
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

// Fungsi untuk menangani unggah file dan mengubah nama file
function handleFileUpload($file)
{
    $uploadDir = 'assets/images/dana_pengeluaran/';
    $timestamp = date('YmdHis');
    $uniqueCode = substr(bin2hex(random_bytes(3)), 0, 6); // Kode unik dengan panjang 6 karakter
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = "{$timestamp}-{$uniqueCode}.{$extension}";
    $filePath = $uploadDir . $newFileName;

    // Pindahkan file ke folder tujuan
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath; // Kembalikan path lengkap untuk disimpan di database
    }
    return null; // Gagal upload
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        // Prepare variables
        $tanggal_pengeluaran = $_POST['tanggal_pengeluaran'];
        $pihak_terlibat = $_POST['pihak_terlibat'];
        $detail_kategori_pengeluaran_id = $_POST['detail_kategori_pengeluaran_id'];
        $sumber_dana = $_POST['sumber_dana'];
        $jenis_bayar = $_POST['jenis_bayar'];
        $total_jumlah = 0;

        // Calculate total from the jumlah_barang inputs
        foreach ($_POST['jumlah_barang'] as $jumlah) {
            $total_jumlah += (float) $jumlah;
        }

        // Insert pengeluaran_dana record
        $stmt = $db->prepare("INSERT INTO pengeluaran_dana (tanggal_pengeluaran, pihak_terlibat, detail_kategori_pengeluaran_id, sumber_dana, jenis_bayar, total_jumlah) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tanggal_pengeluaran, $pihak_terlibat, $detail_kategori_pengeluaran_id, $sumber_dana, $jenis_bayar, $total_jumlah]);

        // Get the last inserted ID for the pengeluaran_dana table
        $pengeluaran_id = $db->lastInsertId();

        // Handle file uploads
        if (isset($_FILES['bukti_pengeluaran']) && !empty($_FILES['bukti_pengeluaran']['name'][0])) {
            foreach ($_FILES['bukti_pengeluaran']['name'] as $index => $name) {
                $file = [
                    'name' => $_FILES['bukti_pengeluaran']['name'][$index],
                    'type' => $_FILES['bukti_pengeluaran']['type'][$index],
                    'tmp_name' => $_FILES['bukti_pengeluaran']['tmp_name'][$index],
                    'error' => $_FILES['bukti_pengeluaran']['error'][$index],
                    'size' => $_FILES['bukti_pengeluaran']['size'][$index]
                ];

                $filePath = handleFileUpload($file);
                if ($filePath) {
                    // Insert into bukti_pengeluaran_dana table
                    $buktiStmt = $db->prepare("INSERT INTO bukti_pengeluaran_dana (pengeluaran_id, file_path) VALUES (?, ?)");
                    $buktiStmt->execute([$pengeluaran_id, $filePath]);
                }
            }
        }

        // Insert item_pengeluaran_dana records
        $itemStmt = $db->prepare("INSERT INTO item_pengeluaran_dana (pengeluaran_id, nama_pengeluaran, keterangan, jumlah_barang, nilai_bayar) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['nama_pengeluaran'] as $index => $nama_pengeluaran) {
            $keterangan = $_POST['keterangan'][$index];
            $jumlah_barang = $_POST['jumlah_barang'][$index];
            $nilai_bayar = (float) $jumlah_barang; // Assuming nilai_bayar is the same as jumlah_barang for this example

            $itemStmt->execute([$pengeluaran_id, $nama_pengeluaran, $keterangan, $jumlah_barang, $nilai_bayar]);
        }

        // Redirect or show success message
        echo "<script>
            alert('Data pengeluaran berhasil ditambah.');
            window.location.href = '/pengeluaran/detail-pengeluaran';
        </script>";
    }

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        try {
            // Begin transaction
            $db->beginTransaction();

            // Delete related items in item_pengeluaran_dana table
            $stmt = $db->prepare("DELETE FROM item_pengeluaran_dana WHERE pengeluaran_id = :pengeluaran_id");
            $stmt->execute(['pengeluaran_id' => $id]);

            // Delete the pengeluaran_dana record
            $stmt = $db->prepare("DELETE FROM pengeluaran_dana WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Commit transaction
            $db->commit();

            // Redirect or display success message
            echo "<script>
                alert('Data pengeluaran berhasil ditambah.');
                window.location.href = '/pengeluaran/detail-pengeluaran';
            </script>";
        } catch (Exception $e) {
            // Rollback transaction if something goes wrong
            $db->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    // Edit Record
    if ($action == 'edit') {
        // Ambil data pengeluaran berdasarkan ID
        $pengeluaran_id = $_POST['pengeluaran_id'];

        // Query untuk mendapatkan data pengeluaran
        $stmt = $db->prepare("SELECT * FROM pengeluaran_dana WHERE id = ?");
        $stmt->execute([$pengeluaran_id]);
        $pengeluaran = $stmt->fetch(PDO::FETCH_ASSOC);

        // Query untuk mendapatkan item pengeluaran
        $itemStmt = $db->prepare("SELECT * FROM item_pengeluaran_dana WHERE pengeluaran_id = ?");
        $itemStmt->execute([$pengeluaran_id]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // Kirim data ke frontend dalam format JSON
        echo json_encode(['pengeluaran' => $pengeluaran, 'items' => $items]);
        exit;
    }

    // Update Record
    if ($action == 'update') {
        // Ambil variabel dari form
        $pengeluaran_id = $_POST['pengeluaran_id'];
        $tanggal_pengeluaran = $_POST['tanggal_pengeluaran'];
        $pihak_terlibat = $_POST['pihak_terlibat'];
        $detail_kategori_pengeluaran_id = $_POST['detail_kategori_pengeluaran_id'];
        $sumber_dana = $_POST['sumber_dana'];
        $jenis_bayar = $_POST['jenis_bayar'];
        $total_jumlah = 0;

        // Handle file upload
        $bukti_pengeluaran = null;
        if (isset($_FILES['bukti_pengeluaran']) && $_FILES['bukti_pengeluaran']['error'] == UPLOAD_ERR_OK) {
            $bukti_pengeluaran = handleFileUpload($_FILES['bukti_pengeluaran']);
        }

        // Update pengeluaran_dana
        $stmt = $db->prepare("UPDATE pengeluaran_dana SET tanggal_pengeluaran = ?, bukti_pengeluaran = ?, pihak_terlibat = ?, detail_kategori_pengeluaran_id = ?, sumber_dana = ?, jenis_bayar = ?, total_jumlah = ? WHERE id = ?");
        $stmt->execute([$tanggal_pengeluaran, $bukti_pengeluaran, $pihak_terlibat, $detail_kategori_pengeluaran_id, $sumber_dana, $jenis_bayar, $total_jumlah, $pengeluaran_id]);

        // Hapus semua item pengeluaran lama
        $itemStmt = $db->prepare("DELETE FROM item_pengeluaran_dana WHERE pengeluaran_id = ?");
        $itemStmt->execute([$pengeluaran_id]);

        // Tambah ulang item pengeluaran
        $itemStmt = $db->prepare("INSERT INTO item_pengeluaran_dana (pengeluaran_id, nama_pengeluaran, keterangan, jumlah_barang, nilai_bayar) VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['nama_pengeluaran'] as $index => $nama_pengeluaran) {
            $keterangan = $_POST['keterangan'][$index];
            $jumlah_barang = $_POST['jumlah_barang'][$index];
            $nilai_bayar = (float) $jumlah_barang;
            $itemStmt->execute([$pengeluaran_id, $nama_pengeluaran, $keterangan, $jumlah_barang, $nilai_bayar]);
        }

        // Redirect atau tampilkan pesan sukses
        echo "<script>
            alert('Data pengeluaran berhasil diperbarui.');
            window.location.href = '/pengeluaran/detail-pengeluaran';
        </script>";
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
                    <h3 class="mb-0">Pengeluaran Dana Sekolah</h3>
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
                    <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                    </button>

                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- <h3 class="card-title text-danger">Edit Detail sedang Maintance</h3> -->
                        <div class="col-md-12">
                            <?php if (!empty($combinedResults)): ?>
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Sumber Dana</th>
                                        <th>Nama Pengeluaran</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($combinedResults as $row): ?>
                                    <tr>
                                        <td><?= $row['pengeluaran_id'] ?? '-'; ?></td>
                                        <td><?= $row['sumber_dana'] ?? '-'; ?></td>
                                        <td>
                                            <ul class="list-circle m-0">
                                                <?php foreach ($row['items'] as $item): ?>
                                                <li><?= $item['nama_pengeluaran']; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <div class="text-start">Rp.
                                                <?= number_format($row['total_jumlah'], 2) ?? '-'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-end">
                                                <?= date('d F Y', strtotime($row['tanggal_pengeluaran'])) ?? '-'; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#SModal" data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                data-bs-total_jumlah="<?= $row['total_jumlah']; ?>"
                                                data-items='<?= json_encode($row['items']); ?>'>
                                                <i class="bi bi-search"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                data-bs-total_jumlah="<?= $row['total_jumlah']; ?>"
                                                data-items='<?= json_encode($row['items']); ?>'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                data-nama_pengeluaran="<?= $row['items'][0]['nama_pengeluaran'] ?? 'Tidak Ada'; ?>"
                                                data-items='<?= json_encode($row['items']); ?>'>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            </button>

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
                        <h5 class="modal-title" id="createModalLabel">Tambah Pengeluaran</h5>
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
                                                <label for="tanggal_pengeluaran" class="form-label">Tanggal
                                                    Pengeluaran</label>
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
                                                <label for="ket_pengeluaran" class="form-label">Keterangan
                                                    (Opsional)</label>
                                                <input type="text" class="form-control" id="ket_pengeluaran"
                                                    name="ket_pengeluaran" placeholder="">
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
                                <table class="table table-striped table-bordered" id="tabel-list-item-pengeluaran">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Keterangan</th>
                                            <th style="width:130px">Jumlah</th>
                                            <th style="width:20px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="row-item-bayar">
                                            <td>1</td>
                                            <td>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="checkbox" class="form-check-input toggle-select"
                                                        id="useselectkategori" />
                                                    <label class="form-check-label" for="useselectkategori">Use
                                                        Select Kategori</label>
                                                </div>

                                                <input type="text" class="form-control nama-pengeluaran-input"
                                                    name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>

                                                <select class="form-select detail-kategori-select"
                                                    name="nama_pengeluaran[]" style="display: none;" required>
                                                    <option selected disabled value="">Pilih pengeluaran</option>
                                                    <?php foreach ($detail_kategori_pengeluaran as $dkp): ?>
                                                    <option value="<?php echo $dkp['id']; ?>">
                                                        <?php echo $dkp['judul']; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>

                                            <td>
                                                <textarea class="form-control" name="keterangan[]"
                                                    placeholder="Keterangan"></textarea>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control jumlah" name="jumlah_barang[]"
                                                    placeholder="Jumlah" required>
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
                                            <td colspan="2">
                                                <div class="d-flex justify-content-between">
                                                    Total
                                                    <select name="jenis_bayar" class="form-select" style="width:auto">
                                                        <option value="1">Tunai</option>
                                                        <option value="2">Transfer</option>
                                                    </select>
                                                </div>
                                            </td>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="pengeluaran_id" id="editPengeluaranId">
                        <div class="row">
                            <div class="form-group col-4 mb-3">
                                <label for="editTanggalPengeluaran" class="form-label">Tanggal Pengeluaran</label>
                                <input type="date" class="form-control" id="editTanggalPengeluaran"
                                    name="tanggal_pengeluaran" required>
                            </div>
                            <div class="form-group col-4 mb-3">
                                <label for="bukti_pengeluaran" class="form-label">Unggah Bukti Pengeluaran
                                    (Opsional)</label>
                                <input type="file" class="form-control" id="bukti_pengeluaran" name="bukti_pengeluaran"
                                    accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="form-group col-4 mb-3">
                                <label for="editPihakTerlibat" class="form-label">Pihak Terlibat</label>
                                <input type="text" class="form-control" id="editPihakTerlibat" name="pihak_terlibat"
                                    placeholder="Contoh: Bagian Keuangan" required>
                            </div>
                            <div class="form-group col-6 mb-3">
                                <label for="detail_kategori_pengeluaran_id" class="form-label">Kategori
                                    Pengeluaran</label>
                                <select class="form-select" id="detail_kategori_pengeluaran_id"
                                    name="detail_kategori_pengeluaran_id" required>
                                    <option selected disabled value="">Pilih pengeluaran</option>
                                    <?php foreach ($detail_kategori_pengeluaran as $dkp): ?>
                                    <option value="<?php echo $dkp['id']; ?>">
                                        <?php echo $dkp['judul']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-6 mb-3">
                                <label for="editSumberDana" class="form-label">Sumber Dana</label>
                                <select class="form-control" id="editSumberDana" name="sumber_dana" required>
                                    <option selected disabled value="">Pilih Sumber Dana</option>
                                    <option value="Dana BOS">Dana BOS</option>
                                    <option value="Dana Sumbangan">Dana Sumbangan</option>
                                    <option value="Dana Sekolah">Dana Sekolah</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                            </div>
                            <hr>
                            <!-- ... other fields ... -->
                            <div class="form-group">
                                <table class="table table-striped table-bordered" id="edit-tabel-list-item-pengeluaran">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Keterangan</th>
                                            <th style="width:130px">Jumlah</th>
                                            <th style="width:20px">
                                                <button type="button" class="btn btn-outline-success add-row-edit">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Will be populated dynamically -->
                                    </tbody>
                                    <tfoot>
                                        <tr id="edit-row-total-bayar">
                                            <td></td>
                                            <td colspan="2">
                                                <div class="d-flex justify-content-between">
                                                    Total
                                                    <select name="jenis_bayar" id="edit_jenis_bayar" class="form-select"
                                                        style="width:auto">
                                                        <option value="1">Tunai</option>
                                                        <option value="2">Transfer</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold" style="padding-right:17px"
                                                id="edit-total-item-nilai-bayar">
                                                0
                                            </td>
                                            <td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" form="editForm" class="btn btn-primary">Simpan Perubahan</button>
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

    <!-- App Content -->
    </div>
</main>
<!-- App Main -->

<!-- Inisialisasi DataTables -->
<script>
$(document).ready(function() {
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

document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#tabel-list-item-pengeluaran tbody');
    const totalAmountDisplay = document.getElementById('total-item-nilai-bayar');
    const previewContainer = document.getElementById('image-preview-container');
    const fileInput = document.getElementById('bukti_pengeluaran');

    // Preview images with delete functionality
    fileInput.addEventListener('change', handleImagePreview);

    function handleImagePreview(event) {
        previewContainer.innerHTML = ''; // Clear previous previews
        selectedFiles = Array.from(event.target.files); // Update files array

        Array.from(event.target.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => createImagePreview(e.target.result, index);
                reader.readAsDataURL(file);
            }
        });

    }

    function createImagePreview(src, index) {
        const imageContainer = document.createElement('div');
        imageContainer.classList.add('position-relative', 'd-inline-block');

        const img = document.createElement('img');
        img.src = src;
        img.classList.add('img-thumbnail');
        img.style.width = '100px';
        img.style.height = '100px';
        img.style.objectFit = 'cover';

        const deleteBtn = document.createElement('button');
        deleteBtn.innerHTML = '&times;';
        deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute', 'top-0', 'end-0');
        deleteBtn.style.zIndex = '1';
        deleteBtn.addEventListener('click', () => removeImagePreview(index));

        imageContainer.append(img, deleteBtn);
        previewContainer.appendChild(imageContainer);
    }


    function removeImagePreview(index) {
        selectedFiles.splice(index, 1); // Remove file from array

        // Reset file input and reassign files
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        // Re-render previews
        handleImagePreview({
            target: {
                files: fileInput.files
            }
        });
    }

    // Add row functionality
    document.querySelector('.add-row').addEventListener('click', () => {
        addNewRow();
        updateTotal();
    });

    // Add a new row to the table
    function addNewRow() {
        const rowCount = tableBody.rows.length + 1;
        const newRow = document.createElement('tr');
        newRow.classList.add('row-item-bayar');
        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input toggle-select" />
                    <label class="form-check-label">Use Select Kategori</label>
                </div>
                <input type="text" class="form-control nama-pengeluaran-input" name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
                <select class="form-select detail-kategori-select" name="nama_pengeluaran[]" style="display: none;" required>
                    <option selected disabled value="">Pilih pengeluaran</option>
                    <?php foreach ($detail_kategori_pengeluaran as $dkp): ?>
                            <option value="<?php echo $dkp['id']; ?>"><?php echo $dkp['judul']; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><textarea class="form-control" name="keterangan[]" placeholder="Keterangan"></textarea></td>
            <td><input type="number" class="form-control jumlah" name="jumlah_barang[]" placeholder="Jumlah" required></td>
            <td><button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-dash-lg"></i></button></td>
        `;
        tableBody.appendChild(newRow);
        updateRowNumbers();
    }

    // Event delegation for table actions
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            // Find and remove the row
            e.target.closest('.row-item-bayar').remove();
            updateRowNumbers(); // Update row numbers after removal
            updateTotal(); // Update the total
        } else if (e.target.classList.contains('toggle-select')) {
            const row = e.target.closest('.row-item-bayar');
            toggleSelectDisplay(e.target.checked, row);
        }
    });

    tableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlah')) {
            updateTotal();
        }
    });

    function toggleSelectDisplay(isChecked, row) {
        const textInput = row.querySelector('.nama-pengeluaran-input');
        const selectInput = row.querySelector('.detail-kategori-select');
        textInput.style.display = isChecked ? 'none' : 'block';
        selectInput.style.display = isChecked ? 'block' : 'none';
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.jumlah').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        totalAmountDisplay.textContent = total.toLocaleString();
    }

    function updateRowNumbers() {
        document.querySelectorAll('.row-item-bayar').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }
});

// Function to calculate total
// function updateTotal() {
//     let total = 0;
//     const jumlahInputs = document.querySelectorAll('input[name="jumlah_barang[]"]');
//     jumlahInputs.forEach(input => {
//         total += parseFloat(input.value) || 0;
//     });
//     document.querySelector('#total-item-nilai-bayar').textContent = total;
// }

// Attach event listener to existing quantity inputs to update the total
// document.querySelectorAll('input[name="jumlah_barang[]"]').forEach(input => {
//     input.addEventListener('input', updateTotal);
// });


document.addEventListener('DOMContentLoaded', function() {
    // Handling Delete
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const pengeluaranId = button.getAttribute('data-bs-id');
            const items = JSON.parse(button.getAttribute('data-items'));

            const modalTitle = deleteModal.querySelector('.modal-title');
            modalTitle.textContent = `Hapus Data Pengeluaran ID: ${pengeluaranId}`;

            // Populate item list in modal body
            const itemList = deleteModal.querySelector('#item-list');
            itemList.innerHTML = '';
            items.forEach(item => {
                const listItem = document.createElement('li');
                listItem.textContent =
                    `${item.nama_pengeluaran} - Jumlah ${item.jumlah_barang} unit (${item.nilai_bayar})`;
                itemList.appendChild(listItem);
            });

            const deleteForm = deleteModal.querySelector('#deleteForm');
            deleteForm.querySelector('#delete-id').value = pengeluaranId;
        });
    }
});


// var editModal = document.getElementById('editModal');
// editModal.addEventListener('show.bs.modal', function (event) {
//     // Kode untuk mengisi data ke dalam modal
// });

// Add event listener to buttons that trigger the modal
document.querySelectorAll('button[data-bs-target="#editModal"]').forEach(button => {
    var editModal = document.getElementById('editModal');

    // editModal.addEventListener('show.bs.modal', function (event) {
    button.addEventListener('click', function() {
        // Fetch the data attributes from the clicked button
        const pengeluaranId = this.getAttribute('data-bs-id');
        const tanggalPengeluaran = this.getAttribute('data-bs-tanggal_pengeluaran');
        const pihakTerlibat = this.getAttribute('data-bs-pihak_terlibat');
        const sumberDana = this.getAttribute('data-bs-sumber_dana');
        const totalJumlah = this.getAttribute('data-bs-total_jumlah');
        const items = JSON.parse(this.getAttribute('data-items'));

        console.log(
            `ID: ${pengeluaranId}, Tahun ID: ${totalJumlah}`
        );

        // Populate the form fields in the modal
        document.getElementById('editPengeluaranId').value = pengeluaranId;
        document.getElementById('editTanggalPengeluaran').value = tanggalPengeluaran;
        document.getElementById('editPihakTerlibat').value = pihakTerlibat;
        document.getElementById('editSumberDana').value = sumberDana;
        // document.getElementById('editTotalJumlah').value = totalJumlah;

        // Populate the item list
        const itemList = document.getElementById('editItemList');
        itemList.innerHTML = ''; // Clear previous items

        items.forEach((item, index) => {
            let itemRow = `
                <div class="mb-3">
                    <label class="form-label">Item ${index + 1}: ${item.nama_pengeluaran}</label>
                    <input type="hidden" name="items[${index}][nama_pengeluaran]" value="${item.nama_pengeluaran}">
                    <input type="number" class="form-control" name="items[${index}][jumlah_barang]" value="${item.jumlah_barang}" required>
                </div>
            `;
            itemList.insertAdjacentHTML('beforeend', itemRow);
        });
    });
});

// Submit the form for updating
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Collect the form data
    const formData = new FormData(this);

    // Send the AJAX request to update the data
    fetch('update_pengeluaran.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.hide();

                // Optionally, refresh the table or update the row directly
                alert('Data updated successfully!');
            } else {
                alert('Failed to update data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>


<!-- 
optimalkan kembali code dengan kriteria berikut:
- pada "Nama Pengeluaran" saya ingin menggunakan kondisi jika "Use Select Kategori" isChecked maka dapat menggunakan
select dan ini memanggil data dari tabel detail_kategori_pengeluaran
- pada "upload bukti pengeluaran" dapat masuk kedalam tabel "bukti_pengeluaran_dana" dan saya ingin agar nama file
    diubah menjadi format datetime dan unicode contoh: "202411030101-kcAwH8.png"
        $buktiTable = $this->table('bukti_pengeluaran_dana');
        $buktiTable->addColumn('pengeluaran_id', 'integer', ['null' => false])
        ->addColumn('file_path', 'string', ['null' => false])
        ->addTimestamps()
        ->create(); 
- 
-->