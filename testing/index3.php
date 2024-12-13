<?php
$db = Database::getInstance()->getConnection();

// Query untuk mengambil data dari relasi tabel
$stmt = $db->prepare("SELECT 
    pd.id AS pengeluaran_id,
    pd.tanggal_pengeluaran,
    pd.sumber_dana,
    pd.pihak_terlibat,
    pd.ket_pengeluaran,
    pd.jenis_bayar,
    pd.total,
    pdi.id AS item_id,
    pdi.use_kategori,
    pdi.nama_pengeluaran,
    pdi.item,
    pdi.satuan,
    pdi.harga,
    pdi.nominal,
    pdi.komite,
    pdi.bosda,
    pdi.jumlah,
    pdb.id AS bukti_id,
    pdb.file_path,
    dkp.judul AS kategori_judul
FROM pengeluaran_dana pd
LEFT JOIN pengeluaran_dana_item pdi ON pd.id = pdi.pengeluaran_dana_id
LEFT JOIN pengeluaran_dana_bukti pdb ON pd.id = pdb.pengeluaran_id
LEFT JOIN detail_kategori_pengeluaran dkp ON pdi.nama_pengeluaran = dkp.id AND pdi.use_kategori = true
ORDER BY pd.tanggal_pengeluaran DESC");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengelompokkan data berdasarkan `pengeluaran_id`
$combinedResults = [];
foreach ($results as $row) {
    $pengeluaranId = $row['pengeluaran_id'];

    // Jika pengeluaran_id belum ada, tambahkan ke dalam hasil
    if (!isset($combinedResults[$pengeluaranId])) {
        $combinedResults[$pengeluaranId] = [
            'pengeluaran_id' => $row['pengeluaran_id'],
            'tanggal_pengeluaran' => $row['tanggal_pengeluaran'],
            'sumber_dana' => $row['sumber_dana'],
            'pihak_terlibat' => $row['pihak_terlibat'],
            'ket_pengeluaran' => $row['ket_pengeluaran'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total' => $row['total'],
            'items' => [],
            'bukti_files' => []
        ];
    }

    // Tambahkan item pengeluaran jika ada
    if ($row['item_id']) {
        $namaPengeluaran = $row['use_kategori'] && is_numeric($row['nama_pengeluaran'])
            ? $row['kategori_judul']
            : $row['nama_pengeluaran'];

        $combinedResults[$pengeluaranId]['items'][] = [
            'id' => $row['item_id'],
            'nama_pengeluaran' => $namaPengeluaran,
            'item' => $row['item'],
            'satuan' => $row['satuan'],
            'harga' => $row['harga'],
            'nominal' => $row['nominal'],
            'komite' => $row['komite'],
            'bosda' => $row['bosda'],
            'jumlah' => $row['jumlah']
        ];
    }

    // Tambahkan bukti pengeluaran jika ada
    if ($row['bukti_id']) {
        $combinedResults[$pengeluaranId]['bukti_files'][] = [
            'id' => $row['bukti_id'],
            'file_path' => $row['file_path']
        ];
    }
}

// Menampilkan hasil untuk debug atau penggunaan lebih lanjut
echo '<pre>' . print_r($combinedResults, true) . '</pre>';