<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

<?php
ob_start(); // Memulai output buffering

include __DIR__ . '/../../layouts/master.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        $nama_tarif = $_POST['nama_tarif'];
        $nominal = $_POST['nominal'];
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'];
        $deskripsi = $_POST['deskripsi'];
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0; // Checkbox default to checked

        // Validate input
        if (!empty($nama_tarif) && !empty($nominal) && !empty($tahun_ajaran_id)) {
            // Prepare SQL query
            $sql = "INSERT INTO tarif_spp (nama_tarif, nominal, tahun_ajaran_id, deskripsi, status_aktif) 
                    VALUES (:nama_tarif, :nominal, :tahun_ajaran_id, :deskripsi, :status_aktif)";
            $stmt = $conn->prepare($sql);

            // Bind parameters and execute
            if (
                $stmt->execute([
                    'nama_tarif' => $nama_tarif,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'deskripsi' => $deskripsi,
                    'status_aktif' => $status_aktif
                ])
            ) {
                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data tarif berhasil ditambahkan.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
                    </script>";
                exit();
            } else {
                echo "Gagal menyisipkan catatan!";
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }


    // Update Record
    if ($action == 'update') {
        $id = $_POST['id'];
        $nama_tarif = $_POST['nama_tarif'];
        $nominal = $_POST['nominal'];
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'];
        $deskripsi = $_POST['deskripsi'];
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0; // Checkbox default to checked

        // Validate input
        if (!empty($id) && !empty($nama_tarif) && !empty($nominal) && !empty($tahun_ajaran_id)) {
            // Prepare SQL query
            $sql = "UPDATE tarif_spp 
                    SET nama_tarif = :nama_tarif, nominal = :nominal, tahun_ajaran_id = :tahun_ajaran_id, deskripsi = :deskripsi, status_aktif = :status_aktif
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Bind parameters and execute
            if (
                $stmt->execute([
                    'id' => $id,
                    'nama_tarif' => $nama_tarif,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'deskripsi' => $deskripsi,
                    'status_aktif' => $status_aktif
                ])
            ) {
                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data tarif berhasil diperbarui.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
                    </script>";
                exit();
            } else {
                echo "Gagal memperbarui Data!";
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }


    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            // Prepare SQL query for deletion
            $sql = "DELETE FROM tarif_spp WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Execute the query
            if ($stmt->execute(['id' => $id])) {
                // Redirect after successful deletion
                echo "<script>
                        alert('Data tarif berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
                    </script>";
                exit();
            } else {
                echo "Gagal menghapus Data!";
            }
        } else {
            echo "ID is required!";
        }
    }
}

$db = Database::getInstance();
$sql = "SELECT 
        t.id,
        t.nama_tarif,
        t.nominal,
        t.deskripsi,
        t.status_aktif,
        ta.tahun AS tahun_ajaran,
        t.tahun_ajaran_id
    FROM tarif_spp t
    LEFT JOIN tahun_ajaran ta ON t.tahun_ajaran_id = ta.id
    ORDER BY t.id DESC
";

$results = $db->query($sql);
// var_dump($results);

// Fetch only active form "tahun_ajaran"
$sql = "SELECT id, tahun FROM tahun_ajaran WHERE status = 'Aktif' ORDER BY tahun DESC";
$tahun_ajaran = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush(); // Mengakhiri output buffering
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
                    <h3 class="mb-0">Tagihan SPP Siswa</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Tagihan
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
                            <?php if (!empty($results)): ?>
                            <table id="datatable" class="table table-striped table-bordered pt-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Tarif</th>
                                        <th>Nominal</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Deskripsi</th>
                                        <th>Status Aktif</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td><?= $row['nama_tarif']; ?></td>
                                        <td>Rp. <?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                                        <td><?= $row['tahun_ajaran']; ?></td>
                                        <td><?= $row['deskripsi'] ?? '-'; ?></td>
                                        <td class="text-center"><?= $row['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="<?= $row['id'] ?? '-'; ?>"
                                                data-nama_tarif="<?= $row['nama_tarif'] ?? '-'; ?>"
                                                data-nominal="<?= $row['nominal'] ?? '-'; ?>"
                                                data-tahun_ajaran_id="<?= $row['tahun_ajaran_id'] ?? '-'; ?>"
                                                data-deskripsi="<?= $row['deskripsi'] ?? '-'; ?>"
                                                data-status_aktif="<?= $row['status_aktif'] ?? '0'; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal" data-bs-id="<?= $row['id'] ?? '-'; ?>"
                                                data-nama_tarif="<?= $row['nama_tarif'] ?? '-'; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p>No data available.</p>
                            <?php endif; ?>
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
                            <h5 class="modal-title" id="createModalLabel">Tambah Tagihan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form for creating a new record -->
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="nama_tarif" class="form-label">Nama Tarif</label>
                                    <input type="text" class="form-control" id="nama_tarif" name="nama_tarif" required
                                        placeholder="SPP Bulan September">
                                </div>
                                <div class="input-group mb-3">
                                    <label for="nominal" class="form-label">Nominal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control" id="nominal" name="nominal" required
                                            aria-label="Jumlah (ke rupiah)" />
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
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
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                        placeholder="Masukkan Deskripsi (opsional)"></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="status_aktif"
                                        name="status_aktif" checked>
                                    <label class="form-check-label" for="status_aktif">Aktif</label>
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

            <!-- /.modal-dialog edit -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Tagihan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" id="edit_id" name="id">
                                <div class="mb-3">
                                    <label for="edit_nama_tarif" class="form-label">Nama Tarif</label>
                                    <input type="text" class="form-control" id="edit_nama_tarif" name="nama_tarif"
                                        required placeholder="SPP Bulan September">
                                </div>
                                <div class="input-group mb-3">
                                    <label for="edit_nominal" class="form-label">Nominal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control" id="edit_nominal" name="nominal"
                                            required aria-label="Jumlah (ke rupiah)" />
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                    <select class="form-select" id="edit_tahun_ajaran_id" name="tahun_ajaran_id"
                                        required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                        <option value="<?php echo $ta['id']; ?>">
                                            <?php echo $ta['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"
                                        placeholder="Masukkan Deskripsi (opsional)"></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_status_aktif"
                                        name="status_aktif">
                                    <label class="form-check-label" for="edit_status_aktif">Aktif</label>
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
                            <h5 class="modal-title" id="deleteModalLabel">Hapus Tagihan</h5>
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
    <!--end::Container-->
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
$(document).ready(function() {
    $('#datatable').dataTable();
    $("[data-toggle=tooltip]").tooltip();

});

document.addEventListener('DOMContentLoaded', function() {

    // Handling Update
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Get data attributes from the button
            const id = button.getAttribute('data-id');
            const nama_tarif = button.getAttribute('data-nama_tarif');
            const nominal = button.getAttribute('data-nominal');
            const tahun_ajaran_id = button.getAttribute('data-tahun_ajaran_id');
            const deskripsi = button.getAttribute('data-deskripsi');
            const status_aktif = button.getAttribute('data-status_aktif') === '1';

            // Update the modal's content.
            const modalTitle = editModal.querySelector('.modal-title');
            modalTitle.textContent = `Edit Data Tagihan: ${nama_tarif}`;

            // Populate the form in the modal with the data
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_tarif').value = nama_tarif;
            document.getElementById('edit_nominal').value = nominal;
            document.getElementById('edit_tahun_ajaran_id').value = tahun_ajaran_id;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_status_aktif').checked = status_aktif;
        });
    }

    // Handling Delete
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-bs-id');
            const nama_tarif = button.getAttribute('data-nama_tarif');

            // Update the modal's content.
            const modalTitle = deleteModal.querySelector('.modal-title');
            modalTitle.textContent = `Hapus Data Tagihan: ${nama_tarif}`;

            // Populate the form with the id
            const form = deleteModal.querySelector('#deleteForm');
            form.querySelector('#delete-id').value = id;

        });

    }
})
</script>