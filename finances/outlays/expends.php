<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

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
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
</style>
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


// Function to handle file uploads
function handleFileUpload($file)
{
    $uploadDir = 'assets/images/dana_pengeluaran/';
    $fileName = pathinfo($file['name'], PATHINFO_FILENAME);
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Create a new file name with date and random code
    $newFileName = $fileName . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFileName;

    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $newFileName;
    }

    return null; // or handle error
}

function uploadFile($file)
{
    $uploadDir = 'assets/images/dana_pengeluaran/';
    if ($file['name']) {
        $randomCode = bin2hex(random_bytes(4));
        $date = date('Y-m-d');
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = "bukti_pengeluaran_{$date}_{$randomCode}.{$fileExtension}";
        $targetFilePath = $uploadDir . $newFileName;

        // Validate file
        if ($file['size'] > 500000) {
            throw new Exception("File is too large.");
        }
        if (!in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'pdf'])) {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.");
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            throw new Exception("Error uploading file.");
        }
        return $newFileName; // Return the name of the uploaded file
    }
    return null; // Return null if no file is uploaded
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

        $stmt = $db->prepare("INSERT INTO pengeluaran_dana (tanggal_pengeluaran, bukti_pengeluaran, pihak_terlibat, detail_kategori_pengeluaran_id, sumber_dana, jenis_bayar, total_jumlah) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Handle file upload
        $bukti_pengeluaran = null;
        if (isset($_FILES['bukti_pengeluaran']) && $_FILES['bukti_pengeluaran']['error'] == UPLOAD_ERR_OK) {
            $bukti_pengeluaran = handleFileUpload($_FILES['bukti_pengeluaran']);
        }

        // Calculate total from the jumlah_barang inputs
        foreach ($_POST['jumlah_barang'] as $jumlah) {
            $total_jumlah += (float) $jumlah;
        }

        $stmt->execute([$tanggal_pengeluaran, $bukti_pengeluaran, $pihak_terlibat, $detail_kategori_pengeluaran_id, $sumber_dana, $jenis_bayar, $total_jumlah]);

        // Get the last inserted ID for the pengeluaran_dana table
        $pengeluaran_id = $db->lastInsertId();
        $itemStmt = $db->prepare("INSERT INTO item_pengeluaran_dana (pengeluaran_id, nama_pengeluaran, keterangan, jumlah_barang, nilai_bayar) VALUES (?, ?, ?, ?, ?)");

        // Insert each item
        foreach ($_POST['nama_pengeluaran'] as $index => $nama_pengeluaran) {
            $keterangan = $_POST['keterangan'][$index];
            $jumlah_barang = $_POST['jumlah_barang'][$index];

            // Calculate nilai_bayar (assuming it's the same as jumlah_barang for this example)
            $nilai_bayar = (float) $jumlah_barang;

            // Execute the statement
            $itemStmt->execute([$pengeluaran_id, $nama_pengeluaran, $keterangan, $jumlah_barang, $nilai_bayar]);
        }

        // redirect or show a success message
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
}

