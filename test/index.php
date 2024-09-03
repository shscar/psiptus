<?php
    include __DIR__ . '/../config/connection.php';
    // $db = Database::getInstance();
    


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil data dari form
        $nis = trim($_POST['nis']);
        $nisn = trim($_POST['nisn']);
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $jenis_kelamin = trim($_POST['jenis_kelamin']);
        $tanggal_lahir = trim($_POST['tanggal_lahir']);
        $tempat_lahir = trim($_POST['tempat_lahir']);
        $alamat = trim($_POST['alamat']);
        $kelas_id = trim($_POST['kelas_id']);
        $status = trim($_POST['status']);
        
        // Validasi data
        $errors = [];
        
        if (empty($nis) || !preg_match('/^[a-zA-Z0-9]+$/', $nis)) {
            $errors[] = 'NIS harus diisi dan hanya boleh mengandung huruf dan angka.';
        }
        
        if (empty($nisn) || !preg_match('/^[0-9]+$/', $nisn)) {
            $errors[] = 'NISN harus diisi dan hanya boleh mengandung angka.';
        }
        
        if (empty($nama_lengkap) || strlen($nama_lengkap) > 100) {
            $errors[] = 'Nama Lengkap harus diisi dan tidak boleh lebih dari 100 karakter.';
        }
        
        if (empty($jenis_kelamin) || !in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
            $errors[] = 'Jenis Kelamin harus dipilih.';
        }
        
        if (empty($tanggal_lahir)) {
            $errors[] = 'Tanggal Lahir harus diisi.';
        }
        
        if (empty($tempat_lahir) || strlen($tempat_lahir) > 50) {
            $errors[] = 'Tempat Lahir harus diisi dan tidak boleh lebih dari 50 karakter.';
        }
        
        if (empty($alamat)) {
            $errors[] = 'Alamat harus diisi.';
        }
        
        if (empty($kelas_id) || !is_numeric($kelas_id)) {
            $errors[] = 'Kelas ID harus diisi dan harus berupa angka.';
        }
        
        if (empty($status) || !in_array($status, ['Aktif', 'Tidak Aktif'])) {
            $errors[] = 'Status harus dipilih.';
        }
        
        // Jika ada kesalahan
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
        } else {
            // Jika tidak ada kesalahan, lakukan penyimpanan data
            $sql = "INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variabel ke parameter
                $stmt->bindParam("sssssssis", $nis, $nisn, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, $tempat_lahir, $alamat, $kelas_id, $status);
                
                // Eksekusi statement
                if ($stmt->execute()) {
                    echo "<p>Data berhasil disimpan.</p>";
                } else {
                    echo "<p>Terjadi kesalahan saat menyimpan data.</p>";
                }
                
            } else {
                echo "<p>Terjadi kesalahan dalam persiapan query.</p>";
            }
            
            // Tutup koneksi
        }
    }
?>