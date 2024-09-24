<?php
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

var_dump($results['id']);

// echo '<pre>';
// print_r($results['pengeluaran_id']);
// echo '</pre>';