// Mengakhiri buffering
ob_end_flush();
?>


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
                            Pengeluaran
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
                    <button type="button" class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                    </button>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if (!empty($combinedResults)): ?>
                                <table id="datatable" class="table table-striped table-bordered pt-3">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Sumber Dana</th>
                                            <th>Jumlah</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combinedResults as $row): ?>
                                            <tr>
                                                <td><?= $row['pengeluaran_id'] ?? '-'; ?></td>
                                                <td>
                                                    <ul class="list-circle">
                                                        <?php foreach ($row['items'] as $item): ?>
                                                            <li><?= $item['nama_pengeluaran']; ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                                <td><?= $row['sumber_dana'] ?? '-'; ?></td>
                                                <td>
                                                    <div class="text-end"><?= number_format($row['total_jumlah'], 2) ?? '-'; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-end">
                                                        <?= date('d F Y', strtotime($row['tanggal_pengeluaran'])) ?? '-'; ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#editModal" data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                        data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                        data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                        data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                        data-bs-total_jumlah="<?= $row['total_jumlah']; ?>"
                                                        data-items='<?= json_encode($row['items']); ?>'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger" data-bs-toggle="modal"
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
                        <h5 class="modal-title" id="createModalLabel">Tambah Tagihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create">
                            <div class="row">
                                <div class="form-group col-4 mb-3">
                                    <label for="tanggal_pengeluaran" class="form-label">Tanggal Pengeluaran</label>
                                    <input type="date" class="form-control" id="tanggal_pengeluaran"
                                        name="tanggal_pengeluaran" required>
                                </div>
                                <div class="form-group col-4 mb-3">
                                    <label for="bukti_pengeluaran" class="form-label">Unggah Bukti Pengeluaran
                                        (Opsional)</label>
                                    <input type="file" class="form-control" id="bukti_pengeluaran"
                                        name="bukti_pengeluaran" accept=".jpg,.jpeg,.png,.pdf">
                                </div>
                                <div class="form-group col-4 mb-3">
                                    <label for="pihak_terlibat" class="form-label">Pihak Terlibat</label>
                                    <input type="text" class="form-control" id="pihak_terlibat" name="pihak_terlibat"
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
                                    <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                    <select class="form-control" id="sumber_dana" name="sumber_dana" required>
                                        <option selected disabled value="">Pilih Sumber Dana</option>
                                        <option value="Dana BOS">Dana BOS</option>
                                        <option value="Dana Sumbangan">Dana Sumbangan</option>
                                        <option value="Dana Sekolah">Dana Sekolah</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
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
                                                    <input type="text" class="form-control" name="nama_pengeluaran[]"
                                                        placeholder="Nama Pengeluaran" required>
                                                </td>
                                                <td>
                                                    <textarea class="form-control" name="keterangan[]"
                                                        placeholder="Keterangan"></textarea>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control" name="jumlah_barang[]"
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
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" id="edit_pengeluaran_id" name="pengeluaran_id">
                            <!-- The rest of the form fields will be similar to your create form but with IDs prefixed with 'edit_' -->
                            <div class="row">
                                <!-- ... (similar to create form fields, with 'edit_' prefixes) ... -->
                                <!-- For example: -->
                                <div class="form-group col-4 mb-3">
                                    <label for="edit_tanggal_pengeluaran" class="form-label">Tanggal Pengeluaran</label>
                                    <input type="date" class="form-control" id="edit_tanggal_pengeluaran"
                                        name="tanggal_pengeluaran" required>
                                </div>
                                <!-- ... other fields ... -->
                                <div class="form-group">
                                    <table class="table table-striped table-bordered"
                                        id="edit-tabel-list-item-pengeluaran">
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
                                            <!-- Will be populated dynamically -->
                                        </tbody>
                                        <tfoot>
                                            <tr id="edit-row-total-bayar">
                                                <td></td>
                                                <td colspan="2">
                                                    <div class="d-flex justify-content-between">
                                                        Total
                                                        <select name="jenis_bayar" id="edit_jenis_bayar"
                                                            class="form-select" style="width:auto">
                                                            <option value="1">Tunai</option>
                                                            <option value="2">Transfer</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold" style="padding-right:17px"
                                                    id="edit-total-item-nilai-bayar">
                                                    0
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <button type="button" class="btn btn-outline-success add-row-edit">
                                        <i class="bi bi-plus-lg"></i> Tambah Baris
                                    </button>
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
    $(document).ready(function () {
        $('#datatable').dataTable();

        $("[data-toggle=tooltip]").tooltip();

    });

    // Event listener for adding a new row
    document.querySelector('.add-row').addEventListener('click', function () {
        let tableBody = document.querySelector('#tabel-list-item-pengeluaran tbody');
        let rowCount = tableBody.rows.length + 1;
        let newRow = document.createElement('tr');
        newRow.classList.add('row-item-bayar');

        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <input type="text" class="form-control" name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
            </td>
            <td>
                <textarea class="form-control" name="keterangan[]" placeholder="Keterangan"></textarea>
            </td>
            <td>
                <input type="number" class="form-control jumlah" name="jumlah_barang[]" placeholder="Jumlah" required>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger remove-row">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(newRow);

        // Attach input event listener to the new quantity input field
        newRow.querySelector('input[name="jumlah_barang[]"]').addEventListener('input', function () {
            updateTotal();
        });

        // Attach event listener to remove the row
        newRow.querySelector('.remove-row').addEventListener('click', function () {
            this.parentElement.parentElement.remove();
            updateTotal();
        });

        updateTotal();
    });

    // Function to calculate total
    function updateTotal() {
        let total = 0;
        const jumlahInputs = document.querySelectorAll('input[name="jumlah_barang[]"]');
        jumlahInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.querySelector('#total-item-nilai-bayar').textContent = total;
    }

    // Attach event listener to existing quantity inputs to update the total
    document.querySelectorAll('input[name="jumlah_barang[]"]').forEach(input => {
        input.addEventListener('input', updateTotal);
    });



    // // Function to open the edit modal and populate the fields with data
    // function openEditModal(data) {
    //     document.getElementById('edit_pengeluaran_id').value = data.pengeluaran_id;
    //     document.getElementById('edit_tanggal_pengeluaran').value = data.tanggal_pengeluaran;
    //     document.getElementById('edit_pihak_terlibat').value = data.pihak_terlibat;
    //     document.getElementById('edit_detail_kategori_pengeluaran_id').value = data.detail_kategori_pengeluaran_id;
    //     document.getElementById('edit_sumber_dana').value = data.sumber_dana;

    //     // Populate the item list
    //     let tbody = document.querySelector('#edit-tabel-list-item-pengeluaran tbody');
    //     tbody.innerHTML = ''; // Clear existing rows
    //     data.items.forEach((item, index) => {
    //         let newRow = document.createElement('tr');
    //         newRow.classList.add('row-item-bayar');

    //         newRow.innerHTML = `
    //             <td>${index + 1}</td>
    //             <td>
    //                 <input type="text" class="form-control" name="nama_pengeluaran[]" value="${item.nama_pengeluaran}" required>
    //             </td>
    //             <td>
    //                 <textarea class="form-control" name="keterangan[]">${item.item_keterangan}</textarea>
    //             </td>
    //             <td>
    //                 <input type="number" class="form-control" name="jumlah_barang[]" value="${item.jumlah_barang}" required>
    //             </td>
    //             <td>
    //                 <button type="button" class="btn btn-outline-danger remove-row">
    //                     <i class="bi bi-dash-lg"></i>
    //                 </button>
    //             </td>
    //         `;
    //         tbody.appendChild(newRow);

    //         // Attach event listener to remove the row
    //         newRow.querySelector('.remove-row').addEventListener('click', function() {
    //             this.parentElement.parentElement.remove();
    //             updateEditTotal();
    //         });
    //     });

    //     // Open modal
    //     let editModal = new bootstrap.Modal(document.getElementById('editModal'));
    //     editModal.show();
    // }

    // // Function to calculate total for edit modal
    // function updateEditTotal() {
    //     let total = 0;
    //     const jumlahInputs = document.querySelectorAll('#edit-tabel-list-item-pengeluaran input[name="jumlah_barang[]"]');
    //     jumlahInputs.forEach(input => {
    //         total += parseFloat(input.value) || 0;
    //     });
    //     document.querySelector('#edit-total-item-nilai-bayar').textContent = total;
    // }

    // // Event listener to update total when editing quantities in the edit modal
    // document.querySelectorAll('#edit-tabel-list-item-pengeluaran input[name="jumlah_barang[]"]').forEach(input => {
    //     input.addEventListener('input', updateEditTotal);
    // });



    document.addEventListener('DOMContentLoaded', function () {

        // Handling Delete
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
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

    document.addEventListener('DOMContentLoaded', function () {
        // Function to update total in the edit modal
        function updateEditTotal() {
            let total = 0;
            const jumlahInputs = document.querySelectorAll(
                '#edit-tabel-list-item-pengeluaran input[name="jumlah_barang[]"]');
            jumlahInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.querySelector('#edit-total-item-nilai-bayar').textContent = total;
        }

        // Event listener for adding a new row in edit modal
        document.querySelector('.add-row-edit').addEventListener('click', function () {
            let tableBody = document.querySelector('#edit-tabel-list-item-pengeluaran tbody');
            let rowCount = tableBody.rows.length + 1;
            let newRow = document.createElement('tr');
            newRow.classList.add('row-item-bayar');

            newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <input type="text" class="form-control" name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
            </td>
            <td>
                <textarea class="form-control" name="keterangan[]" placeholder="Keterangan"></textarea>
            </td>
            <td>
                <input type="number" class="form-control jumlah" name="jumlah_barang[]" placeholder="Jumlah" required>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger remove-row-edit">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </td>
        `;
            tableBody.appendChild(newRow);

            // Attach input event listener to the new quantity input field
            newRow.querySelector('input[name="jumlah_barang[]"]').addEventListener('input', function () {
                updateEditTotal();
            });

            // Attach event listener to remove the row
            newRow.querySelector('.remove-row-edit').addEventListener('click', function () {
                this.parentElement.parentElement.remove();
                updateEditTotal();
                updateRowNumbers('#edit-tabel-list-item-pengeluaran');
            });

            updateEditTotal();
        });

        // Function to update row numbers after deletion
        function updateRowNumbers(tableSelector) {
            const rows = document.querySelectorAll(`${tableSelector} tbody tr`);
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        // Handling Edit Modal Show Event
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const pengeluaranId = button.getAttribute('data-bs-id');
                const pengeluaranData = JSON.parse(button.getAttribute('data-pengeluaran'));

                // Populate form fields with existing data
                document.getElementById('edit_pengeluaran_id').value = pengeluaranData.pengeluaran_id;
                document.getElementById('edit_tanggal_pengeluaran').value = pengeluaranData
                    .tanggal_pengeluaran;
                document.getElementById('edit_pihak_terlibat').value = pengeluaranData.pihak_terlibat;
                document.getElementById('edit_detail_kategori_pengeluaran_id').value = pengeluaranData
                    .detail_kategori_pengeluaran_id;
                document.getElementById('edit_sumber_dana').value = pengeluaranData.sumber_dana;
                document.getElementById('edit_jenis_bayar').value = pengeluaranData.jenis_bayar;

                // Populate the items table
                let tbody = document.querySelector('#edit-tabel-list-item-pengeluaran tbody');
                tbody.innerHTML = ''; // Clear existing rows
                pengeluaranData.items.forEach((item, index) => {
                    let newRow = document.createElement('tr');
                    newRow.classList.add('row-item-bayar');

                    newRow.innerHTML = `
                    <td>${index + 1}</td>
                    <td>
                        <input type="text" class="form-control" name="nama_pengeluaran[]" value="${item.nama_pengeluaran}" required>
                    </td>
                    <td>
                        <textarea class="form-control" name="keterangan[]">${item.item_keterangan || ''}</textarea>
                    </td>
                    <td>
                        <input type="number" class="form-control jumlah" name="jumlah_barang[]" value="${item.jumlah_barang}" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger remove-row-edit">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </td>
                `;
                    tbody.appendChild(newRow);

                    // Attach input event listener to the quantity input field
                    newRow.querySelector('input[name="jumlah_barang[]"]').addEventListener('input',
                        function () {
                            updateEditTotal();
                        });

                    // Attach event listener to remove the row
                    newRow.querySelector('.remove-row-edit').addEventListener('click', function () {
                        this.parentElement.parentElement.remove();
                        updateEditTotal();
                        updateRowNumbers('#edit-tabel-list-item-pengeluaran');
                    });
                });

                // Update total
                updateEditTotal();
            });
        }
    });
</script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>