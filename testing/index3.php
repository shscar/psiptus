<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT id, nis, nama_lengkap, kelas_id FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT 
        rts.id AS riwayat_id,
        rtt.id AS detail_tarifspp_id,
        rtp.id AS detail_pembayaranlain_id,
        s.nis,
        s.nama_lengkap,
        k.nama_kelas,
        rts.tanggal_bayar,
        rts.no_invoice,
        rts.jenis_bayar,
        rts.total_bayar,
        ts.nama_tarif AS tarif_spp,
        rtt.tarif_spp_id,
        rtt.jumlah_bayar AS jmlb_tfs,
        spl.nama_pembayaran AS pembayaran_lainnya,
        rtp.pembayaran_lainnya_id,
        rtp.jumlah_bayar AS jmlb_pyl,
        ts.nominal AS nominal_spp,
        spl.nominal AS nominal_lainnya,
        (ts.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar_spp,
        (spl.nominal - COALESCE(SUM(drl.jumlah_bayar), 0)) AS kurang_bayar_lainnya
    FROM riwayat_transaksi_siswa rts
    LEFT JOIN siswa s ON rts.siswa_id = s.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp rtt ON rts.id = rtt.riwayat_transaksi_id
    LEFT JOIN tarif_spp ts ON rtt.tarif_spp_id = ts.id
    LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain rtp ON rts.id = rtp.riwayat_transaksi_id
    LEFT JOIN siswa_pembayaran_lainnya spl ON rtp.pembayaran_lainnya_id = spl.id
    LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp drt ON ts.id = drt.tarif_spp_id AND rts.id = drt.riwayat_transaksi_id
    LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain drl ON spl.id = drl.pembayaran_lainnya_id AND rts.id = drl.riwayat_transaksi_id
    GROUP BY rts.id, rtt.id, rtt.tarif_spp_id, rtp.id, rtp.pembayaran_lainnya_id
    ORDER BY rts.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Mengelompokkan data berdasarkan `riwayat_id`
$combinedResults = [];
foreach ($results as $row) {
    $riwayatId = $row['riwayat_id'];
    if (!isset($combinedResults[$riwayatId])) {
        $combinedResults[$riwayatId] = [
            'riwayat_id' => $row['riwayat_id'],
            'nis' => $row['nis'],
            'nama_lengkap' => $row['nama_lengkap'],
            'nama_kelas' => $row['nama_kelas'],
            'no_invoice' => $row['no_invoice'],
            'tanggal_bayar' => $row['tanggal_bayar'],
            'jenis_bayar' => $row['jenis_bayar'],
            'total_bayar' => $row['total_bayar'],
            'detail_tarifspp_ids' => [],
            'detail_pembayaranlain_ids' => [],
            'items' => [],
        ];
    }
    if ($row['detail_tarifspp_id']) {
        $combinedResults[$riwayatId]['items'][] = [
            'id' => $row['detail_tarifspp_id'],
            'jenis_bayar' => 'TS: ' . $row['tarif_spp'],
            'jumlah_bayar' => 'Rp. ' . $row['jmlb_tfs'],
            'kurang_bayar' => 'Rp. ' . $row['kurang_bayar_spp'],
        ];
    }
    if ($row['detail_pembayaranlain_id']) {
        $combinedResults[$riwayatId]['items'][] = [
            'id' => $row['detail_pembayaranlain_id'],
            'jenis_bayar' => 'PL: ' . $row['pembayaran_lainnya'],
            'jumlah_bayar' => 'Rp. ' . $row['jmlb_pyl'],
            'kurang_bayar' => 'Rp. ' . $row['kurang_bayar_lainnya'],
        ];
    }
}

echo '<pre>';
print_r($combinedResults);
echo '</pre>';