<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // Masukkan data ke tabel detail_riwayat_transaksi_siswa_tarifspp
        if (isset($_POST['item_type']) && is_array($_POST['item_type'])) {
            foreach ($_POST['item_type'] as $key => $type) {
                $item_id = $_POST['item_id'][$key];
                $jumlah_bayar = floatval($_POST['jumlah_bayar'][$key]);

                if ($type === 'tarif_spp') {
                    $stmt = $db->prepare("INSERT INTO detail_riwayat_transaksi_siswa_tarifspp 
                        (riwayat_transaksi_id, tarif_spp_id, jumlah_bayar) 
                        VALUES (:riwayat_transaksi_id, :tarif_spp_id, :jumlah_bayar)");
                    $stmt->execute([
                        ':riwayat_transaksi_id' => $riwayat_transaksi_id,
                        ':tarif_spp_id' => $item_id,
                        ':jumlah_bayar' => $jumlah_bayar
                    ]);
                }

                // Masukkan data ke tabel detail_riwayat_transaksi_siswa_pembayaranlain
                if ($type === 'pembayaran_lainnya') {
                    $stmt = $db->prepare("INSERT INTO detail_riwayat_transaksi_siswa_pembayaranlain 
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

// Ambil data siswa dari database untuk digunakan di Select2
$stmt = $db->query("SELECT id, nis, nama_lengkap, kelas_id FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch data
$query = "
    SELECT 
        rts.id AS riwayat_id,
        s.nis,
        s.nama_lengkap,
        k.nama_kelas,
        rts.tanggal_bayar,
        rts.jenis_bayar,
        rts.total_bayar,
        ts.nama_tarif AS tarif_spp,
        spl.nama_pembayaran AS pembayaran_lainnya
    FROM riwayat_transaksi_siswa rts
    LEFT JOIN siswa s ON rts.siswa_id = s.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN detail_riwayat_transaksi_siswa_tarifspp drtst ON rts.id = drtst.riwayat_transaksi_id
    LEFT JOIN tarif_spp ts ON drtst.tarif_spp_id = ts.id
    LEFT JOIN detail_riwayat_transaksi_siswa_pembayaranlain drtspl ON rts.id = drtspl.riwayat_transaksi_id
    LEFT JOIN siswa_pembayaran_lainnya spl ON drtspl.pembayaran_lainnya_id = spl.id
    ORDER BY rts.tanggal_bayar DESC
";
$stmt = $db->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Combine results based on `riwayat_id`
$combinedResults = [];
foreach ($results as $row) {
    $riwayatId = $row['riwayat_id'];
    if (!isset($combinedResults[$riwayatId])) {
        $combinedResults[$riwayatId] = [
            'riwayat_id' => $row['riwayat_id'],
            'nis' => $row['nis'],
            'nama_lengkap' => $row['nama_lengkap'],
            'nama_kelas' => $row['nama_kelas'],
            'tanggal_bayar' => $row['tanggal_bayar'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total_bayar' => $row['total_bayar'],
            'items' => []
        ];
    }
    // Append jenis pembayaran items
    if (!empty($row['tarif_spp'])) {
        $combinedResults[$riwayatId]['items'][] = [
            'jenis_bayar' => 'TS: ' . $row['tarif_spp']
        ];
    }
    if (!empty($row['pembayaran_lainnya'])) {
        $combinedResults[$riwayatId]['items'][] = [
            'jenis_bayar' => 'PL: ' . $row['pembayaran_lainnya']
        ];
    }

}
// Transform combined results into a numeric array
$combinedResults = array_values($combinedResults);


// Mengakhiri output buffering
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pembayaran</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- DataTables CSS/JS Dependencies -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
</head>

<body>
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

                <!-- Layouts Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Grade Level </h3>
                        <button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal"
                            data-bs-target="#addDataModal">
                            <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                        </button>
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
                                            <td>
                                                <button>cek</button>
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
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addDataLabel">Tambah Data Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addDataForm" method="POST">
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
                                                name="tanggal_bayar">
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
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" form="addDataForm" class="btn btn-primary">Simpan</button>
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
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function () {
            // $('#DataPembayaranSiswa').DataTable();

            // Initialize Select2 with dropdownParent option
            $('#student_name').select2({
                placeholder: 'Pilih Nama Siswa',
                dropdownParent: $('#addDataModal')
            });

            // Validasi tombol berdasarkan pemilihan dropdown siswa
            $('#student_name').on('change', function () {
                var selectedValue = $(this).val();
                if (selectedValue) {
                    $('#modalspilihtagihan').prop('disabled', false);
                } else {
                    $('#modalspilihtagihan').prop('disabled',
                        true);
                }

                // Ambil nis siswa berdasarkan pilihan di dropdown
                var nis = $('#student_name option:selected').data('nis');
                $('#nis_siswa').val(nis);
            }).trigger('change');

            // Initialize DataTable
            $('#jenisPembayaranTable').DataTable();

            // Fetch and populate data in the second modal
            $('#student_name').on('change', function () {
                const id = $(this).val();

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
                                table.row.add([
                                    index++,
                                    item.nama_tarif,
                                    item.nominal,
                                    item.total_bayar,
                                    item.kurang_bayar,
                                    `<button class="btn btn-success btn-sm pilihBtn" data-id="${item.item_id}" data-type="${item.type}">+ Pilih</button>`
                                ]).draw();
                            });

                            response.pembayaran_lainnya.forEach(function (item) {
                                table.row.add([
                                    index++,
                                    item.nama_pembayaran,
                                    item.nominal,
                                    '0',
                                    item.nominal,
                                    `<button class="btn btn-success btn-sm pilihBtn" data-id="${item.item_id}" data-type="${item.type}">+ Pilih</button>`
                                ]).draw();
                            });

                        }
                    });
                }
            });

            // Show the first modal when the second modal is closed
            $('#jenisPembayaranModal').on('hidden.bs.modal', function () {
                $('#addDataModal').modal('show');
            });

            // Ensure the second modal is opened without closing the first modal
            $('#jenisPembayaranModal').on('show.bs.modal', function () {
                $('#addDataModal').modal('hide');
            });


            // Handle "Pilih" button click in the jenisPembayaranTable
            $('#jenisPembayaranTable').on('click', '.pilihBtn', function () {
                var row = $(this).closest('tr');
                var jenisPembayaran = row.find('td:nth-child(2)').text();
                var tagihan = row.find('td:nth-child(3)').text();
                var itemId = $(this).data('id'); // Ambil ID dari item
                var type = $(this).data('type');

                // Check if already selected to prevent duplicates
                if ($('#selectedPembayaran').find(`[data-jenis="${jenisPembayaran}"]`).length === 0) {
                    $('#selectedPembayaran').append(`
                        <button class="btn btn-outline-success me-2" data-jenis="${jenisPembayaran}">
                            ${jenisPembayaran} (${type}) <span class="removeItem">&times;</span>
                        </button>
                    `);

                    // Append to row-item-bayar in the main table
                    $('#tabel-list-item-pengeluaran tbody').append(`
                        <tr class="row-item-bayar">
                            <td>${$('#tabel-list-item-pengeluaran tbody tr').length + 1}</td>
                            <td>
                                <label for="jenis" class="form-label" name="item_id">${jenisPembayaran}</label>
                                <input type="hidden" name="item_type[]" value="${type}">
                                <input type="hidden" name="item_id[]" value="${itemId}">
                            </td>
                            <td><label for="tagihan" class="form-label">${tagihan}</label></td>
                            <td><input type="number" class="form-control jumlah-bayar" name="jumlah_bayar[]" required></td>
                        </tr>
                    `);

                    // Recalculate total bayar
                    calculateTotal();
                } else {
                    alert('Item ini sudah dipilih.');
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

                // Recalculate total bayar
                calculateTotal();
            });

            // Event listener to calculate total bayar
            $(document).on('input', '.jumlah-bayar', function () {
                calculateTotal();
            });

            // Function to calculate total bayar
            function calculateTotal() {
                var totalBayar = 0;
                $('.jumlah-bayar').each(function () {
                    var value = parseFloat($(this).val()) || 0;
                    totalBayar += value;
                });
                $('#total-bayar').text(totalBayar.toFixed(2)); // Format to 2 decimal places
            }
        });
    </script>

</body>

</html>