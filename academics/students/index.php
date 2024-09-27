<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Concise SQL query with table aliases
$stmt = $db->prepare("SELECT 
            s.id,
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

// Mengakhiri output buffering
ob_end_flush();
?>

<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Simple Tables</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Simple Tables
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
                            <h3 class="card-title">DataTable Default Features</h3>
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
                                                <td><?= htmlspecialchars($row['nisn']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['nama_lengkap']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['jenis_kelamin']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($row['tahun_ajaran']) ?: '-'; ?></td>
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
                                                    <button class="btn btn-warning btn-sm">Edit</button>
                                                    <!-- <button class="btn btn-danger">Delete</button> -->
                                                    <!-- <a class="btn btn-info btn-sm"
                                                href="/siswa/edit-siswa?id=<?= htmlspecialchars($row['id']); ?>">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a> -->
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

            <!-- /.modal-dialog create -->
            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="createModalLabel">Create New Data</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label for="new-recipient-name" class="col-form-label">Recipient:</label>
                                    <input type="text" class="form-control" id="new-recipient-name"
                                        placeholder="Enter recipient name">
                                </div>
                                <div class="mb-3">
                                    <label for="new-message-text" class="col-form-label">Message:</label>
                                    <textarea class="form-control" id="new-message-text"
                                        placeholder="Enter message"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Create</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.modal-dialog update -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="editModalLabel">Edit Data</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label for="recipient-name" class="col-form-label">Recipient:</label>
                                    <input type="text" class="form-control" id="recipient-name">
                                </div>
                                <div class="mb-3">
                                    <label for="message-text" class="col-form-label">Message:</label>
                                    <textarea class="form-control" id="message-text"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.modal-dialog delete -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="deleteModalLabel">Hapus Data</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat
                                dikembalikan.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger">Hapus</button>
                        </div>
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

    // Edit Modal
    const editModal = document.getElementById('editModal')
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const recipient = button.getAttribute('data-bs-whatever')
            const modalTitle = editModal.querySelector('.modal-title')
            const modalBodyInput = editModal.querySelector('.modal-body input')

            modalTitle.textContent = `Edit data for ${recipient}`
            modalBodyInput.value = recipient
        })
    }

    // Delete Modal
    const deleteModal = document.getElementById('deleteModal')
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const recipient = button.getAttribute('data-bs-whatever')
            const modalTitle = deleteModal.querySelector('.modal-title')

            modalTitle.textContent = `Hapus data untuk ${recipient}`
        })
    }
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>