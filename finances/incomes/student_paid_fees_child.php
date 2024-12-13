<?php

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Query gabungan untuk mendapatkan kelas siswa dan transaksi terkait
    $query = $db->prepare("
        SELECT 
            s.kelas_id, 
            rt.id AS riwayat_transaksi_id 
        FROM siswa s
        LEFT JOIN riwayat_transaksi_siswa rt ON s.id = rt.siswa_id
        WHERE s.id = :id
    ");
    $query->execute(['id' => $id]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $kelas_id = $result['kelas_id'];
        $riwayat_transaksi_id = $result['riwayat_transaksi_id'];

        if ($kelas_id) {
            // Query tarif SPP
            $tarifSPPQuery = $db->prepare("
                SELECT 
                    ts.id AS item_id, 
                    ts.nama_tarif, 
                    ts.nominal,
                    COALESCE(SUM(drt.jumlah_bayar), 0) AS total_dibayar,
                    (ts.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar
                FROM tarif_spp ts
                JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id
                LEFT JOIN riwayat_transaksi_siswa_detail_tarifspp drt 
                    ON ts.id = drt.tarif_spp_id AND drt.riwayat_transaksi_id IN (
                        SELECT id FROM riwayat_transaksi_siswa WHERE siswa_id = :siswa_id
                    )
                WHERE tsk.kelas_id = :kelas_id
                GROUP BY ts.id
            ");
            $tarifSPPQuery->execute([
                'siswa_id' => $id,
                // 'riwayat_transaksi_id' => $riwayat_transaksi_id,
                'kelas_id' => $kelas_id,
            ]);
            $tarifSPP = $tarifSPPQuery->fetchAll(PDO::FETCH_ASSOC);

            // Query pembayaran lainnya
            $pembayaranLainnyaQuery = $db->prepare("
                SELECT 
                    spl.id AS item_id, 
                    spl.nama_pembayaran, 
                    spl.nominal,
                    COALESCE(SUM(drl.jumlah_bayar), 0) AS total_dibayar,
                    (spl.nominal - COALESCE(SUM(drl.jumlah_bayar), 0)) AS kurang_bayar
                FROM siswa_pembayaran_lainnya spl
                JOIN siswa_pembayaran_lainnya_kelas splk ON spl.id = splk.siswa_pembayaran_lainnya_id
                LEFT JOIN riwayat_transaksi_siswa_detail_pembayaranlain drl 
                    ON spl.id = drl.pembayaran_lainnya_id AND drl.riwayat_transaksi_id IN (
                        SELECT id FROM riwayat_transaksi_siswa WHERE siswa_id = :siswa_id
                    )
                WHERE splk.kelas_id = :kelas_id
                GROUP BY spl.id
            ");
            $pembayaranLainnyaQuery->execute([
                'siswa_id' => $id,
                // 'riwayat_transaksi_id' => $riwayat_transaksi_id,
                'kelas_id' => $kelas_id,
            ]);
            $pembayaranLainnya = $pembayaranLainnyaQuery->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'tarif_spp' => array_map(function ($item) {
                    return array_merge($item, ['type' => 'tarif_spp']);
                }, $tarifSPP),
                'pembayaran_lainnya' => array_map(function ($item) {
                    return array_merge($item, ['type' => 'pembayaran_lainnya']);
                }, $pembayaranLainnya),
            ]);
        } else {
            echo json_encode(['error' => 'Kelas tidak ditemukan.']);
        }
    } else {
        echo json_encode(['error' => 'Data siswa tidak ditemukan.']);
    }
    exit;
}