<?php
    include __DIR__ . '/../../layouts/master.php';

    $db = Database::getInstance();
    // Concise SQL query with table aliases

    $sql = "SELECT * FROM tahun_ajaran";
    $results = $db->query($sql);
// var_dump($results);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Data Tahun Ajaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Data Tahun Ajaran</li>
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
                            <h3 class="card-title">DataTable default features</h3>
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm" onclick="window.location.href='/siswa/tambah-siswa';">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                            <?php
                                if (!empty($success)) {
                                    echo '<div class="alert alert-success">' . $success . '</div>';
                                }
                            ?>
                        </div>
                        <!-- Isi konten lainnya -->

                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($results) > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['tahun']); ?></td>
                                                <td>
                                                    <?php
                                                        $status = htmlspecialchars($row['status']);
                                                        $badgeClass = $status == 'Aktif' ? 'badge-success' : 'badge-danger';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a class="btn btn-info btn-sm" href="edit.php?id=<?= htmlspecialchars($row['id']); ?>">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <a class="btn btn-danger btn-sm" href="delete.php?id=<?= htmlspecialchars($row['id']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data</td>
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