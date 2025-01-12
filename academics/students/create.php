<?php
include __DIR__ . '/../../layouts/master.php';
// require 'vendor/autoload.php'; // Autoload PhpSpreadsheet
// use PhpOffice\PhpSpreadsheet\IOFactory;

$db = Database::getInstance()->getConnection();

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
//     // Lokasi file Excel yang diunggah
//     $uploadDir = 'uploads/';
//     $fileName = basename($_FILES['file_excel']['name']);
//     $filePath = $uploadDir . $fileName;

//     // Validasi tipe file
//     $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
//     if ($fileType !== 'xlsx' && $fileType !== 'xls') {
//         echo "File yang diunggah bukan file Excel.";
//         exit;
//     }

//     // Pindahkan file yang diunggah ke direktori tujuan
//     if (move_uploaded_file($_FILES['file_excel']['tmp_name'], $filePath)) {
//         try {
//             // Membaca file Excel
//             $spreadsheet = IOFactory::load($filePath);
//             $sheet = $spreadsheet->getActiveSheet();
//             $dataRows = $sheet->toArray(null, true, true, true);

//             // Periksa apakah ada data
//             if (count($dataRows) < 2) {
//                 echo "Tidak ada data untuk dimasukkan.";
//                 exit;
//             }

//             // Mengabaikan header (baris pertama)
//             array_shift($dataRows);

//             // Persiapkan query untuk memasukkan data
//             $stmt = $db->prepare("INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, tempat_lahir, alamat, kelas_id, status, created_at, updated_at) 
//                                    VALUES (:nis, :nisn, :nama_lengkap, :jenis_kelamin, :tanggal_lahir, :tempat_lahir, :alamat, :kelas_id, :status, NOW(), NOW())");

//             // Iterasi data dan masukkan ke database
//             foreach ($dataRows as $row) {
//                 $stmt->bindValue(':nis', $row['B']);
//                 $stmt->bindValue(':nisn', $row['C']);
//                 $stmt->bindValue(':nama_lengkap', $row['D']);
//                 $stmt->bindValue(':jenis_kelamin', $row['E']);
//                 $stmt->bindValue(':tanggal_lahir', $row['F']);
//                 $stmt->bindValue(':tempat_lahir', $row['G']);
//                 $stmt->bindValue(':alamat', $row['H']);
//                 $stmt->bindValue(':kelas_id', $row['I']);
//                 $stmt->bindValue(':status', $row['J']);

//                 // Eksekusi query
//                 $stmt->execute();
//             }

//             echo "Data berhasil dimasukkan ke database.";
//         } catch (Exception $e) {
//             echo "Terjadi kesalahan: " . $e->getMessage();
//         }
//     } else {
//         echo "Gagal mengunggah file.";
//     }
// }



$query = "SELECT
            kelas.id,
            kelas.nama_kelas,
            tingkat_kelas.tingkat,
            tahun_ajaran.tahun
        FROM kelas
        JOIN tingkat_kelas ON kelas.tingkat_kelas_id = tingkat_kelas.id
        JOIN tahun_ajaran ON tingkat_kelas.tahun_ajaran_id = tahun_ajaran.id
        -- WHERE tahun_ajaran.status = 'Aktif'
    ";

$stmt = $db->prepare($query);
$stmt->execute();
$resultKelas = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
        $stmt = $db->prepare($sql);
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

    $db = null;
}
?>

<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Siswa</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Student-C
                        </li>
                    </ol>
                </div>
            </div>

        </div>
    </div>

    <!-- App Content -->
    <div class="app-content">
        <div class="container-fluid">

            <ul class="nav nav-tabs" id="formTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual"
                        type="button" role="tab" aria-controls="manual" aria-selected="true">
                        Masukan Data (Form Manual)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="excel-tab" data-bs-toggle="tab" data-bs-target="#excel" type="button"
                        role="tab" aria-controls="excel" aria-selected="false">
                        Masukan dengan Excel
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="formTabsContent">
                <!-- Form Manual -->
                <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="card card-primary card-outline mb-4">
                                <div class="card-header">
                                    <div class="card-title">Form Tambah Siswa</div>
                                    <?php
                                    if (!empty($error)) {
                                        // echo '<div class="alert alert-danger">' . $error . '</div>';
                                        echo '<div class="alert alert-danger">' . $error . '</div>';
                                    }
                                    ?>
                                </div>
                                <form action="#" method="POST">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-6 mb-3">
                                                <label for="nis">NIS</label>
                                                <input type="number" class="form-control" id="nis" name="nis" required
                                                    maxlength="20">
                                            </div>
                                            <div class="form-group col-6 mb-3">
                                                <label for="nisn">NISN</label>
                                                <input type="number" class="form-control" id="nisn" name="nisn"
                                                    required>
                                            </div>

                                            <div class="form-group col-8 mb-3">
                                                <label for="nama_lengkap">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama_lengkap"
                                                    name="nama_lengkap" required maxlength="100">
                                            </div>
                                            <div class="form-group col-md-4 mb-3">
                                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin"
                                                    required>
                                                    <option value="No">Pilih</option>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-3">
                                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                                <input type="date" class="form-control" id="tanggal_lahir"
                                                    name="tanggal_lahir" required>
                                            </div>
                                            <div class="form-group col-md-8 mb-3">
                                                <label for="tempat_lahir">Tempat Lahir</label>
                                                <input type="text" class="form-control" id="tempat_lahir"
                                                    name="tempat_lahir" maxlength="50">
                                            </div>
                                            <div class="form-group col-md-12 mb-3">
                                                <label for="alamat">Alamat</label>
                                                <textarea class="form-control" id="alamat" name="alamat"
                                                    rows="3"></textarea>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label for="kelas_id">Kelas</label>
                                                <select class="form-control" id="kelas_id" name="kelas_id">
                                                    <option value="">-- Pilih Kelas --</option>
                                                    <?php foreach ($resultKelas as $kelas): ?>
                                                        <option value="<?php echo $kelas['id']; ?>">
                                                            <?php echo "{$kelas['tingkat']} {$kelas['nama_kelas']} - {$kelas['tahun']}"; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label for="status">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="Aktif">Aktif</option>
                                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" class="btn btn-secondary" onclick="history.back();">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            Submit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Excel -->
                <div class="tab-pane fade" id="excel" role="tabpanel" aria-labelledby="excel-tab">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="card card-primary card-outline mb-4">
                                <div class="card-header">
                                    <h4>Form Excel</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <form action="/siswa/file-upload" method="POST" enctype="multipart/form-data">
                                            <div class="mb-3">

                                                <div class="form-group col-md-12 mb-3">
                                                    <a href="/siswa/file/example-data-siswa"
                                                        download="example-data-siswa.xls"
                                                        class="btn btn-info mb-3">Download Template</a>
                                                </div>
                                                <label for="file_excel" class="form-label">Import File Excel</label>
                                                <input type="file" class="form-control" id="file_excel"
                                                    name="file_excel" accept=".xlsx, .xls" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary" name="upload"
                                                value="upload">Import</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
</main>
<!--end::App Main-->