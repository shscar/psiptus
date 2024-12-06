<?php
// Memulai buffering
ob_start();
include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Query untuk mengambil data pengeluaran dan item pengeluaran
$stmt = $db->prepare("
    SELECT 
        pd.id AS pengeluaran_id,
        pd.tanggal_pengeluaran,
        bpd.file_path AS bukti_pengeluaran,
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
    LEFT JOIN bukti_pengeluaran_dana bpd ON pd.bukti_pengeluaran_id = bpd.id
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
    // Jika ada data item pengeluaran, tambahkan ke dalam array items
    if (!empty($row['nama_pengeluaran'])) {
        $combinedResults[$pengeluaranId]['items'][] = [
            'nama_pengeluaran' => $row['nama_pengeluaran'],
            'item_keterangan' => $row['item_keterangan'],
            'jumlah_barang' => $row['jumlah_barang'],
            'nilai_bayar' => $row['nilai_bayar']
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
                        <!-- <h3 class="card-title text-danger">Edit Detail sedang maintenance</h3> -->
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
                                                    <button>aa</button>
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
                                                <select class="form-select detail-kategori-select" name="kategori_id[]"
                                                    style="display: none;" disabled>
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
        let selectedFiles = [];

        // Event listener untuk preview gambar
        fileInput.addEventListener('change', previewSelectedImages);

        // Handle preview gambar
        function previewSelectedImages(event) {
            clearImagePreviews();
            selectedFiles = Array.from(event.target.files);

            selectedFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => createImagePreview(e.target.result, index);
                    reader.readAsDataURL(file);
                }
            });
        }

        // Hapus preview gambar sebelumnya
        function clearImagePreviews() {
            previewContainer.innerHTML = '';
        }

        // Membuat elemen preview gambar
        function createImagePreview(src, index) {
            const imageContainer = document.createElement('div');
            imageContainer.classList.add('position-relative', 'd-inline-block');

            const img = document.createElement('img');
            img.src = src;
            img.classList.add('img-thumbnail');
            img.style = 'width: 100px; height: 100px; object-fit: cover;';

            const deleteBtn = document.createElement('button');
            deleteBtn.innerHTML = '&times;';
            deleteBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute', 'top-0', 'end-0');
            deleteBtn.style.zIndex = '1';
            deleteBtn.addEventListener('click', () => removeImagePreview(index));

            imageContainer.append(img, deleteBtn);
            previewContainer.appendChild(imageContainer);
        }

        // Menghapus gambar dari preview dan memperbarui input file
        function removeImagePreview(index) {
            selectedFiles.splice(index, 1);
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            previewSelectedImages({
                target: {
                    files: fileInput.files
                }
            });
        }

        // Tambah baris baru ke tabel
        document.querySelector('.add-row').addEventListener('click', () => {
            addNewTableRow();
            updateTotalAmount();
        });

        // Membuat baris baru
        function addNewTableRow() {
            const newRow = document.createElement('tr');
            newRow.classList.add('row-item-bayar');
            newRow.innerHTML = `
            <td></td>
            <td>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" class="form-check-input toggle-select" />
                    <label class="form-check-label">Use Select Kategori</label>
                </div>
                <input type="text" class="form-control nama-pengeluaran-input" name="nama_pengeluaran[]" placeholder="Nama Pengeluaran" required>
                <select class="form-select detail-kategori-select" name="kategori_id[]" style="display: none;">
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

        // Event delegation untuk aksi pada tabel
        // tableBody.addEventListener('click', (e) => {
        //     if (e.target.closest('.remove-row')) {
        //         e.target.closest('.row-item-bayar').remove();
        //         updateRowNumbers();
        //         updateTotalAmount();
        //     } else if (e.target.classList.contains('toggle-select')) {
        //         toggleSelectInput(e.target);
        //     }
        // });

        tableBody.addEventListener('input', (e) => {
            if (e.target.classList.contains('jumlah')) {
                updateTotalAmount();
            }
        });

        // Update total jumlah bayar
        function updateTotalAmount() {
            const total = Array.from(document.querySelectorAll('.jumlah'))
                .reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
            totalAmountDisplay.textContent = total.toLocaleString();
        }


        function toggleSelectDisplay(isChecked, row) {
            const textInput = row.querySelector('.nama-pengeluaran-input');
            const selectInput = row.querySelector('.detail-kategori-select');

            if (isChecked) {
                textInput.setAttribute('disabled', 'disabled');
                textInput.removeAttribute('required');
                textInput.style.display = 'none';

                selectInput.removeAttribute('disabled');
                selectInput.setAttribute('required', 'required');
                selectInput.style.display = 'block';
            } else {
                selectInput.setAttribute('disabled', 'disabled');
                selectInput.removeAttribute('required');
                selectInput.style.display = 'none';

                textInput.removeAttribute('disabled');
                textInput.setAttribute('required', 'required');
                textInput.style.display = 'block';
            }
        }

        tableBody.addEventListener('click', function (e) {
            if (e.target.classList.contains('toggle-select')) {
                const row = e.target.closest('tr');
                toggleSelectDisplay(e.target.checked, row);
            } else if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                updateRowNumbers();
            }
        });

        function updateRowNumbers() {
            document.querySelectorAll('.row-item-bayar').forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        // Tambahkan event listener pada tombol tambah baris
        document.querySelector('.add-row').addEventListener('click', addNewTableRow);
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