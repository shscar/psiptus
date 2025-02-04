<?php
// Memulai buffering
ob_start();
// include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Inisialisasi variabel
$kelas_id = $_GET['kelas_id'] ?? 3; // Default kelas_id jika tidak diberikan
$data_by_kelas = []; // Variabel untuk menyimpan hasil akhir

try {
    // Query tarif SPP
    $stmt_spp = $db->prepare("
        SELECT 
            k.nama_kelas, 
            k.jurusan, 
            ts.id AS item_id, 
            ts.nama_tarif, 
            ts.nominal,
            COALESCE(SUM(drt.jumlah_bayar), 0) AS total_dibayar,
            (ts.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar
        FROM tarif_spp ts
        JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id
        JOIN kelas k ON k.id = tsk.kelas_id
        LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp drt 
            ON ts.id = drt.tarif_spp_id
        WHERE tsk.kelas_id = :kelas_id
        GROUP BY k.nama_kelas, k.jurusan, ts.id, ts.nama_tarif, ts.nominal
    ");
    $stmt_spp->execute([':kelas_id' => $kelas_id]);
    $results_spp = $stmt_spp->fetchAll(PDO::FETCH_ASSOC);

    // Query pembayaran lainnya
    $stmt_lainnya = $db->prepare("
        SELECT 
            k.nama_kelas,
            k.jurusan,
            spl.id AS item_id,
            spl.nama_pembayaran,
            spl.nominal,
            COALESCE(SUM(drl.jumlah_bayar), 0) AS total_dibayar,
            (spl.nominal - COALESCE(SUM(drl.jumlah_bayar), 0)) AS kurang_bayar
        FROM siswa_pembayaran_lainnya spl
        JOIN siswa_pembayaran_lainnya_kelas plk ON spl.id = plk.siswa_pembayaran_lainnya_id
        JOIN kelas k ON k.id = plk.kelas_id
        LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain drl
            ON spl.id = drl.pembayaran_lainnya_id
        WHERE plk.kelas_id = :kelas_id
        GROUP BY k.nama_kelas, k.jurusan, spl.id, spl.nama_pembayaran, spl.nominal
    ");
    $stmt_lainnya->execute([':kelas_id' => $kelas_id]);
    $results_lainnya = $stmt_lainnya->fetchAll(PDO::FETCH_ASSOC);

    // Mengelompokkan data berdasarkan kelas
    foreach ($results_spp as $row) {
        $data_by_kelas[$row['nama_kelas']]['spp'][] = $row;
    }
    foreach ($results_lainnya as $row) {
        $data_by_kelas[$row['nama_kelas']]['lainnya'][] = $row;
    }
} catch (PDOException $e) {
    // Tampilkan error untuk debugging
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tagihan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4">Data Tagihan Siswa</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>A. Kelas</th>
                    <th>B. Nama</th>
                    <th>C. Tagihan Sebelumnya</th>
                    <th>D. Tagihan SPP</th>
                    <th>E. Pembayaran Lainnya</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Contoh data siswa
                $nama_siswa = 'Ahmad Hidayat';
                $tagihan_sebelumnya = 100000;

                if (!empty($data_by_kelas)) {
                    foreach ($data_by_kelas as $kelas => $tagihan) {
                        echo "<tr>";
                        echo "<td>{$kelas} - {$tagihan['spp'][0]['jurusan']}</td>";
                        echo "<td>{$nama_siswa}</td>";
                        echo "<td>Rp. " . number_format($tagihan_sebelumnya, 0, ',', '.') . "</td>";

                        // Tagihan SPP
                        echo "<td>";
                        if (!empty($tagihan['spp'])) {
                            echo "<ul>";
                            foreach ($tagihan['spp'] as $item) {
                                echo "<li>{$item['nama_tarif']} - Rp. " . number_format($item['kurang_bayar'], 0, ',', '.') . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "Tidak ada tagihan SPP";
                        }
                        echo "</td>";

                        // Pembayaran Lainnya
                        echo "<td>";
                        if (!empty($tagihan['lainnya'])) {
                            echo "<ul>";
                            foreach ($tagihan['lainnya'] as $item) {
                                echo "<li>{$item['nama_pembayaran']} - Rp. " . number_format($item['kurang_bayar'], 0, ',', '.') . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "Tidak ada pembayaran lainnya";
                        }
                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data untuk kelas ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>