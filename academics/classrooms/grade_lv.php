<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

<?php
ob_start(); // Memulai output buffering

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance();
// $conn = $db->getConnection(); // Mendapatkan instance koneksi PDO

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'];
        $tingkat = $_POST['tingkat'];
        $keterangan = $_POST['keterangan'];

        // Validate input
        if (!empty($tahun_ajaran_id) && !empty($tingkat)) {
            $sql = "INSERT INTO tingkat_kelas (tahun_ajaran_id, tingkat, keterangan) VALUES (:tahun_ajaran_id, :tingkat, :keterangan)";
            $stmt = $conn->prepare($sql);
            if (
                $stmt->execute([
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'tingkat' => $tingkat,
                    'keterangan' => $keterangan
                ])
            ) {
                echo "<script>
                        alert('Data tingkat kelas berhasil ditambahkan.');
                        window.location.href = '/tingkat-kelas';
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
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'];
        $tingkat = $_POST['tingkat'];
        $keterangan = $_POST['keterangan'];

        // Validate input
        if (!empty($id) && !empty($tahun_ajaran_id) && !empty($tingkat)) {
            $sql = "UPDATE tingkat_kelas SET tahun_ajaran_id = :tahun_ajaran_id, tingkat = :tingkat, keterangan = :keterangan WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if (
                $stmt->execute([
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'tingkat' => $tingkat,
                    'keterangan' => $keterangan,
                    'id' => $id
                ])
            ) {
                echo "<script>
                        alert('Data berhasil diperbarui.');
                        window.location.href = '/tingkat-kelas';
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
            $sql = "DELETE FROM tingkat_kelas WHERE id = :id";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(['id' => $id])) {
                echo "<script>
                        alert('Data berhasil dihapus.');
                        window.location.href = '/tingkat-kelas';
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

// Fetch records for display with join
$sql = "SELECT tk.id, tk.tingkat, tk.tahun_ajaran_id, tk.keterangan, ta.tahun, ta.status 
    FROM tingkat_kelas tk
    JOIN tahun_ajaran ta ON tk.tahun_ajaran_id = ta.id
    ORDER BY tk.id DESC
";
$tingkat_kelas = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Fetch only active form "tahun_ajaran"
$sql = "SELECT id, tahun FROM tahun_ajaran WHERE status = 'Aktif' ORDER BY tahun DESC";
$tahun_ajaran = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// var_dump($siswa_kelas_id);

ob_end_flush(); // Mengakhiri output buffering
?>


<!-- App Main -->
<main class="app-main">
    <!-- begin:: Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Tingkat Kelas</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Tingkat Kelas
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- begin:: Content -->
    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Grade Level </h3>
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
                                        <th>No.</th>
                                        <th>Grade</th>
                                        <th>Tahun Ajaran</th>
                                        <th style="width:68%;">Keterangan</th>
                                        <th style="width:9%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tingkat_kelas as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td><?= $row['tingkat'] ?? '-'; ?></td>
                                        <td><?= $row['tahun'] ?? '-'; ?></td>
                                        <td><?= $row['keterangan'] ?? '-'; ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="<?= $row['id'] ?? '-'; ?>"
                                                data-tahun_ajaran_id="<?= $row['tahun_ajaran_id'] ?? '-'; ?>"
                                                data-tingkat="<?= $row['tingkat'] ?? '-'; ?>"
                                                data-keterangan="<?= $row['keterangan'] ?? '-'; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal" data-bs-id="<?= $row['id'] ?? '-'; ?>"
                                                data-tingkat="<?= $row['tingkat'] ?? '-'; ?>">
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
                            <h5 class="modal-title" id="createModalLabel">Tambah Tingkat Kelas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                    <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                        <option value="<?php echo $ta['id']; ?>">
                                            <?php echo $ta['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tingkat" class="form-label">Tingkat</label>
                                    <input type="text" class="form-control" id="tingkat" name="tingkat" required
                                        placeholder="Masukkan Tingkat (misalnya: XI)">
                                </div>
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                        placeholder="Masukkan Keterangan (opsional)"></textarea>
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
                            <h5 class="modal-title" id="editModalLabel">Edit Tingkat Kelas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form for editing an existing record -->
                            <form id="editForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" id="edit_id">

                                <!-- Dropdown for Tahun Ajaran -->
                                <div class="mb-3">
                                    <label for="edit_tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                    <select class="form-select" id="edit_tahun_ajaran_id" name="tahun_ajaran_id"
                                        required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                        <option value="<?= $ta['id']; ?>">
                                            <?= $ta['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Input for Tingkat -->
                                <div class="mb-3">
                                    <label for="edit_tingkat" class="form-label">Tingkat</label>
                                    <input type="text" class="form-control" id="edit_tingkat" name="tingkat" required>
                                </div>

                                <!-- Textarea for Keterangan -->
                                <div class="mb-3">
                                    <label for="edit_keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="edit_keterangan" name="keterangan"
                                        rows="3"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
</main>
<!--end::App Main-->

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
$(document).ready(function() {
    $('#datatable').dataTable();
    $("[data-toggle=tooltip]").tooltip();

});

document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Mengambil nilai dari tombol yang ditekan
            const id = button.getAttribute('data-id');
            const tahun_ajaran_id = button.getAttribute('data-tahun_ajaran_id');
            const tingkat = button.getAttribute('data-tingkat');
            const keterangan = button.getAttribute('data-keterangan');

            // Update the modal's content.
            const modalTitle = editModal.querySelector('.modal-title');
            modalTitle.textContent = `Edit Data Kelas: ${tingkat}`;

            // Mengisi modal form dengan data yang didapat
            const edit_id = document.getElementById('edit_id');
            const edit_tahun_ajaran_id = document.getElementById('edit_tahun_ajaran_id');
            const edit_tingkat = document.getElementById('edit_tingkat');
            const edit_keterangan = document.getElementById('edit_keterangan');
            edit_id.value = id;
            edit_tingkat.value = tingkat;
            edit_keterangan.value = keterangan;

            // Set selected option untuk dropdown Tahun Ajaran
            Array.from(edit_tahun_ajaran_id.options).forEach(option => {
                option.selected = (option.value === tahun_ajaran_id);
            });
            
            // Men-debug untuk memastikan nilai diambil dengan benar
            // console.log(
            //    `ID: ${id}, Tahun Ajaran ID: ${tahun_ajaran_id}, Tingkat: ${tingkat}, Keterangan: ${keterangan}`
            // );

            // Men-debug untuk memastikan apakah option yang benar terpilih
            // console.log(`Tahun Ajaran yang terpilih: ${edit_tahun_ajaran_id.value}`);
        });
    }

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-bs-id');
            const tingkat = button.getAttribute('data-tingkat');

            // Update the modal's content.
            const modalTitle = deleteModal.querySelector('.modal-title');
            modalTitle.textContent = `Delete Kelas: ${tingkat}`;

            // Update the modal's content.
            const form = deleteModal.querySelector('#deleteForm');
            form.querySelector('#delete-id').value = id;
        });
    }
})
</script>