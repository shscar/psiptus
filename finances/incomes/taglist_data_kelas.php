<?php
// Memulai buffering
ob_start();
// include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $kelas_id = $_GET['kelas_id'] ?? null;

    if (!$kelas_id) {
        echo json_encode(['status' => 'error', 'message' => 'Kelas tidak dipilih']);
        exit;
    }

    try {
        $data_by_kelas = getDataByKelas($kelas_id);
        // echo json_encode(['status' => 'success', 'data' => $data_by_kelas]);
        // echo '<pre>';
        // print_r($data_by_kelas);
        // echo '</pre>';
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getDataByKelas($kelas_id)
{
    global $db;
    $data_by_kelas = [];

    // Query gabungan untuk SPP, pembayaran lainnya, dan siswa
    $stmt = $db->prepare("    
        SELECT 
            k.nama_kelas,
            k.jurusan,
            ts.id AS tarif_spp_id,
            ts.nama_tarif,
            ts.nominal AS nominal_spp,
            spl.id AS pembayaran_lainnya_id,
            spl.nama_pembayaran,
            spl.nominal AS nominal_lainnya,
            s.nama_lengkap,
            COALESCE(SUM(drt.jumlah_bayar), 0) AS total_dibayar_spp,
            COALESCE(SUM(drl.jumlah_bayar), 0) AS total_dibayar_lainnya
        FROM kelas k
        LEFT JOIN tarif_spp_kelas tsk ON k.id = tsk.kelas_id
        LEFT JOIN tarif_spp ts ON tsk.tarif_spp_id = ts.id
        LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp drt ON ts.id = drt.tarif_spp_id
        LEFT JOIN siswa_pembayaran_lainnya_kelas plk ON k.id = plk.kelas_id
        LEFT JOIN siswa_pembayaran_lainnya spl ON plk.siswa_pembayaran_lainnya_id = spl.id
        LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain drl ON spl.id = drl.pembayaran_lainnya_id
        LEFT JOIN siswa s ON s.kelas_id = k.id
        WHERE k.id = :kelas_id
        GROUP BY k.nama_kelas, k.jurusan, ts.id, spl.id, s.nama_lengkap
        ORDER BY k.nama_kelas, ts.nama_tarif, spl.nama_pembayaran, s.nama_lengkap
    ");
    $stmt->execute([':kelas_id' => $kelas_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $kelas = $row['nama_kelas'];
        if (!isset($data_by_kelas[$kelas])) {
            $data_by_kelas[$kelas] = ['spp' => [], 'lainnya' => [], 'siswa' => []];
        }

        if (!empty($row['tarif_spp_id']) && !isset($data_by_kelas[$kelas]['spp'][$row['tarif_spp_id']])) {
            $data_by_kelas[$kelas]['spp'][$row['tarif_spp_id']] = [
                'nama_tarif' => $row['nama_tarif'],
                'kurang_bayar' => $row['nominal_spp'] - $row['total_dibayar_spp'],
            ];
        }

        if (!empty($row['pembayaran_lainnya_id']) && !isset($data_by_kelas[$kelas]['lainnya'][$row['pembayaran_lainnya_id']])) {
            $data_by_kelas[$kelas]['lainnya'][$row['pembayaran_lainnya_id']] = [
                'nama_pembayaran' => $row['nama_pembayaran'],
                'kurang_bayar' => $row['nominal_lainnya'] - $row['total_dibayar_lainnya'],
            ];
        }

        if (!empty($row['nama_lengkap']) && !in_array($row['nama_lengkap'], $data_by_kelas[$kelas]['siswa'])) {
            $data_by_kelas[$kelas]['siswa'][] = $row['nama_lengkap'];
        }
    }

    // Ubah array tarif SPP dan pembayaran lainnya dari associative ke indexed array
    foreach ($data_by_kelas as &$kelasData) {
        $kelasData['spp'] = array_values($kelasData['spp']);
        $kelasData['lainnya'] = array_values($kelasData['lainnya']);
    }

    return $data_by_kelas;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tagihan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Tambahkan jQuery di sini -->
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Data Tagihan Siswa</h3>
            <button id="exportBtn" class="btn btn-success">Export to Excel</button>
        </div>
        <table id="dataTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>A. Kelas</th>
                    <th>B. Nama</th>
                    <th>C. Tagihan SPP</th>
                    <th>D. Pembayaran Lainnya</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data_by_kelas)): ?>
                    <?php foreach ($data_by_kelas as $kelas => $tagihan): ?>
                        <?php foreach ($tagihan['siswa'] as $siswa): ?>
                            <tr>
                                <td><?= htmlspecialchars($kelas) ?></td>
                                <td><?= htmlspecialchars($siswa) ?></td>
                                <td>
                                    <?php if (!empty($tagihan['spp'])): ?>
                                        <div style="display: flex; flex-wrap: wrap;">
                                            <?php foreach ($tagihan['spp'] as $item): ?>
                                                <div style="margin-right: 20px;">
                                                    <strong><?= htmlspecialchars($item['nama_tarif']) ?></strong><br>
                                                    Rp. <?= number_format($item['kurang_bayar'], 0, ',', '.') ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        Tidak ada tagihan SPP
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tagihan['lainnya'])): ?>
                                        <div style="display: flex; flex-wrap: wrap;">
                                            <?php foreach ($tagihan['lainnya'] as $item): ?>
                                                <div style="margin-right: 20px;">
                                                    <strong><?= htmlspecialchars($item['nama_pembayaran']) ?></strong><br>
                                                    Rp. <?= number_format($item['kurang_bayar'], 0, ',', '.') ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        Tidak ada pembayaran lainnya
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data untuk kelas ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin/libs/js-xlsx/xlsx.core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin/libs/FileSaver/FileSaver.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin/tableExport.min.js"></script>
    <script>
        document.getElementById('exportBtn').addEventListener('click', function () {
            TableExport(document.getElementById('dataTable'), {
                formats: ['xlsx'],
                filename: 'Data_Tagihan_Siswa',
                exportButtons: false
            }).exportToFile('Data_Tagihan_Siswa', 'xlsx');
        });
    </script>

</body>

</html>