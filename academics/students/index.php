<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Concise SQL query with table aliases
$stmt = $db->prepare("SELECT 
            s.id,
            s.nis,
            s.nisn,
            s.nama_lengkap,
            s.jenis_kelamin,
            s.status,
            t.tahun AS tahun_ajaran,
            CONCAT(tk.tingkat, ' - ', k.nama_kelas) AS kelas
        FROM siswa s
        LEFT JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN tingkat_kelas tk ON k.tingkat_kelas_id = tk.id
        LEFT JOIN tahun_ajaran t ON tk.tahun_ajaran_id = t.id
        ORDER BY s.id DESC
    ");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete Record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['delete-id'];

    // Prepare the delete statement
    $stmt = $db->prepare("DELETE FROM siswa WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>
                alert('Data siswa berhasil diHapus.');
                window.location.href = '/siswa';
              </script>";
        exit();
    } else {
        echo "Error deleting record: " . $stmt->errorInfo()[2];
    }
} else {
    echo "Invalid request.";
}

// Mengakhiri output buffering
ob_end_flush();
?>

<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Data Siswa

                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle rounded-5"
                                    data-bs-toggle="dropdown" aria-expanded="false">?</button>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Create
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Edit
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Delete
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-dash me-2"></i>
                                        Export
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-dash me-2"></i>
                                        Import
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </h3>

                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            student
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- App Content -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">DataTable Siswa</h3>
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm"
                                    onclick="window.location.href='/siswa/tambah-siswa';">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                            <?php
                            if (!empty($success)) {
                                echo '<div class="alert alert-success">' . $success . '</div>';
                            }
                            ?>
                        </div>

                        <!-- Card body -->
                        <div class="card-body">
                            <table id="datatable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">NIS</th>
                                        <th style="width: 10%">NISN</th>
                                        <th style="width: 25%">Nama</th>
                                        <th style="width: 15%">Gender</th>
                                        <th style="width: 10%">Kelas</th>
                                        <th style="width: 8%">Status</th>
                                        <th style="width: 8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($results) > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nis']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['nisn']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['nama_lengkap']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['jenis_kelamin']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?: '-'; ?></td>
                                                <td class="project-state">
                                                    <?php
                                                    $status = htmlspecialchars($row['status']) ?: '-';
                                                    switch ($status) {
                                                        case 'Aktif':
                                                            $badgeClass = 'badge bg-success';
                                                            $statusText = 'Aktif';
                                                            break;
                                                        case 'Tidak Aktif':
                                                            $badgeClass = 'badge bg-danger';
                                                            $statusText = 'Tidak Aktif';
                                                            break;
                                                        default:
                                                            $badgeClass = 'badge bg-secondary';
                                                            $statusText = 'Unknown';
                                                    }
                                                    ?>
                                                    <span class="<?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td class="project-actions text-right">
                                                    <button class="btn btn-info btn-sm"
                                                        onclick="window.location.href='/siswa/edit-siswa?<?= htmlspecialchars($row['id']); ?>'">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-bs-id="<?= htmlspecialchars($row['id']) ?: '-'; ?>"
                                                        data-nis="<?= htmlspecialchars($row['nis']) ?: '-'; ?>"
                                                        data-nisn="<?= htmlspecialchars($row['nisn']) ?: '-'; ?>"
                                                        data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?: '-'; ?>"
                                                        data-kelas="<?= htmlspecialchars($row['kelas']) ?: '-'; ?>">

                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <!-- <button class="btn btn-danger btn-sm"
                                                        onclick="window.location.href='/siswa/delete-siswa?<?= htmlspecialchars($row['id']); ?>'">
                                                        <i class="bi bi-trash"></i>
                                                    </button> -->
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
                    </div>
                </div>
            </div>

            <!-- /.modal-dialog delete -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="deleteForm" method="POST" action="#">
                            <!-- Specify your delete script here -->
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="deleteModalLabel">Hapus Data</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data berikut:
                                <table class="table">
                                    <tr>
                                        <td>NIS/N</td>
                                        <td>
                                            : <span id="nis"></span>(<span id="nisn"></span>) <br />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nama</td>
                                        <td>
                                            : <span id="nama"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Kelas</td>
                                        <td>
                                            : <span id="kelas"></span>
                                        </td>
                                    </tr>
                                </table>
                                </p>
                                <p>
                                    Tindakan ini tidak dapat dikembalikan!.
                                </p>
                                <input type="hidden" id="delete-id" name="delete-id" value="">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>
<!--end::App Main-->

<!-- Inisialisasi DataTables -->
<script>
    $(document).ready(function () {
        $('#datatable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Handling Delete 
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const name = button.getAttribute('data-nama');
                const nis = button.getAttribute('data-nis');
                const nisn = button.getAttribute('data-nisn');
                const kelas = button.getAttribute('data-kelas');

                // Update the modal's content.
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Hapus Data: ${name}`;

                // Populate the form with the id
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;

                // Mengisi konten modal dengan data yang didapat
                document.getElementById('nama').textContent = name;
                document.getElementById('nis').textContent = nis;
                document.getElementById('nisn').textContent = nisn;
                document.getElementById('kelas').textContent = kelas;
            });
        }
    });
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>