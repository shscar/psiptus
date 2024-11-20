<?php
// Memulai output buffering
// ob_start();

// include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarif_spp_id = $_POST['tarif_spp_id'];
    $kelas_id = $_POST['kelas_id'];

    if ($tarif_spp_id && $kelas_id) {
        try {
            $sqlKelas = "INSERT INTO tarif_spp_kelas (tarif_spp_id, kelas_id) VALUES (:tarif_spp_id, :kelas_id)";
            $stmtKelas = $db->prepare($sqlKelas);
            $stmtKelas->execute([
                'tarif_spp_id' => $tarif_spp_id,
                'kelas_id' => $kelas_id
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error saving data: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data incomplete']);
    }
}