<?php
$db = Database::getInstance()->getConnection();


try {
    $stmt = $db->prepare("SELECT nama_tarif AS nama_pembayaran, nominal, tahun_ajaran_id, 'SPP' AS jenis_pembayaran
        FROM tarif_spp

        UNION

        SELECT nama_pembayaran, nominal, tahun_ajaran_id, 'Pembayaran Lainnya' AS jenis_pembayaran
        FROM siswa_pembayaran_lainnya
    ");
    $stmt->execute();
    $combinedData = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
$no = 1;
foreach ($combinedData as $row) { // Mengganti $tarifData dengan $combinedData
    $dibayar = 0;
    $kurang = $row['nominal'] - $dibayar;
    ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= htmlspecialchars($row['nama_pembayaran']); ?></td> <!-- Mengganti nama_tarif dengan nama_pembayaran -->
        <td><?= number_format($row['nominal'], 2, ',', '.'); ?></td>
        <td><?= number_format($dibayar, 2, ',', '.'); ?></td>
        <td><?= number_format($kurang, 2, ',', '.'); ?></td>
        <td><button class="btn btn-success btn-sm pilihBtn">+ Pilih</button></td>
    </tr>
<?php } ?>


<!-- // var_dump($results);

// echo '
<pre>';
// print_r($combinedData);
// // print_r($results['pengeluaran_id']);
// echo '</pre>'; -->