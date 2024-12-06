<?php

$db = Database::getInstance()->getConnection();

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $id = $_POST['id'];

//     // Ambil informasi kelas dari siswa
//     $kelasQuery = $db->prepare("SELECT kelas_id FROM siswa WHERE id = :id");
//     $kelasQuery->execute(['id' => $id]);
//     $kelas = $kelasQuery->fetch(PDO::FETCH_ASSOC);

//     if ($kelas) {
//         $kelas_id = $kelas['kelas_id'];

//         // Ambil tarif SPP terkait kelas
//         $tarifSPPQuery = $db->prepare("
//             SELECT ts.id AS item_id, ts.nama_tarif, ts.nominal
//             FROM tarif_spp ts
//             JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id
//             WHERE tsk.kelas_id = :kelas_id AND ts.status_aktif = 1
//             ");
//         $tarifSPPQuery->execute(['kelas_id' => $kelas_id]);
//         $tarifSPP = $tarifSPPQuery->fetchAll(PDO::FETCH_ASSOC);

//         // Ambil pembayaran lainnya terkait kelas
//         $pembayaranLainnyaQuery = $db->prepare("
//             SELECT spl.id AS item_id, spl.nama_pembayaran, spl.nominal
//             FROM siswa_pembayaran_lainnya spl
//             JOIN siswa_pembayaran_lainnya_kelas splk ON spl.id = splk.siswa_pembayaran_lainnya_id
//             WHERE splk.kelas_id = :kelas_id AND spl.status_aktif = 1
//             ");
//         $pembayaranLainnyaQuery->execute(['kelas_id' => $kelas_id]);
//         $pembayaranLainnya = $pembayaranLainnyaQuery->fetchAll(PDO::FETCH_ASSOC);


//         echo json_encode([
//             'tarif_spp' => array_map(function ($item) {
//                 return array_merge($item, ['type' => 'tarif_spp']);
//             }, $tarifSPP),
//             'pembayaran_lainnya' => array_map(function ($item) {
//                 return array_merge($item, ['type' => 'pembayaran_lainnya']);
//             }, $pembayaranLainnya),
//         ]);
//         // echo json_encode(array_merge($tarifSPP, $pembayaranLainnya));

//         exit;
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Ambil informasi kelas dari siswa
    $kelasQuery = $db->prepare("SELECT kelas_id FROM siswa WHERE id = :id");
    $kelasQuery->execute(['id' => $id]);
    $kelas = $kelasQuery->fetch(PDO::FETCH_ASSOC);

    if ($kelas) {
        $kelas_id = $kelas['kelas_id'];

        // Ambil tarif SPP terkait kelas
        $tarifSPPQuery = $db->prepare("
            SELECT 
                ts.id AS item_id, 
                ts.nama_tarif, 
                ts.nominal,
                COALESCE(SUM(drt.jumlah_bayar), 0) AS total_bayar,
                (ts.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar
            FROM tarif_spp ts
            JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id
            LEFT JOIN detail_riwayat_transaksi_siswa_tarifspp drt 
                ON ts.id = drt.tarif_spp_id
            WHERE tsk.kelas_id = :kelas_id AND ts.status_aktif = 1
            GROUP BY ts.id
        ");
        $tarifSPPQuery->execute(['kelas_id' => $kelas_id]);
        $tarifSPP = $tarifSPPQuery->fetchAll(PDO::FETCH_ASSOC);

        // Ambil pembayaran lainnya terkait kelas
        $pembayaranLainnyaQuery = $db->prepare("SELECT 
                spl.id AS item_id, 
                spl.nama_pembayaran, 
                spl.nominal,
                COALESCE(SUM(drt.jumlah_bayar), 0) AS total_bayar,
                (spl.nominal - COALESCE(SUM(drt.jumlah_bayar), 0)) AS kurang_bayar
            FROM siswa_pembayaran_lainnya AS spl
            JOIN siswa_pembayaran_lainnya_kelas AS splk ON spl.id = splk.siswa_pembayaran_lainnya_id
            LEFT JOIN detail_riwayat_transaksi_siswa_pembayaranlain AS drt 
                ON spl.id = drt.siswa_pembayaran_lainnya_id
            WHERE splk.kelas_id = :kelas_id AND spl.status_aktif = 1
            GROUP BY spl.id
        ");
        $pembayaranLainnyaQuery->execute(['kelas_id' => $kelas_id]);
        $pembayaranLainnya = $pembayaranLainnyaQuery->fetchAll(PDO::FETCH_ASSOC);

        // Tambahkan jumlah_bayar ke pembayaran lainnya
        // foreach ($pembayaranLainnya as &$pembayaran) {
        //     $jumlahBayarQuery = $db->prepare("
        //         SELECT COALESCE(SUM(jumlah_bayar), 0) AS jumlah_bayar
        //         FROM detail_riwayat_transaksi_siswa_pembayaranlain
        //         WHERE siswa_id = :siswa_id AND pembayaran_lainnya_id = :pembayaran_lainnya_id
        //     ");
        //     $jumlahBayarQuery->execute(['siswa_id' => $id, 'pembayaran_lainnya_id' => $pembayaran['item_id']]);
        //     $jumlahBayar = $jumlahBayarQuery->fetch(PDO::FETCH_ASSOC);
        //     $pembayaran['jumlah_bayar'] = $jumlahBayar['jumlah_bayar'];
        // }

        // Gabungkan data dan kirim sebagai JSON
        echo json_encode([
            'tarif_spp' => array_map(function ($item) {
                return array_merge($item, ['type' => 'tarif_spp']);
            }, $tarifSPP),
            'pembayaran_lainnya' => array_map(function ($item) {
                return array_merge($item, ['type' => 'pembayaran_lainnya']);
            }, $pembayaranLainnya),
        ]);
        exit;
    }
}