<?php
// Memulai output buffering
ob_start(); 

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Query to fetch data from the `jenis_dana_pemasukan_lain` table
$stmt = $db->prepare("SELECT 
        j.id,
        j.nama_pendapatan,
        j.kategori,
        j.periode,
        j.sumber,
        j.created_at,
        j.updated_at
    FROM jenis_dana_pemasukan_lain j
    ORDER BY j.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengakhiri output buffering
ob_end_flush();
?>


<!-- App Main -->
<main class="app-main">
    <!-- App Content Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Jenis Pembayaran</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Jenis
                        </li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!-- Content Header -->
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
                                            <th>Nama Pendapatan</th>
                                            <th>Kategori</th>
                                            <th>Gunakan Periode</th>
                                            <th>Sumber</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= $row['nama_pendapatan']; ?></td>
                                            <td><?= $row['kategori']; ?></td>
                                            <td><?= $row['periode']; ?></td>
                                            <td><?= $row['sumber'] ?? '-'; ?></td>
                                            <td class="text-center">
                                                <!-- Edit Button -->
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editModal" 
                                                    data-id="<?= $row['id']; ?>"
                                                    data-nama_pendapatan="<?= $row['nama_pendapatan']; ?>"
                                                    data-kategori="<?= $row['kategori']; ?>"
                                                    data-periode="<?= $row['periode']; ?>"
                                                    data-sumber="<?= $row['sumber']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Delete Button -->
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal" 
                                                    data-bs-id="<?= $row['id']; ?>" 
                                                    data-nama_pendapatan="<?= $row['nama_pendapatan']; ?>">
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
                            <h5 class="modal-title" id="createModalLabel">Tambah Jenis</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="input-grup mb-3">
                                    <label for="nama_pendapatan" class="form-label">Nama Pendapatan</label>
                                    <input type="text" class="form-control" id="nama_pendapatan" name="nama_pendapatan" required>
                                </div>
                                <div class="input-grup mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <input type="text" class="form-control" id="kategori" name="kategori" required>
                                </div>
                                <div class="input-grup mb-3">
                                    <label for="sumber" class="form-label">Sumber</label>
                                    <input type="text" class="form-control" id="sumber" name="sumber" required>
                                </div>
                                <div class="input-grup mb-3">
                                    <input type="checkbox" class="form-check-input" id="priode" checked>
                                    <label class="form-check-label" for="priode">Gunakan Priode</label>
                                    <select class="form-select" id="priode" name="priode" required>
                                        <option value="">Pilih Tahun Ajaran</option>
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


            // Men-debug untuk memastikan nilai diambil dengan benar
            // console.log(
            //     `ID: ${id}, Tahun Ajaran ID: ${tahun_ajaran_id}`
            // );

            // Men-debug untuk memastikan apakah option yang benar terpilih
            console.log(`Tahun Ajaran yang terpilih: ${edit_tahun_ajaran_id.value}`);
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

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>