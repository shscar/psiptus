<?php
$db = Database::getInstance()->getConnection();


// Ambil data tagihan dan pembayaran siswa
$stmt = $db->prepare("
    SELECT spl.nama_pembayaran, spl.nominal AS nilai_tagihan, 
        IFNULL(SUM(ps.jumlah_bayar), 0) AS dibayar, 
        (spl.nominal - IFNULL(SUM(ps.jumlah_bayar), 0)) AS kurang
    FROM siswa_pembayaran_lainnya spl
    LEFT JOIN pembayaran_spp ps ON spl.id = ps.tarif_spp_id
    WHERE spl.status_aktif = 1
    GROUP BY spl.id, spl.nama_pembayaran, spl.nominal
");
$stmt->execute();
$tagihanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "
    SELECT 'SPP' AS jenis_pembayaran, nama_tarif AS nama, nominal, 0 AS dibayar, nominal AS kurang
    FROM tarif_spp
    UNION ALL
    SELECT 'Pembayaran Lainnya' AS jenis_pembayaran, nama_pembayaran AS nama, nominal, 0 AS dibayar, nominal AS kurang
    FROM siswa_pembayaran_lainnya
    WHERE status_aktif = 1
    ORDER BY jenis_pembayaran, nama;
";
$stmt = $db->prepare($query);
$stmt->execute();
$tagihanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($results);

echo '<pre>';
print_r($results);
// print_r($results['pengeluaran_id']);
echo '</pre>';


$no = 1;
foreach ($tagihanData as $row) {
    $kurang = $row['nominal'] - $row['dibayar'];
    echo "
    <tr>
        <td>{$no}</td>
        <td>{$row['jenis_pembayaran']} - {$row['nama']}</td>
        <td>" . number_format($row['nominal'], 2, ',', '.') . "</td>
        <td>" . number_format($row['dibayar'], 2, ',', '.') . "</td>
        <td>" . number_format($kurang, 2, ',', '.') . "</td>
        <td><button class='btn btn-success btn-sm pilihBtn' data-nama_pembayaran='{$row['nama']}'>+ Pilih</button></td>
    </tr>";
    $no++;
}