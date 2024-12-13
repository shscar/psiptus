<?php
// Memulai buffering
ob_start();
include __DIR__ . '/../layouts/master.php';
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
            'file_path' => $row['file_path']
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
        $select = isset($kategori_id) ? 1 : 0;

        echo '<pre>';
        print_r($_POST);
        echo '</pre>';
        exit;

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
                        Pengeluaran Dana Sekolah
                    </h3>
                    <span class="text-danger">Maintance</span>
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
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combinedResults as $row): ?>
                                            <tr>
                                                <td><?= $row['pengeluaran_id'] ?? '-'; ?></td>
                                                <td>
                                                    <div class="text-end">
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
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#SModal" data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                        data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                        data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                        data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                        data-bs-total="<?= $row['total']; ?>"
                                                        data-items='<?= json_encode($row['items']); ?>'>
                                                        <i class="bi bi-search"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                        data-bs-target="#editModal" data-bs-id="<?= $row['pengeluaran_id']; ?>"
                                                        data-bs-tanggal_pengeluaran="<?= $row['tanggal_pengeluaran']; ?>"
                                                        data-bs-pihak_terlibat="<?= $row['pihak_terlibat']; ?>"
                                                        data-bs-sumber_dana="<?= $row['sumber_dana']; ?>"
                                                        data-bs-total="<?= $row['total']; ?>"
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
                            <div class="form-group" id="input-container">
                                <table class="table table-striped table-bordered" id="tabel-list-item-pengeluaran">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pengeluaran</th>
                                            <th>Item</th>
                                            <th>Satuan</th>
                                            <th>Harga</th>
                                            <th>Nominal</th>
                                            <th>Komite</th>
                                            <th>Bosda</th>
                                            <!-- <th>Keterangan</th> -->
                                            <th style="width:130px">Jumlah</th>
                                            <th style="width:20px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="row-item-bayar">
                                            <td>1</td>
                                            <td>
                                                <div class="form-group">
                                                    <!-- Switch Checkbox -->
                                                    <div class="form-check form-switch mb-2">
                                                        <input type="checkbox" class="form-check-input toggle-select"
                                                            id="useselectkategori">
                                                        <label class="form-check-label" for="useselectkategori">Use
                                                            Kategori</label>
                                                    </div>

                                                    <!-- Input Text -->
                                                    <div class="input-container">
                                                        <input type="text" class="form-control nama-pengeluaran-input"
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

    document.addEventListener('DOMContentLoaded', function () {
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
                <div class="form-group">
                    <!-- Switch Checkbox -->
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input toggle-select" id="useselectkategori${rowCount}">
                        <label class="form-check-label" for="useselectkategori${rowCount}">Use Kategori</label>
                    </div>

                    <!-- Input Text -->
                    <div class="input-container">
                        <input type="text" class="form-control nama-pengeluaran-input" name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
                    </div>

                    <!-- Select Dropdown -->
                    <div class="select-container" style="display: none;">
                        <select class="form-select detail-kategori-select" name="nama_pengeluaran[]">
                            <option selected disabled value="">Pilih pengeluaran</option>
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
                <input type="number" class="form-control item" name="item[]" placeholder="Item" min="1" required>
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
                    <option value="orang">Orang</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control harga" name="harga[]" placeholder="Harga" min="0" required>
            </td>
            <td>
                <input type="number" class="form-control nominal" name="nominal[]" placeholder="Rp 0.00" min="0" disabled>
            </td>
            <td>
                <input type="number" class="form-control komite" name="komite[]" placeholder="Komite" min="0" required>
            </td>
            <td>
                <input type="number" class="form-control bos" name="bosda[]" placeholder="Bosda" min="0" required>
            </td>
            <td>
                <input type="number" class="form-control jumlah" name="jumlah[]" placeholder="0.00" min="0" disabled>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-dash-lg"></i></button>
            </td>
        `;
            tableBody.appendChild(newRow);
            updateRowNumbers();
        }


        // Event delegation for table actions
        tableBody.addEventListener('click', function (e) {
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
        tableBody.addEventListener('input', function (e) {
            if (e.target.classList.contains('jumlah')) {
                updateTotal();
            }
        });

        // script untuk memilih (detail item) pengeluaran menggunakan select atau manual
        const checkbox = document.getElementById('useselectkategori');
        const formGroup = checkbox.closest('.form-group');
        const textInput = formGroup.querySelector('.nama-pengeluaran-input');
        const textInputContainer = formGroup.querySelector('.input-container');
        const selectInput = formGroup.querySelector('.detail-kategori-select');
        const selectInputContainer = formGroup.querySelector('.select-container');
        // Fungsi untuk mengatur tampilan dan status elemen
        function toggleInputDisplay(isChecked) {
            if (isChecked) {
                // Menampilkan select dropdown dan menyembunyikan input teks
                textInputContainer.style.display = 'none';
                selectInputContainer.style.display = 'block';
                // Nonaktifkan input teks dan aktifkan dropdown
                textInput.disabled = true;
                selectInput.disabled = false;
                // Validasi
                textInput.required = false;
                selectInput.required = true;
            } else {
                // Menampilkan input teks dan menyembunyikan select dropdown
                textInputContainer.style.display = 'block';
                selectInputContainer.style.display = 'none';
                // Nonaktifkan dropdown dan aktifkan input teks
                selectInput.disabled = true;
                textInput.disabled = false;
                // Validasi
                selectInput.required = false;
                textInput.required = true;
            }
        }
        // Event listener untuk checkbox
        checkbox.addEventListener('change', function () {
            toggleInputDisplay(checkbox.checked);
        });
        // Kondisi awal
        toggleInputDisplay(checkbox.checked);


        // // Mendapatkan semua elemen input (item, harga, menjadi nominal)
        // const items = document.querySelectorAll('.item');
        // const harga = document.querySelectorAll('.harga');
        // const nominal = document.querySelectorAll('.nominal');
        // // menghitung jumlah item
        // items.forEach((item, index) => {
        //     item.addEventListener('input', () => {
        //         calculateNominal(index);
        //     });
        // });
        // // menghitung jumlah harga
        // harga.forEach((hargaInput, index) => {
        //     hargaInput.addEventListener('input', () => {
        //         calculateNominal(index);
        //     });
        // });
        // // kalkulasi item dikali harga
        // function calculateNominal(index) {
        //     const itemValue = parseFloat(items[index].value) || 0;
        //     const hargaValue = parseFloat(harga[index].value) || 0;
        //     const totalNominal = itemValue * hargaValue;
        //     nominal[index].value = totalNominal.toFixed(2);
        // }


    //     // Mendapatkan semua elemen input (komite, bosda, menjadi jumlah)
    //     const komiteInputs = document.querySelectorAll('.komite');
    //     const bosInputs = document.querySelectorAll('.bos');
    //     const jumlahInputs = document.querySelectorAll('.jumlah');
    //     // Fungsi untuk menghitung jumlah
    //     function hitungJumlah(index) {
    //         const komiteValue = parseFloat(komiteInputs[index].value) || 0;
    //         const bosValue = parseFloat(bosInputs[index].value) || 0;
    //         const total = komiteValue + bosValue;
    //         jumlahInputs[index].value = total.toFixed(2); // Menampilkan dua desimal
    //     }
    //     // Menambahkan event listener untuk setiap input
    //     komiteInputs.forEach((input, index) => {
    //         input.addEventListener('input', () => hitungJumlah(index));
    //     });
    //     bosInputs.forEach((input, index) => {
    //         input.addEventListener('input', () => hitungJumlah(index));
    //     });
    //     // update total dari jumlah
    //     function updateTotal() {
    //         let total = 0;
    //         document.querySelectorAll('.jumlah').forEach(input => {
    //             total += parseFloat(input.value) || 0;
    //         });
    //         totalAmountDisplay.textContent = total.toLocaleString();
    //     }
    //     // update penambahan (detail item) untuk pengeluaran
    //     function updateRowNumbers() {
    //         document.querySelectorAll('.row-item-bayar').forEach((row, index) => {
    //             row.querySelector('td:first-child').textContent = index + 1;
    //         });
    //     }
    // });

    // // Fungsi untuk menghitung total
    // function updateTotal() {
    //     let total = 0;
    //     const jumlahInputs = document.querySelectorAll('input[name="jumlah[]"]');
    //     jumlahInputs.forEach(input => {
    //         total += parseFloat(input.value) || 0;
    //     });
    //     document.querySelector('#total-item-nilai-bayar').textContent = total.toFixed(2); // Format total dengan 2 desimal
    // }
    // // Fungsi debounce untuk membatasi frekuensi pemanggilan fungsi
    // function debounce(func, delay) {
    //     let timeout;
    //     return function (...args) {
    //         clearTimeout(timeout);
    //         timeout = setTimeout(() => func.apply(this, args), delay);
    //     };
    // }
    // // Mengaitkan event listener pada kontainer input
    // document.querySelector('#input-container').addEventListener('input', debounce(updateTotal, 300));


    // document.addEventListener('DOMContentLoaded', function () {
    //     // Handling Delete
    //     const deleteModal = document.getElementById('deleteModal');
    //     if (deleteModal) {
    //         deleteModal.addEventListener('show.bs.modal', function (event) {
    //             const button = event.relatedTarget;
    //             const pengeluaranId = button.getAttribute('data-bs-id');
    //             const items = JSON.parse(button.getAttribute('data-items'));

    //             const modalTitle = deleteModal.querySelector('.modal-title');
    //             modalTitle.textContent = `Hapus Data Pengeluaran ID: ${pengeluaranId}`;

    //             // Populate item list in modal body
    //             const itemList = deleteModal.querySelector('#item-list');
    //             itemList.innerHTML = '';
    //             items.forEach(item => {
    //                 const listItem = document.createElement('li');
    //                 listItem.textContent =
    //                     `${item.nama_pengeluaran} - Jumlah ${item.jumlah} unit (${item.nilai_bayar})`;
    //                 itemList.appendChild(listItem);
    //             });

    //             const deleteForm = deleteModal.querySelector('#deleteForm');
    //             deleteForm.querySelector('#delete-id').value = pengeluaranId;
    //         });
    //     }
    });

</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

