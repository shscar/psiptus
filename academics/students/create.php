<?php
include __DIR__ . '/../../layouts/master.php';

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

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">General Form</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            General Form
                        </li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row g-4">
                <!--begin::Col-->
                <div class="col-md-12">
                    <!--begin::Quick Example-->
                    <div class="card card-primary card-outline mb-4">
                        <!--begin::Header-->
                        <div class="card-header">
                            <div class="card-title">Quick Example</div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Form-->
                        <form>
                            <!--begin::Body-->
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="exampleInputEmail1"
                                        aria-describedby="emailHelp" />
                                    <div id="emailHelp" class="form-text">
                                        We'll never share your email with anyone else.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1" />
                                </div>
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" id="inputGroupFile02" />
                                    <label class="input-group-text" for="inputGroupFile02">Upload</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" />
                                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                </div>
                            </div>
                            <!--end::Body-->
                            <!--begin::Footer-->
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                            <!--end::Footer-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Quick Example-->
                </div>
                <!--end::Col-->

            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->