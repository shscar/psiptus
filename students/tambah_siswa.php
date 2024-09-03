<?php
include __DIR__ . '/../layouts/master.php';

// Initialize variables to store form data and error messages
$nis = $nisn = $nama_lengkap = $jenis_kelamin = $tanggal_lahir = $tempat_lahir = $alamat = $kelas_id = $status = "";
$error = $success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $nis = trim($_POST["nis"]);
    $nisn = filter_var($_POST["nisn"], FILTER_SANITIZE_NUMBER_INT);
    $nama_lengkap = trim($_POST["nama_lengkap"]);
    $jenis_kelamin = $_POST["jenis_kelamin"];
    $tanggal_lahir = $_POST["tanggal_lahir"];
    $tempat_lahir = trim($_POST["tempat_lahir"]);
    $alamat = trim($_POST["alamat"]);
    $kelas_id = filter_var($_POST["kelas_id"], FILTER_SANITIZE_NUMBER_INT);
    $status = $_POST["status"];

    // Perform basic validation
    if (empty($nis) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($tanggal_lahir)) {
        $error = "Field NIS, Nama Lengkap, Jenis_Kelamin dan Tanggal Lahir harus diisi.";
    } else {
        try {
            // Prepare SQL statement
            $sql = "INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status) 
                    VALUES (:nis, :nisn, :nama_lengkap, :jenis_kelamin, :tanggal_lahir, :tempat_lahir, :alamat, :kelas_id, :status)";
            
            $stmt = $conn->prepare($sql);

            // Bind parameters to the prepared statement
            $stmt->bindParam(':nis', $nis);
            $stmt->bindParam(':nisn', $nisn);
            $stmt->bindParam(':nama_lengkap', $nama_lengkap);
            $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
            $stmt->bindParam(':tanggal_lahir', $tanggal_lahir);
            $stmt->bindParam(':tempat_lahir', $tempat_lahir);
            $stmt->bindParam(':alamat', $alamat);
            $stmt->bindParam(':kelas_id', $kelas_id);
            $stmt->bindParam(':status', $status);

            // Execute the statement
            if ($stmt->execute()) {
                $success = "Data siswa berhasil ditambahkan.";
                // Clear form fields after successful submission
                $nis = $nisn = $nama_lengkap = $jenis_kelamin = $tanggal_lahir = $tempat_lahir = $alamat = $kelas_id = $status = "";

                header("Location: /data-siswa");
                exit();
            } else {
                $error = "Error: Gagal menambahkan data siswa.";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
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
                            echo '<div class="alert alert-danger">' . $error . '</div>';
                        }
                        ?>
                        <form method="POST" action="#">
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
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                                            maxlength="50" value="<?php echo $tempat_lahir; ?>">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="alamat">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat"
                                            rows="3"><?php echo $alamat; ?></textarea>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="kelas_id">Kelas ID</label>
                                        <input type="number" class="form-control" id="kelas_id" name="kelas_id" required
                                            value="<?php echo $kelas_id; ?>">
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