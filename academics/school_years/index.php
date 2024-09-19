<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />

<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

<?php
ob_start(); // Memulai output buffering

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();
$query = $db->query("SELECT 1");

$stmt = $db->prepare("SELECT * FROM tahun_ajaran ORDER BY id DESC");
$stmt->execute();
$tahun_ajaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        $tahun = $_POST['tahun'];
        $status = $_POST['status'];

        $sql = "INSERT INTO tahun_ajaran (tahun, status) VALUES (:tahun, :status)";
        $stmt = $db->prepare($sql);
        // $stmt->execute(['tahun' => $tahun, 'status' => $status]);

        // Validate input
        if (!empty($tahun) && !empty($status)) {
            // Prepare SQL query
            $sql = "INSERT INTO tahun_ajaran (tahun, status) VALUES (:tahun, :status)";
            $stmt = $db->prepare($sql);
            if ($stmt->execute(['tahun' => $tahun, 'status' => $status])) {
                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data siswa berhasil ditambahkan.');
                        window.location.href = '/tahun-ajaran';
                    </script>";
                exit();
            } else {
                echo "Failed to insert record!";
            }
        } else {
            echo "Please fill in all required fields!";
        }
    }

    // Update Record
    if ($action == 'update') {
        $id = $_POST['id'];
        $tahun = $_POST['tahun'];
        $status = $_POST['status'];

        // Validate input
        if (!empty($id) && !empty($tahun) && !empty($status)) {
            // Prepare SQL query
            $sql = "UPDATE tahun_ajaran SET tahun = :tahun, status = :status WHERE id = :id";
            $stmt = $db->prepare($sql);
            if ($stmt->execute(['tahun' => $tahun, 'status' => $status, 'id' => $id])) {
                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data berhasil diperbarui.');
                        window.location.href = '/tahun-ajaran';
                    </script>";
                exit();
            } else {
                echo "Failed to update record!";
            }
        } else {
            echo "Please fill in all required fields!";
        }
    }

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            $sql = "DELETE FROM tahun_ajaran WHERE id = :id";
            $stmt = $db->prepare($sql);
            if ($stmt->execute(['id' => $id])) {
                echo "<script>
                        alert('Data berhasil dihapus.');
                        window.location.href = '/tahun-ajaran';
                    </script>";
                exit();
            } else {
                echo "Failed to delete record!";
            }
        } else {
            echo "Invalid ID!";
        }
    }
}

// Mengakhiri output buffering
ob_end_flush();
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
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
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Siswa</h3>
                    <button type="button" class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                    </button>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table id="datatable" class="table table-striped table-bordered pt-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:7%;">No.</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Status</th>
                                        <th style="width:10%;">Edit</th>
                                        <th style="width:10%;">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tahun_ajaran as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= $row['tahun']; ?></td>
                                            <td><?= $row['status']; ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editModal" data-bs-id="<?= $row['id'] ?>"
                                                    data-bs-tahun="<?= $row['tahun'] ?>"
                                                    data-bs-status="<?= $row['status'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal" data-bs-id="<?= $row['id'] ?>"
                                                    data-bs-tahun="<?= $row['tahun'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
                            <h5 class="modal-title" id="createModalLabel">Tambah Tahun Ajaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun Ajaran</label>
                                    <input type="text" class="form-control" id="tahun" name="tahun" required
                                        placeholder="Masukkan Tahun Ajaran">
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.modal-dialog update -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" id="edit-id" name="id">
                                <div class="mb-3">
                                    <label for="edit-tahun" class="col-form-label">Tahun Ajaran:</label>
                                    <input type="text" class="form-control" id="edit-tahun" name="tahun">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-status" class="col-form-label">Status:</label>
                                    <select class="form-control" id="edit-status" name="status">
                                        <option value="Aktif">Aktif</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" form="editForm" class="btn btn-primary">Simpan</button>
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
                            <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.</p>
                        </div>
                        <div class="modal-footer">
                            <form id="deleteForm" method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" id="delete-id" name="id">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--end::App Content-->

</main>
<!--end::App Main-->

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
    $(document).ready(function () {
        $('#datatable').dataTable();

        $("[data-toggle=tooltip]").tooltip();

    });

    document.addEventListener('DOMContentLoaded', function () {

        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const tahun = button.getAttribute('data-bs-tahun');
                const status = button.getAttribute('data-bs-status');

                // Update the modal's content.
                const modalTitle = editModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Data: ${tahun}`;

                const form = editModal.querySelector('#editForm');
                form.querySelector('#edit-id').value = id;
                form.querySelector('#edit-tahun').value = tahun;
                form.querySelector('#edit-status').value = status;
            });
        }

        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const tahun = button.getAttribute('data-bs-tahun');

                // Update the modal's content.
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Delete Data: ${tahun}`;

                // Update the modal's content.
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;
            });
        }
    });
</script>