<?php
    include __DIR__ . '/../layouts/master.php';

    $db = Database::getInstance();
    // Concise SQL query with table aliases
    $sql = "SELECT 
            s.id,
            s.nisn,
            s.nama_lengkap,
            s.jenis_kelamin,
            s.status,
            t.tahun AS tahun_ajaran,
            CONCAT(tk.tingkat, ' ', k.nama_kelas) AS kelas
        FROM siswa s
        LEFT JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN tingkat_kelas tk ON k.tingkat_kelas_id = tk.id
        LEFT JOIN tahun_ajaran t ON tk.tahun_ajaran_id = t.id
        ORDER BY s.id DESC
    ";

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
                                        <th style="width: 10%">NISN</th>
                                        <th style="width: 25%">Nama</th>
                                        <th style="width: 15%">Gender</th>
                                        <th style="width: 10%">Kelas</th>
                                        <th style="width: 18%">Tahun Ajaran</th>
                                        <th style="width: 8%">Status</th>
                                        <th style="width: 8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($results) > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nisn']) ?: '-';?></td>
                                                <td><?= htmlspecialchars($row['nama_lengkap']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['jenis_kelamin']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['tahun_ajaran']) ?: '-'; ?></td>
                                                <td class="project-state">
                                                    <?php
                                                        $status = htmlspecialchars($row['status']) ?: '-';
                                                        switch ($status) {
                                                            case 'Aktif':
                                                                $badgeClass = 'badge-success';
                                                                $statusText = 'Aktif';
                                                                break;
                                                            case 'Tidak Aktif':
                                                                $badgeClass = 'badge-danger';
                                                                $statusText = 'Tidak Aktif';
                                                                break;
                                                            default:
                                                                $badgeClass = 'badge-secondary';
                                                                $statusText = 'Unknown';
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td class="project-actions text-right">
                                                    <a class="btn btn-info btn-sm" href="/siswa/edit-siswa?id=<?= htmlspecialchars($row['id']); ?>">
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