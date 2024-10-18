<?php
// Memulai output buffering
ob_start();

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Menyiapkan dan mengeksekusi query untuk mengambil data dari tabel siswa_pembayaran_lainnya
$stmt = $db->prepare("SELECT 
        pl.id,
        pl.nama_pembayaran,
        pl.bisa_diangsur,
        pl.nominal,
        pl.keterangan,
        pl.status_aktif,
        ta.tahun AS tahun_ajaran,
        pl.tahun_ajaran_id
    FROM siswa_pembayaran_lainnya pl
    LEFT JOIN tahun_ajaran ta ON pl.tahun_ajaran_id = ta.id
    ORDER BY pl.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// query untuk mengambil data tabel "tahun_ajaran"
$stmt = $db->prepare("SELECT id, tahun FROM tahun_ajaran ORDER BY tahun DESC");
$stmt->execute();
$tahun_ajaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        $nama_pembayaran = $_POST['nama_pembayaran'];
        $nominal = $_POST['nominal'];
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'] ?: null;
        $keterangan = $_POST['keterangan'] ?: null;
        $bisa_diangsur = isset($_POST['bisa_diangsur']) ? 1 : 0;
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

        // Validasi input
        if (!empty($nama_pembayaran) && !empty($nominal)) {
            // Persiapkan query SQL
            $sql = "INSERT INTO siswa_pembayaran_lainnya (nama_pembayaran, bisa_diangsur, nominal, tahun_ajaran_id, keterangan, status_aktif) 
                VALUES (:nama_pembayaran, :bisa_diangsur, :nominal, :tahun_ajaran_id, :keterangan, :status_aktif)";
            $stmt = $conn->prepare($sql);

            // Ikat parameter dan eksekusi
            if (
                $stmt->execute([
                    'nama_pembayaran' => $nama_pembayaran,
                    'bisa_diangsur' => $bisa_diangsur,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'keterangan' => $keterangan,
                    'status_aktif' => $status_aktif
                ])
            ) {
                // Redirect untuk menghindari pengiriman form ulang
                echo "<script>
                    alert('Data pembayaran berhasil ditambahkan.');
                    window.location.href = '/pendapatan/tagihan-lain-siswa';
                </script>";
                exit();
            } else {
                echo "Gagal Menyisipkan Data Tagihan!";
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }

    // Update Record
    if ($action == 'update') {
        $id = $_POST['id'];
        $nama_pembayaran = $_POST['nama_pembayaran'];
        $nominal = $_POST['nominal'];
        $tahun_ajaran_id = $_POST['tahun_ajaran_id'] ?: null;
        $keterangan = $_POST['keterangan'] ?: null;
        $bisa_diangsur = isset($_POST['bisa_diangsur']) ? 1 : 0;
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

        // Validasi input
        if (!empty($id) && !empty($nama_pembayaran) && !empty($nominal)) {
            // Persiapkan query SQL untuk update
            $sql = "UPDATE siswa_pembayaran_lainnya
                SET nama_pembayaran = :nama_pembayaran,
                    nominal = :nominal,
                    tahun_ajaran_id = :tahun_ajaran_id,
                    keterangan = :keterangan,
                    bisa_diangsur = :bisa_diangsur,
                    status_aktif = :status_aktif
                WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Ikat parameter dan eksekusi
            if (
                $stmt->execute([
                    'id' => $id,
                    'nama_pembayaran' => $nama_pembayaran,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'keterangan' => $keterangan,
                    'bisa_diangsur' => $bisa_diangsur,
                    'status_aktif' => $status_aktif
                ])
            ) {
                // Redirect untuk menghindari pengiriman form ulang
                echo "<script>
                    alert('Data pembayaran berhasil diperbarui.');
                    window.location.href = '/pendapatan/tagihan-lain-siswa';
                </script>";
                exit();
            } else {
                echo "Gagal Memperbarui Data Tagihan!";
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }

    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            // Prepare SQL query
            $sql = "DELETE FROM siswa_pembayaran_lainnya WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Bind parameters and execute
            if ($stmt->execute(['id' => $id])) {
                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data pembayaran berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-lain-siswa';
                    </script>";
                exit();
            } else {
                echo "Gagal Menghapus Data Tagihan!";
            }
        } else {
            echo "ID tidak valid!";
        }
    }
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
                    <h3 class="mb-0">Tagihan Lain-lain Untuk Siswa</h3>
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
        </div>
    </div>
    <!-- App Content -->
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
                            <?php if (!empty($results)): ?>
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis</th>
                                        <th>Nama Pembayaran</th>
                                        <th>Diangsur</th>
                                        <th>Nominal</th>
                                        <th>Ajaran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td>test</td>
                                        <td><?= $row['nama_pembayaran'] ?? '-'; ?></td>
                                        <td><?= $row['bisa_diangsur'] ? 'Ya' : 'Tidak'; ?></td>
                                        <td>Rp. <?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                                        <td><?= $row['tahun_ajaran'] ?? '-'; ?></td>
                                        <td class="text-center"><?= $row['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                        </td>
                                        <td class="text-center">

                                            <button type="button" class="btn btn-warning btn-sm edit-button"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= $row['id'] ?? '-'; ?>"
                                                data-nama_pembayaran="<?= $row['nama_pembayaran'] ?? '-'; ?>"
                                                data-nominal="<?= $row['nominal'] ?? '-'; ?>"
                                                data-tahun_ajaran_id="<?= $row['tahun_ajaran_id'] ?? '-'; ?>"
                                                data-keterangan="<?= $row['keterangan'] ?? '-'; ?>"
                                                data-bisa_diangsur="<?= $row['bisa_diangsur'] ?? '-'; ?>"
                                                data-status_aktif="<?= $row['status_aktif'] ?? '-'; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal" data-id="<?= $row['id'] ?? '-'; ?>"
                                                data-nama_pembayaran="<?= $row['nama_pembayaran'] ?? '-'; ?>">
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
                            <h5 class="modal-title" id="createModalLabel">Tambah Pembayaran Lainnya</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="nama_pembayaran" class="form-label">Nama Pembayaran</label>
                                    <input type="text" class="form-control" id="nama_pembayaran" name="nama_pembayaran"
                                        required placeholder="Contoh: Buku Pelajaran">
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
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran (Opsional)</label>
                                    <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id">
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                        <option value="<?php echo $ta['id']; ?>">
                                            <?php echo $ta['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                        placeholder="Masukkan Keterangan (opsional)"></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="bisa_diangsur"
                                        name="bisa_diangsur">
                                    <label class="form-check-label" for="bisa_diangsur">Bisa Diangsur</label>
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
                                    <label for="edit_nama_pembayaran" class="form-label">Nama Tarif</label>
                                    <input type="text" class="form-control" id="edit_nama_pembayaran"
                                        name="nama_pembayaran" required placeholder="Contoh: Buku Pelajaran">
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
                                    <select class="form-select" id="edit_tahun_ajaran_id" name="tahun_ajaran_id">
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                        <option value="<?php echo $ta['id']; ?>">
                                            <?php echo $ta['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_keterangan" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"
                                        placeholder="Masukkan Keterangan (opsional)"></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_bisa_diangsur"
                                        name="bisa_diangsur">
                                    <label class="form-check-label" for="edit_bisa_diangsur">Bisa Diangsur</label>
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
                            <h5 class="modal-title" id="deleteModalLabel">Hapus Data Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="deleteForm" method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" id="delete-id" name="id">
                                <p>Apakah Anda yakin ingin menghapus data tagihan ini? Tindakan ini tidak dapat
                                    dikembalikan.</p>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" form="deleteForm" class="btn btn-danger">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- App Content -->
</main>
<!-- App Main -->

<!-- Inisialisasi DataTables -->
<script>
$(document).ready(function() {
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

document.addEventListener('DOMContentLoaded', function() {

    // Handling Update
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Get data attributes from the button
            const id = button.getAttribute('data-id');
            const nama_pembayaran = button.getAttribute('data-nama_pembayaran');
            const nominal = button.getAttribute('data-nominal');
            const tahun_ajaran_id = button.getAttribute('data-tahun_ajaran_id');
            const keterangan = button.getAttribute('data-keterangan');
            const bisa_diangsur = button.getAttribute('data-bisa_diangsur') === '1';
            const status_aktif = button.getAttribute('data-status_aktif') === '1';

            // Update the modal's content.
            const modalTitle = editModal.querySelector('.modal-title');
            modalTitle.textContent = `Edit Data Tagihan: ${nama_pembayaran}`;

            // Populate the form in the modal with the data
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_pembayaran').value = nama_pembayaran;
            document.getElementById('edit_nominal').value = nominal;
            document.getElementById('edit_tahun_ajaran_id').value = tahun_ajaran_id;
            document.getElementById('edit_keterangan').value = keterangan;
            document.getElementById('edit_bisa_diangsur').checked = bisa_diangsur;
            document.getElementById('edit_status_aktif').checked = status_aktif;
        });
    }

    // Handling Delete
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            const id = button.getAttribute('data-id');
            const nama_pembayaran = button.getAttribute('data-nama_pembayaran');

            // Update the modal's content.
            const modalTitle = deleteModal.querySelector('.modal-title');
            modalTitle.textContent = `Hapus Data Pembayaran: ${nama_pembayaran}`;

            // Populate the form with the id
            const form = deleteModal.querySelector('#deleteForm');
            form.querySelector('#delete-id').value = id;
        });
    }
})
</script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>