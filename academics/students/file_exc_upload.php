<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$db = Database::getInstance()->getConnection();

if (empty($_FILES['file_excel']['name'])) {
    echo "<script>
        alert('Oops! Please select a file.'); 
        document.location='/siswa/tambah-siswa';
    </script>";
    exit;
}

$type = explode(".", $_FILES['file_excel']['name']);
$ext = strtolower(end($type));

if ($ext != 'xls' && $ext != 'xlsx') {
    echo "<script>
        alert('Please upload only Excel XLS or XLSX files.'); 
        document.location='/siswa/tambah-siswa';
        </script>";
    exit;
}

$target = basename($_FILES['file_excel']['name']);
if (!move_uploaded_file($_FILES['file_excel']['tmp_name'], $target)) {
    echo "<script>
        alert('Failed to upload the file.'); 
        document.location='/siswa/tambah-siswa';
    </script>";
    exit;
}

try {
    $spreadsheet = IOFactory::load($target);
    $sheet = $spreadsheet->getActiveSheet();
    $baris = $sheet->getHighestRow();

    for ($i = 2; $i <= $baris; $i++) {
        $nis = $sheet->getCell('B' . $i)->getValue();
        $nisn = $sheet->getCell('C' . $i)->getValue();
        $nama_lengkap = $sheet->getCell('D' . $i)->getValue();
        $jenis_kelamin = $sheet->getCell('E' . $i)->getValue();
        $tanggal_lahir = $sheet->getCell('F' . $i)->getFormattedValue();
        $tempat_lahir = $sheet->getCell('G' . $i)->getValue();
        $alamat = $sheet->getCell('H' . $i)->getValue();
        $kelas_id = $sheet->getCell('I' . $i)->getValue();
        $status = $sheet->getCell('J' . $i)->getValue();

        // Validasi data sebelum insert
        if (empty($nis) || empty($nisn) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($tanggal_lahir) || empty($kelas_id)) {
            throw new Exception("Incomplete data on row $i.");
        }

        $stmt = $db->prepare("INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $nis);
        $stmt->bindValue(2, $nisn);
        $stmt->bindValue(3, $nama_lengkap);
        $stmt->bindValue(4, $jenis_kelamin);
        $stmt->bindValue(5, $tanggal_lahir);
        $stmt->bindValue(6, $tempat_lahir);
        $stmt->bindValue(7, $alamat);
        $stmt->bindValue(8, $kelas_id);
        $stmt->bindValue(9, $status ?: 'Aktif');

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert row $i: " . implode(", ", $stmt->errorInfo()));
        }
    }

    echo "<script>
        alert('File imported successfully.'); 
        document.location='/siswa';
    </script>";
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "<script>
        alert('Error occurred: " . htmlspecialchars($e->getMessage()) . "'); 
        document.location='/siswa/tambah-siswa';
    </script>";
} finally {
    if (file_exists($target)) {
        unlink($target); // Hapus file setelah semua proses selesai
    }
}