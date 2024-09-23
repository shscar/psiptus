<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

<?php
// Memulai output buffering
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


// var_dump($results);

// Mengakhiri output buffering
ob_end_flush();
?>


<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Pemasukan Dana Operasional Sekolah</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Bos
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- App Content -->
    <main class="app-content">
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
                                            <th>Total Jumlah</th>
                                            <th>Tanggal Pengeluaran</th>
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
                                                <td>
                                                    <div class="form-inline btn-action-group">
                                                        <button type="button"
                                                            class="d-flex btn btn-success btn-edit btn-xs me-1"
                                                            data-id="<?= $row['pengeluaran_id']; ?>">
                                                            <span class="btn-label-icon"><i class="fas fa-edit"></i></span>
                                                        </button>
                                                        <button type="button" class="d-flex btn btn-danger btn-delete btn-xs"
                                                            data-id="<?= $row['pengeluaran_id']; ?>"
                                                            data-delete-title="Hapus data pengeluaran dengan ID <?= $row['pengeluaran_id']; ?>?">
                                                            <span class="btn-label-icon"><i class="fas fa-times"></i></span>
                                                        </button>
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

        <!-- /.modal-dialog create -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Tambah Tagihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="proses_pengeluaran.php" method="POST" enctype="multipart/form-data">
                            <!-- Informasi Umum -->
                            <div class="row">
                                <div class="form-group col-4 mb-3">
                                    <label for="tanggal_pengeluaran" class="form-label">Tanggal Pengeluaran</label>
                                    <input type="date" class="form-control" id="tanggal_pengeluaran"
                                        name="tanggal_pengeluaran" required>
                                </div>

                                <!-- Dokumentasi Bukti -->
                                <div class="form-group col-4 mb-3">
                                    <label for="bukti_pengeluaran" class="form-label">Unggah Bukti Pengeluaran
                                        (Opsional)</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="bukti_pengeluaran"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                        <label class="input-group-text" for="bukti_pengeluaran">Upload</label>
                                    </div>
                                </div>

                                <!-- Tindakan -->
                                <div class="form-group col-4 mb-3">
                                    <label for="pihak_terlibat" class="form-label">Pihak Terlibat</label>
                                    <input type="text" class="form-control" id="pihak_terlibat" name="pihak_terlibat"
                                        placeholder="Contoh: Bagian Keuangan, Kepala Sekolah">
                                </div>

                                <div class="form-group col-6 mb-3">
                                    <label for="kategori_pengeluaran" class="form-label">Kategori Pengeluaran</label>
                                    <select class="form-control" id="kategori_pengeluaran" name="kategori_pengeluaran"
                                        required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Operasional">Operasional</option>
                                        <option value="Pembelian Barang">Pembelian Barang</option>
                                        <option value="Gaji">Gaji</option>
                                        <option value="Pemeliharaan">Pemeliharaan</option>
                                        <option value="Kegiatan Siswa">Kegiatan Siswa</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                </div>

                                <div class="form-group col-6 mb-3" class="form-label">
                                    <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                    <select class="form-control" id="sumber_dana" name="sumber_dana" required>
                                        <option value="">Pilih Sumber Dana</option>
                                        <option value="Dana BOS">Dana BOS</option>
                                        <option value="Dana Sumbangan">Dana Sumbangan</option>
                                        <option value="Dana Sekolah">Dana Sekolah</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <table class="table table-striped table-bordered" id="tabel-list-item-pengeluaran"
                                        style="width: 100%;">
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
                                                    <input type="text" class="form-control mb-2"
                                                        name="nama_pengeluaran[]" placeholder="Nama Pengeluaran">
                                                </td>
                                                <td>
                                                    <textarea class="form-control" style="height:auto"
                                                        name="keterangan[]" placeholder="Keterangan"></textarea>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control text-end item-nilai-bayar"
                                                        name="jumlah_barang[]" placeholder="Jumlah">
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
                                                        <select name="id_jenis_bayar" class="form-select"
                                                            style="width:auto">
                                                            <option value="1">Tunai</option>
                                                            <option value="2">Transfer</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold" style="padding-right:17px"
                                                    id="total-item-nilai-bayar">
                                                    0</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- App Content -->
    </main>
    <!-- App Main -->

    <script>
        // Script untuk menambah baris baru
        document.querySelector('.add-row').addEventListener('click', function () {
            let tableBody = document.querySelector('#tabel-list-item-pengeluaran tbody');
            let rowCount = tableBody.rows.length + 1;
            let newRow = document.createElement('tr');
            newRow.classList.add('row-item-bayar');

            newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <input type="text" class="form-control mb-2" name="nama_barang[]" placeholder="Nama Barang">
            </td>
            <td>
                <textarea class="form-control" style="height:auto" name="keterangan[]" placeholder="Keterangan"></textarea>
            </td>
            <td>
                <input type="number" class="form-control text-end item-nilai-bayar" name="jumlah_barang[]" placeholder="Jumlah">
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger remove-row">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </td>
        `;
            tableBody.appendChild(newRow);

            // Script untuk menghapus baris
            newRow.querySelector('.remove-row').addEventListener('click', function () {
                this.parentElement.parentElement.remove();
            });
        });
    </script>