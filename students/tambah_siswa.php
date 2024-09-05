<?php
include __DIR__ . '/../layouts/master.php';

$query = "SELECT 
            kelas.id, 
            kelas.nama_kelas, 
            tingkat_kelas.tingkat, 
            tahun_ajaran.tahun
        FROM kelas
        JOIN tingkat_kelas ON kelas.tingkat_kelas_id = tingkat_kelas.id
        JOIN tahun_ajaran ON tingkat_kelas.tahun_ajaran_id = tahun_ajaran.id
        WHERE tahun_ajaran.status_aktif = 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $alamat = $_POST['alamat'];
    $kelas_id = $_POST['kelas_id'];
    $status = $_POST['status'];

    try {
        // Query untuk menambahkan data ke tabel siswa
        $sql = "INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status) 
                VALUES (:nis, :nisn, :nama_lengkap, :jenis_kelamin, :tanggal_lahir, :tempat_lahir, :alamat, :kelas_id, :status)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nis', $nis, PDO::PARAM_STR);
        $stmt->bindParam(':nisn', $nisn, PDO::PARAM_STR);
        $stmt->bindParam(':nama_lengkap', $nama_lengkap, PDO::PARAM_STR);
        $stmt->bindParam(':jenis_kelamin', $jenis_kelamin, PDO::PARAM_STR);
        $stmt->bindParam(':tanggal_lahir', $tanggal_lahir, PDO::PARAM_STR);
        $stmt->bindParam(':tempat_lahir', $tempat_lahir, PDO::PARAM_STR);
        $stmt->bindParam(':alamat', $alamat, PDO::PARAM_STR);
        $stmt->bindParam(':kelas_id', $kelas_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Data siswa berhasil ditambahkan.');
                    window.location.href = '/siswa';
                  </script>";
            exit();
        } else {
            echo "Error: Gagal menambahkan data siswa.";
        }
    } catch (PDOException $e) {
        // Memeriksa apakah error adalah duplikat entri
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            // Mengambil nilai NIS yang menyebabkan error
            preg_match("/Duplicate entry '(\d+)' for key 'siswa\.nis'/", $e->getMessage(), $matches);
            $duplicateNis = isset($matches[1]) ? $matches[1] : '';
    
            // Menampilkan pesan error yang lebih informatif
            $error = "NIS $duplicateNis sudah ada. Silakan gunakan NIS yang berbeda.";
            exit(); // Menghentikan eksekusi lebih lanjut
        } else {
            $error = "Error: " . $e->getMessage();
            exit(); // Menghentikan eksekusi lebih lanjut
        }
    }

    $conn = null;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <h1>Tambah Siswa Baru</h1>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Form Tambah Siswa</h3>
                        </div>
                        <?php
                        if (!empty($error)) {
                            // echo '<div class="alert alert-danger">' . $error . '</div>';
                            echo '<div class="alert alert-danger">' . $error . '</div>';
                        }
                        ?>
                        <form action="#" method="POST">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="nis">NIS</label>
                                        <input type="text" class="form-control" id="nis" name="nis" required maxlength="20"
                                            value="<?php echo $nis; ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="nisn">NISN</label>
                                        <input type="number" class="form-control" id="nisn" name="nisn" required
                                            value="<?php echo $nisn; ?>">
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label for="nama_lengkap">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                            required maxlength="100" value="<?php echo $nama_lengkap; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="jenis_kelamin">Jenis Kelamin</label>
                                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="Laki-laki" <?php echo ($jenis_kelamin == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="Perempuan" <?php echo ($jenis_kelamin == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="tanggal_lahir">Tanggal Lahir</label>
                                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                            required value="<?php echo $tanggal_lahir; ?>">
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label for="tempat_lahir">Tempat Lahir</label>
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" maxlength="50" 
                                            value="<?php echo $tempat_lahir; ?>">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="alamat">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat"
                                            rows="3"><?php echo $alamat; ?></textarea>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="kelas_id">Kelas</label>
                                        <select class="form-control" id="kelas_id" name="kelas_id">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php foreach ($result as $row): ?>
                                                <option value="<?php echo $row['id']; ?>" 
                                                    <?php echo ($kelas_id == $row['id']) ? 'selected' : ''; ?>>
                                                    <?php echo "{$row['tingkat']} {$row['nama_kelas']} - {$row['tahun']}"; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="Aktif" <?php echo ($status == 'Aktif') ? 'selected' : ''; ?>>Aktif
                                            </option>
                                            <option value="Tidak Aktif" <?php echo ($status == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-secondary" onclick="history.back();">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->