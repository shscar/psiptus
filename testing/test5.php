<?php

$db = Database::getInstance()->getConnection();

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
            SELECT ts.id AS item_id, ts.nama_tarif, ts.nominal
            FROM tarif_spp ts
            JOIN tarif_spp_kelas tsk ON ts.id = tsk.tarif_spp_id
            WHERE tsk.kelas_id = :kelas_id AND ts.status_aktif = 1
            ");
        $tarifSPPQuery->execute(['kelas_id' => $kelas_id]);
        $tarifSPP = $tarifSPPQuery->fetchAll(PDO::FETCH_ASSOC);

        // Ambil pembayaran lainnya terkait kelas
        $pembayaranLainnyaQuery = $db->prepare("
            SELECT spl.id AS item_id, spl.nama_pembayaran, spl.nominal
            FROM siswa_pembayaran_lainnya spl
            JOIN siswa_pembayaran_lainnya_kelas splk ON spl.id = splk.siswa_pembayaran_lainnya_id
            WHERE splk.kelas_id = :kelas_id AND spl.status_aktif = 1
            ");
        $pembayaranLainnyaQuery->execute(['kelas_id' => $kelas_id]);
        $pembayaranLainnya = $pembayaranLainnyaQuery->fetchAll(PDO::FETCH_ASSOC);


        echo json_encode([
            'tarif_spp' => array_map(function ($item) {
                return array_merge($item, ['type' => 'tarif_spp']);
            }, $tarifSPP),
            'pembayaran_lainnya' => array_map(function ($item) {
                return array_merge($item, ['type' => 'pembayaran_lainnya']);
            }, $pembayaranLainnya),
        ]);
        // echo json_encode(array_merge($tarifSPP, $pembayaranLainnya));

        exit;
    }
}