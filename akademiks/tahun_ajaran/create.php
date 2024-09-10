<?php
    include __DIR__ . '/../../layouts/master.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tahun = $_POST['tahun'];
        $status = $_POST['status'];

        $query = 'INSERT INTO tahun_ajaran (tahun, status) VALUES (:tahun, :status)';
        $stmt = $conn->prepare($query);
        $stmt->execute(['tahun' => $tahun, 'status' => $status]);

        header('Location: index.php');
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
                            <h3 class="card-title">Form Tambah Tahun Ajaran</h3>
                        </div>
                        <?php
                            if (!empty($error)) {
                                echo '<div class="alert alert-danger">' . $error . '</div>';
                            }
                        ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="tahun">Tahun</label>
                                <input type="text" id="tahun" name="tahun" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->