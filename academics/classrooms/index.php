<?php
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Query untuk mengambil data kelas
$stmt = $db->prepare("SELECT 
        k.id,
        k.nama_kelas,
        tk.tingkat,
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
    WHERE k.wali_kelas_id IS NOT NULL AND k.tingkat_kelas_id IS NOT NULL
    ORDER BY k.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status = 'Aktif';

// Query untuk mengambil data tingkat_kelas beserta tahun ajaran yang terkait
$stmt = $db->prepare("SELECT tk.id, tk.tingkat, ta.tahun 
                       FROM tingkat_kelas tk 
                       JOIN tahun_ajaran ta ON tk.tahun_ajaran_id = ta.id 
                       WHERE ta.status = :status");
$stmt->bindParam(':status', $status);
$stmt->execute();
$tingkat_kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil data guru yang aktif dari tabel guru_staff
$stmt = $db->prepare("SELECT id, nama_lengkap, nip 
        FROM guru_staff 
        WHERE status_kerja = :status");
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->execute();
$guru_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($results);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        $nama_kelas = $_POST['nama_kelas'];
        $jurusan = $_POST['jurusan'] ?? null;
        $tingkat_kelas_id = $_POST['tingkat_kelas_id'];
        $wali_kelas_id = $_POST['wali_kelas_id'] ?? null;
        $jumlah_siswa = $_POST['jumlah_siswa'] ?? null;
        $gedung = $_POST['gedung'] ?? null;
        $keterangan = $_POST['keterangan'] ?? null;

        // Validate required fields
        if (!empty($nama_kelas) && !empty($tingkat_kelas_id)) {
            $sql = "INSERT INTO kelas (nama_kelas, jurusan, tingkat_kelas_id, wali_kelas_id, jumlah_siswa, gedung, keterangan, created_at, updated_at) 
                    VALUES (:nama_kelas, :jurusan, :tingkat_kelas_id, :wali_kelas_id, :jumlah_siswa, :gedung, :keterangan, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $params = [
                'nama_kelas' => $nama_kelas,
                'jurusan' => $jurusan,
                'tingkat_kelas_id' => $tingkat_kelas_id,
                'wali_kelas_id' => $wali_kelas_id,
                'jumlah_siswa' => $jumlah_siswa,
                'gedung' => $gedung,
                'keterangan' => $keterangan
            ];

            // Execute and handle result
            if ($stmt->execute($params)) {
                echo "<script>
                        alert('Data kelas berhasil ditambahkan.');
                        window.location.href = '/kelas';
                      </script>";
                exit();
            } else {
                echo "Failed to insert record!";
            }
        } else {
            echo "Please fill in all required fields!";
        }
    }

}

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
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
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
                                        <!-- test 2 table -->
                                        <!-- <td><?= $row['tingkat'] . ' ' . $row['nama_kelas'] ?? '-'; ?></td> -->

                                        <td><?= $row['nama_kelas'] ?? '-'; ?></td>
                                        <td><?= $row['guru_staff'] ?? '-'; ?></td>
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

            <!-- /.modal-dialog create -->
            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">Tambah Kelas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="nama_kelas" class="form-label">Nama Kelas</label>
                                    <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required
                                        placeholder="Masukkan Nama Kelas">
                                </div>
                                <div class="mb-3">
                                    <label for="jurusan" class="form-label">Jurusan</label>
                                    <input type="text" class="form-control" id="jurusan" name="jurusan"
                                        placeholder="Masukkan Jurusan">
                                </div>
                                <div class="mb-3">
                                    <label for="tingkat_kelas_id" class="form-label">Tingkat Kelas</label>
                                    <select class="form-select" id="tingkat_kelas_id" name="tingkat_kelas_id" required>
                                        <option value="">Pilih Tingkat Kelas</option>
                                        <?php foreach ($tingkat_kelas as $tk): ?>
                                        <option value="<?php echo $tk['id']; ?>">
                                            <?php echo $tk['tingkat'] . ' - ' . $tk['tahun']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                                    <select class="form-select" id="wali_kelas_id" name="wali_kelas_id">
                                        <option value="">Pilih Wali Kelas</option>
                                        <?php foreach ($guru_staff as $guru): ?>
                                        <option value="<?php echo $guru['id']; ?>">
                                            <?php echo $guru['nama_lengkap'] . ' (' . $guru['nip'] . ')'; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa</label>
                                    <input type="number" class="form-control" id="jumlah_siswa" name="jumlah_siswa"
                                        placeholder="Masukkan Jumlah Siswa (opsional)">
                                </div>
                                <div class="mb-3">
                                    <label for="gedung" class="form-label">Gedung</label>
                                    <input type="text" class="form-control" id="gedung" name="gedung"
                                        placeholder="Masukkan Gedung (opsional)">
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