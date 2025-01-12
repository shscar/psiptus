<?php
require 'vendor/autoload.php'; // Autoload PhpSpreadsheet
$db = Database::getInstance()->getConnection();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelas_id = intval($_POST['kelas_id']); // Validasi input kelas_id

    // Ambil data siswa berdasarkan kelas
    $stmt = $db->prepare("SELECT nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, kelas_id, alamat, status 
                          FROM siswa WHERE kelas_id = ?");
    $stmt->execute([$kelas_id]);
    $siswaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($siswaData)) {
        die("Tidak ada data untuk kelas ini.");
    }

    // Buat file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header kolom
    $headers = ['No', 'NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Alamat', 'Kelas ID', 'Status'];
    $sheet->fromArray($headers, null, 'A1');

    // Tambahkan data ke Excel
    $row = 2;
    $no = 1;
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
    header("Content-Disposition: attachment;filename=\"data_siswa_kelas_{$kelas_id}.xlsx\"");
    header('Cache-Control: max-age=0');

    // Simpan file Excel ke output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    die("Invalid request method.");
}
?>