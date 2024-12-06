<?php
$db = Database::getInstance()->getConnection();

// Query to fetch data
$query = "
    SELECT 
        rts.id AS riwayat_id,
        s.nis,
        s.nama_lengkap,
        k.nama_kelas,
        rts.tanggal_bayar,
        rts.jenis_bayar,
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
            'items' => []
        ];
    }

    // Append jenis pembayaran items
    if (!empty($row['tarif_spp'])) {
        $combinedResults[$riwayatId]['items'][] = $row['tarif_spp'];
    }
    if (!empty($row['pembayaran_lainnya'])) {
        $combinedResults[$riwayatId]['items'][] = $row['pembayaran_lainnya'];
    }
}

// Transform combined results into a numeric array
$combinedResults = array_values($combinedResults);

// Menampilkan hasil untuk debugging
echo '<pre>';
print_r($combinedResults);
echo '</pre>';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembayaran Siswa</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Data Pembayaran Siswa</h1>
        <table id="DataPembayaranSiswa" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Jenis Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($combinedResults as $row) {
                    $items = implode(', ', $row['items']);
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>{$row['nama_lengkap']}</td>";
                    echo "<td>{$row['nama_kelas']}</td>";
                    echo "<td>{$row['tanggal_bayar']}</td>";
                    echo "<td>{$items}</td>";
                    echo "<td>
                            <button class='btn btn-primary btn-sm'>Edit</button>
                            <button class='btn btn-danger btn-sm'>Hapus</button>
                          </td>";
                    echo "</tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#DataPembayaranSiswa').DataTable();
        });
    </script>
</body>

</html>