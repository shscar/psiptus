<?php
    include __DIR__ . '/../layouts/master.php';

    $db = Database::getInstance();
    $sql = "SELECT s.id, s.nama_lengkap, s.nis, s.nisn, s.jenis_kelamin, k.email, k.telepon, s.status FROM siswa s
        LEFT JOIN siswa_kontak k ON s.id = k.siswa_id
        -- ORDER BY s.id ASC
    ";

    $results = $db->query($sql);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>DataTables</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">DataTables</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">DataTable with default features</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>NISN</th>
                                        <th>Nama</th>
                                        <th>Gender</th>
                                        <th>Tahun Ajaran</th>
                                        <th style="width: 10%">Kelas</th>
                                        <th style="width: 8%">Jurusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($results) > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nisn']) ?: '-';?></td>
                                                <td><?= htmlspecialchars($row['nama_lengkap']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['jenis_kelamin']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['tahun_ajar']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?: '-'; ?></td>
                                                <td class="project-actions text-right">
                                                    <a class="btn btn-info btn-sm" href="edit.php?id=<?= htmlspecialchars($row['id']); ?>">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data siswa</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

    <!-- Page specific script -->
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>