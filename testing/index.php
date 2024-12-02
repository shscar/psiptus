<?php
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">
            Add Data
        </button>

        <!-- Add Data Modal -->
        <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDataLabel">Tambah Data Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDataForm">
                            <div class="mb-3">
                                <label for="student_name" class="form-label">Nama Siswa</label>
                                <select id="student_name" class="form-control select2" name="student_name">
                                    <option value="">Pilih Nama Siswa</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['nis']; ?>"
                                            data-kelas-id=" <?= $student['kelas_id']; ?>"> <?= $student['nama_lengkap']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#jenisPembayaranModal">
                                Pilih Jenis Pembayaran
                            </button>
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

    <script>
        $(document).ready(function () {
            // Initialize Select2 with dropdownParent option
            $('#student_name').select2({
                placeholder: 'Pilih Nama Siswa',
                dropdownParent: $('#addDataModal') // Menetapkan parent dropdown ke modal
            });

            // Initialize DataTable
            $('#jenisPembayaranTable').DataTable();

            // Fetch and populate data in the second modal
            $('#student_name').on('change', function () {
                const nis = $(this).val();

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

            // Handle row selection in the second modal
            $(document).on('click', '.pilihBtn', function () {
                const row = $(this).closest('tr');
                const jenis = row.find('td:eq(1)').text();
                alert(`Jenis Pembayaran "${jenis}" telah dipilih.`);
            });
        });
    </script>
</body>

</html>