<?php
require 'vendor/autoload.php'; // Autoload PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Koneksi ke database
$db = Database::getInstance()->getConnection();

// Ambil data siswa
$query = $db->query("SELECT nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status FROM siswa");
$siswaData = $query->fetchAll(PDO::FETCH_ASSOC);

// Buat file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = ['NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Alamat', 'Kelas ID', 'Status'];
$sheet->fromArray($headers, null, 'A1');

// Tambahkan data ke Excel
$row = 2; // Dimulai dari baris kedua karena baris pertama untuk header
foreach ($siswaData as $data) {
    $sheet->setCellValue("A{$row}", $data['nis']);
    $sheet->setCellValue("B{$row}", $data['nisn']);
    $sheet->setCellValue("C{$row}", $data['nama_lengkap']);
    $sheet->setCellValue("D{$row}", $data['jenis_kelamin']);
    $sheet->setCellValue("E{$row}", $data['tanggal_lahir']);
    $sheet->setCellValue("F{$row}", $data['tempat_lahir']);
    $sheet->setCellValue("G{$row}", $data['alamat']);
    $sheet->setCellValue("H{$row}", $data['kelas_id']);
    $sheet->setCellValue("I{$row}", $data['status']);
    $row++;
}

// Set header untuk download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="data_siswa.xlsx"');
header('Cache-Control: max-age=0');

// Simpan file Excel ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>