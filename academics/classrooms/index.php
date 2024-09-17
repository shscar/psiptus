<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />

<!-- DataTables Buttons CSS (Opsional, jika menggunakan tombol) -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

<?php
include __DIR__ . '/../../layouts/master.php';

$db = Database::getInstance();

$sql = "SELECT 
            k.id,
            k.nama_kelas,
            k.jurusan,
            k.jumlah_siswa,
            k.gedung,
            k.keterangan,
            g.nama_lengkap AS guru_staff,
            t.tahun AS tahun_ajaran,
            CONCAT(tk.tingkat, ' ', k.nama_kelas) AS kelas
        FROM kelas k
        LEFT JOIN guru_staff g ON k.wali_kelas_id = g.id
        LEFT JOIN tingkat_kelas tk ON k.tingkat_kelas_id = tk.id
        LEFT JOIN tahun_ajaran t ON tk.tahun_ajaran_id = t.id
        -- WHERE k.wali_kelas_id IS NOT NULL AND k.tingkat_kelas_id IS NOT NULL
        ORDER BY k.id DESC
    ";

$results = $db->query($sql);
// var_dump($results);
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
                    <h3 class="card-title">Data Siswa</h3>
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
                                        <th>Gedung</th>
                                        <th>Kelas</th>
                                        <th>Wakel</th>
                                        <th>Jumlah Siswa/i</th>
                                        <th>Jurusan</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $index => $row): ?>
                                    <tr>
                                        <td><?= $row['gedung'] ?? '-'; ?></td>
                                        <td><?= $row['tingkat'] . ' ' . $row['nama_kelas'] ?? '-'; ?></td>
                                        <td><?= $row['wali_kelas'] ?? '-'; ?></td>
                                        <td><?= $row['jumlah_siswa'] ?? '-'; ?></td>
                                        <td><?= $row['jurusan'] ?? '-'; ?></td>
                                        <td><?= $row['tahun_ajaran'] ?? '-'; ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="<?= $row['id'] ?? '-'; ?>"
                                                data-nama_kelas="<?= $row['nama_kelas'] ?? '-'; ?>"
                                                data-jurusan="<?= $row['jurusan'] ?? '-'; ?>"
                                                data-jumlah_siswa="<?= $row['jumlah_siswa'] ?? '-'; ?>"
                                                data-gedung="<?= $row['gedung'] ?? '-'; ?>"
                                                data-keterangan="<?= $row['keterangan'] ?? '-'; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal" data-bs-id="<?= $row['id'] ?? '-'; ?>">
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