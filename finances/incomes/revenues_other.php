<?php
// Memulai output buffering
ob_start(); 

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

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
                    <h3 class="mb-0">Pendapatan lain-lain</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            other rev
                        </li>
                    </ol>
                </div>
            </div>
        </div>
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
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editModal" 
                                                    data-id="<?= $row['id']; ?>"
                                                    data-nama_pendapatan="<?= $row['nama_pendapatan']; ?>"
                                                    data-kategori="<?= $row['kategori']; ?>"
                                                    data-periode="<?= $row['periode']; ?>"
                                                    data-sumber="<?= $row['sumber']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
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
            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">Tambah Jenis</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="form-group mb-3">
                                    <label for="nama_pendapatan" class="form-label">Nama Pendapatan</label>
                                    <input type="text" class="form-control" id="nama_pendapatan" name="nama_pendapatan" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="Internal">Internal</option>
                                        <option value="External">External</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="sumber" class="form-label">Sumber</label>
                                    <input type="text" class="form-control" id="sumber" name="sumber">
                                </div>
                                <div class="form-group mb-3">
                                    <input type="checkbox" class="form-check-input" id="use_priode" checked>
                                    <label class="form-check-label" for="use_priode">Gunakan Periode</label>
                                </div>
                                <div id="priodeSelect">
                                    <select class="form-select" id="priode" name="periode">
                                        <option value="Tahun Ajaran">Tahun Ajaran</option>
                                        <option value="Bulan">Bulan</option>
                                        <option value="Tahun">Tahun</option>
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
                            <h5 class="modal-title" id="editModalLabel">Edit Jenis Pendapatan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="form-group mb-3">
                                    <label for="edit_nama_pendapatan" class="form-label">Nama Pendapatan</label>
                                    <input type="text" class="form-control" id="edit_nama_pendapatan" name="nama_pendapatan" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="edit_kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="edit_kategori" name="kategori" required>
                                        <option value="Internal">Internal</option>
                                        <option value="External">External</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="edit_sumber" class="form-label">Sumber</label>
                                    <input type="text" class="form-control" id="edit_sumber" name="sumber">
                                </div>
                                <div class="form-group">
                                    <div class="mb-2">
                                        <input type="checkbox" class="form-check-input" id="use_edit_priode" name="use_edit_priode">
                                        <label class="form-check-label" for="use_edit_priode">Gunakan Periode</label>
                                    </div>
                                    <div id="priodeSelect2" class="mb-3">
                                        <select class="form-select" id="edit_periode" name="periode">
                                            <option value="Bulan">Bulan</option>
                                            <option value="Tahun">Tahun</option>
                                            <option value="Tahun Ajaran">Tahun Ajaran</option>
                                        </select>
                                    </div>
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

document.getElementById('use_priode').addEventListener('change', function() {
    const priodeSelect = document.getElementById('priodeSelect');
    priodeSelect.style.display = this.checked ? 'block' : 'none';
});

document.addEventListener('DOMContentLoaded', function() {
    // Handling Update
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Get data attributes from the button
            const id = button.getAttribute('data-id');
            const nama_pendapatan = button.getAttribute('data-nama_pendapatan');
            const kategori = button.getAttribute('data-kategori');
            const periode = button.getAttribute('data-periode');
            const sumber = button.getAttribute('data-sumber');

            // Update the modal's content.
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_pendapatan').value = nama_pendapatan;
            document.getElementById('edit_kategori').value = kategori;
            document.getElementById('edit_sumber').value = sumber;

            // Handling the "Gunakan Periode" checkbox and periode dropdown
            const useEditPeriode = document.getElementById('use_edit_priode');
            const priodeSelect = document.getElementById('priodeSelect2');
            const editPeriode = document.getElementById('edit_periode');

            if (periode) {
                useEditPeriode.checked = true;
                priodeSelect.style.display = 'block';
                editPeriode.value = periode;
            } else {
                useEditPeriode.checked = false;
                priodeSelect.style.display = 'none';
            }

            // Toggle visibility of the periode dropdown when the checkbox changes
            useEditPeriode.addEventListener('change', function() {
                priodeSelect.style.display = this.checked ? 'block' : 'none';
            });
        });
    }

    // Handling Delete
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-bs-id');
            const nama_pendapatan = button.getAttribute('data-nama_pendapatan');

            // Update the modal's content.
            const modalTitle = deleteModal.querySelector('.modal-title');
            modalTitle.textContent = `Hapus Data Tagihan: ${nama_pendapatan}`;

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