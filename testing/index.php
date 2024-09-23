<?php

require __DIR__ . ',,/config/connection.php';


$db = Database::getInstance()->getConnection();


$stmt = $db->prepare("SELECT * FROM siswa_pembayaran_lainnya ORDER BY id DESC");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($results);

echo '<pre>';
print_r($results);
echo '</pre>';