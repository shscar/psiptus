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
$headers = ['No', 'NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Alamat', 'Kelas ID', 'Status'];
$sheet->fromArray($headers, null, 'A1');

// Tambahkan data ke Excel
$row = 2; // Dimulai dari baris kedua karena baris pertama untuk header
$no = 1; // Inisialisasi nomor urut
foreach ($siswaData as $data) {
    $sheet->setCellValue("A{$row}", $no++);
    $sheet->setCellValue("B{$row}", $data['nis']);
    $sheet->setCellValue("C{$row}", $data['nisn']);
    $sheet->setCellValue("D{$row}", $data['nama_lengkap']);
    $sheet->setCellValue("E{$row}", $data['jenis_kelamin']);
    $sheet->setCellValue("F{$row}", $data['tanggal_lahir']);
    $sheet->setCellValue("G{$row}", $data['tempat_lahir']);
    $sheet->setCellValue("H{$row}", $data['alamat']);
    $sheet->setCellValue("I{$row}", $data['kelas_id']);
    $sheet->setCellValue("J{$row}", $data['status']);
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