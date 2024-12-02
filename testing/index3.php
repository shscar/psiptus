<?php
$db = Database::getInstance()->getConnection();

// Ambil data siswa dari database untuk digunakan di Select2
$stmt = $db->query("SELECT id, nis, nama_lengkap, kelas_id FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nis = $_GET['nis'];

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
        <form id="studentForm">
            <div class="mb-3 row">
                <label for="student_name" class="col-sm-3 col-form-label">Nama Siswa</label>
                <div class="col-sm-9">
                    <select id="student_name" class="form-control select2" name="student_name">
                        <option value="">Pilih Nama Siswa</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?= $student['nis']; ?>" data-kelas-id="<?= $student['kelas_id']; ?>">
                            <?= $student['nama_lengkap']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#jenisPembayaranModal">
                    Pilih Jenis Pembayaran
                </button>
            </div>
        </form>

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
    $(document).ready(function() {
        $('#student_name').select2({
            placeholder: 'Pilih Nama Siswa'
        });

        $('#jenisPembayaranTable').DataTable();

        $('#student_name').on('change', function() {
            const nis = $(this).val();

            if (nis) {
                $.ajax({
                    url: '',
                    type: 'GET',
                    data: {
                        nis: nis
                    },
                    dataType: 'json',
                    success: function(response) {
                        const table = $('#jenisPembayaranTable').DataTable();
                        table.clear();

                        let index = 1;
                        response.tarif_spp.forEach(function(item) {
                            table.row.add([
                                index++,
                                item.nama_tarif,
                                item.nominal,
                                '0',
                                item.nominal,
                                '<button class="btn btn-success btn-sm pilihBtn">+ Pilih</button>'
                            ]).draw();
                        });

                        response.pembayaran_lainnya.forEach(function(item) {
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

        $(document).on('click', '.pilihBtn', function() {
            const row = $(this).closest('tr');
            const jenis = row.find('td:eq(1)').text();
            alert(`Jenis Pembayaran "${jenis}" telah dipilih.`);
        });
    });
    </script>
</body>

</html>