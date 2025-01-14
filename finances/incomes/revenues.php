<?php
// Memulai output buffering
ob_start();

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// query untuk mengambil data dari tabel `pemasukan_dana`
$stmt = $db->prepare("SELECT 
        p.id,
        p.tanggal,
        p.deskripsi,
        p.nominal,
        p.sumber_dana,
        p.keterangan,
        ta.tahun AS tahun_ajaran,
        p.tahun_ajaran_id
    FROM pemasukan_dana p
    LEFT JOIN tahun_ajaran ta ON p.tahun_ajaran_id = ta.id
    ORDER BY p.id DESC
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($results);

// query untuk mengambil data tabel "tahun_ajaran"
$stmt = $db->prepare("SELECT id, tahun FROM tahun_ajaran ORDER BY tahun DESC");
$stmt->execute();
$tahun_ajaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Create Record
    if ($action == 'create') {
        // Mengambil data dari form
        $tanggal = $_POST['tanggal'];
        $deskripsi = !empty($_POST['deskripsi']);
        $nominal = $_POST['nominal'];
        $sumber_dana = $_POST['sumber_dana'];
        $tahun_ajaran_id = !empty($_POST['tahun_ajaran_id']) ?: null;
        $keterangan = !empty($_POST['keterangan']) ?: null;

        // Validasi input
        if (!empty($tanggal) && !empty($deskripsi) && !empty($nominal) && !empty($sumber_dana)) {
            try {
                // Siapkan query SQL
                $sql = "INSERT INTO pemasukan_dana 
                        (tanggal, deskripsi, nominal, sumber_dana, tahun_ajaran_id, keterangan) 
                        VALUES (:tanggal, :deskripsi, :nominal, :sumber_dana, :tahun_ajaran_id, :keterangan)";
                $stmt = $conn->prepare($sql);

                // Bind parameter dan eksekusi
                $stmt->execute([
                    'tanggal' => $tanggal,
                    'deskripsi' => $deskripsi,
                    'nominal' => $nominal,
                    'sumber_dana' => $sumber_dana,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'keterangan' => $keterangan
                ]);

                // Redirect untuk menghindari form resubmission
                echo "<script>
                        alert('Data pemasukan dana BOS berhasil ditambahkan.');
                        window.location.href = '/pendapatan/pemasukan-bos';
                    </script>";
                exit();
            } catch (PDOException $e) {
                echo "Gagal menyisipkan data pemasukan: " . $e->getMessage();
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }

    // Update Record
    if ($action == 'update') {
        $id = $_POST['id'];
        $tanggal = $_POST['tanggal'];
        $deskripsi = !empty($_POST['deskripsi']) ? $_POST['deskripsi'] : null;
        $nominal = $_POST['nominal'];
        $sumber_dana = $_POST['sumber_dana'];
        $tahun_ajaran_id = !empty($_POST['tahun_ajaran_id']) ? $_POST['tahun_ajaran_id'] : null;
        $keterangan = !empty($_POST['keterangan']) ? $_POST['keterangan'] : null;

        // Validasi input
        if (!empty($id) && !empty($tanggal) && !empty($deskripsi) && !empty($nominal) && !empty($sumber_dana)) {
            try {
                // Siapkan query SQL untuk update
                $sql = "UPDATE pemasukan_dana 
                        SET tanggal = :tanggal, 
                            deskripsi = :deskripsi, 
                            nominal = :nominal, 
                            sumber_dana = :sumber_dana, 
                            tahun_ajaran_id = :tahun_ajaran_id, 
                            keterangan = :keterangan 
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);

                // Bind parameter dan eksekusi
                $stmt->execute([
                    'tanggal' => $tanggal,
                    'deskripsi' => $deskripsi,
                    'nominal' => $nominal,
                    'sumber_dana' => $sumber_dana,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'keterangan' => $keterangan,
                    'id' => $id
                ]);

                // Redirect untuk menghindari form resubmission
                echo "<script>
                        alert('Data pemasukan dana BOS berhasil diperbarui.');
                        window.location.href = '/pendapatan/pemasukan-bos';
                    </script>";
                exit();
            } catch (PDOException $e) {
                echo "Gagal memperbarui data pemasukan: " . $e->getMessage();
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            try {
                // Siapkan query SQL untuk delete
                $sql = "DELETE FROM pemasukan_dana WHERE id = :id";
                $stmt = $conn->prepare($sql);

                // Bind parameter dan eksekusi
                $stmt->execute(['id' => $id]);

                // Redirect untuk menghindari form resubmission
                echo "<script>
                        alert('Data pemasukan dana BOS berhasil dihapus.');
                        window.location.href = '/pendapatan/pemasukan-bos';
                    </script>";
                exit();
            } catch (PDOException $e) {
                echo "Gagal menghapus data pemasukan: " . $e->getMessage();
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
                    <h3 class="mb-0">Pemasukan Dana Operasional Sekolah</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Rev Bos
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
                                            <th>Tanggal</th>
                                            <th>Deskripsi</th>
                                            <th>Nominal</th>
                                            <th>Sumber Dana</th>
                                            <th>Ajaran</th>
                                            <th>Keterangan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $index => $row): ?>
                                            <tr>
                                                <td><?= $index + 1; ?></td>
                                                <td><?= htmlspecialchars(string: date('d-m-Y', strtotime($row['tanggal']))); ?>
                                                </td>
                                                <td><?= $row['deskripsi'] ?? '-'; ?></td>
                                                <td><?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                                                <td><?= $row['sumber_dana']; ?></td>
                                                <td><?= $row['tahun_ajaran'] ?? '-'; ?></td>
                                                <td><?= $row['keterangan'] ?? '-'; ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#editModal" data-id="<?= $row['id']; ?>"
                                                        data-tanggal="<?= $row['tanggal']; ?>"
                                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi']); ?>"
                                                        data-nominal="<?= $row['nominal']; ?>"
                                                        data-sumber_dana="<?= htmlspecialchars($row['sumber_dana']); ?>"
                                                        data-tahun_ajaran_id="<?= $row['tahun_ajaran_id'] ?? '-'; ?>"
                                                        data-keterangan="<?= htmlspecialchars($row['keterangan'] ?? '-'); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal" data-id="<?= $row['id']; ?>"
                                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi']); ?>">
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

            <!-- /.modal-dialog create New Record -->
            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">Tambah Pemasukan Dana</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"
                                        placeholder="Masukkan Deskripsi" required></textarea>
                                </div>
                                <div class="input-group mb-3">
                                    <label for="nominal" class="form-label">Nominal</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="nominal" name="nominal" required
                                            aria-label="Jumlah (ke rupiah)" placeholder="jumlah dana" />
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                    <input type="text" class="form-control" id="sumber_dana" name="sumber_dana" required
                                        placeholder="Contoh: APBD, BOS Pusat, Hibah, Subsidi, dll.">
                                </div>
                                <div class="mb-3">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran (opsional)</label>
                                    <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id">
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                            <option value="<?php echo htmlspecialchars($ta['id']); ?>">
                                                <?php echo htmlspecialchars($ta['tahun']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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

            <!-- /.modal-dialog update New Record -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Pemasukan Dana</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" id="edit_id">

                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"
                                        placeholder="Masukkan Deskripsi"></textarea>
                                </div>
                                <div class="input-group mb-3">
                                    <label for="nominal" class="form-label">Nominal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control" id="edit_nominal" name="nominal"
                                            required />
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                    <input type="text" class="form-control" id="edit_sumber_dana" name="sumber_dana"
                                        required placeholder="Contoh: APBD, BOS Pusat, Hibah, Subsidi, dll." />
                                </div>
                                <div class="mb-3">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran (opsional)</label>
                                    <select class="form-select" id="edit_tahun_ajaran_id" name="tahun_ajaran_id">
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                            <option value="<?php echo htmlspecialchars($ta['id']); ?>">
                                                <?php echo htmlspecialchars($ta['tahun']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"
                                        placeholder="Masukkan Keterangan (opsional)"></textarea>
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

            <!-- /.modal-dialog Delete New Record -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Hapus Pemasukan Dana</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.</p>
                            <form id="deleteForm" method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" id="delete-id">
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

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons JS (Opsional, jika menggunakan tombol) -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

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

    // format mata uang rupiah 
    const nominal = document.getElementById('nominal');
    nominal.addEventListener('keyup', function (e) {
        // nominal.value = formatRupiah(this.value);
        const numValue = parseRupiah(this.value);
        nominal.value = formatRupiah(numValue);
    });
    const edit_nominal = document.getElementById('edit_nominal');
    edit_nominal.addEventListener('keyup', function (e) {
        // Menghapus karakter non-numeric sebelum memformat
        const numericValue = parseRupiah(this.value);
        edit_nominal.value = formatRupiah(numericValue);
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Handling Update
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                // Mengambil nilai dari tombol yang ditekan
                const id = button.getAttribute('data-id');
                const tanggal = button.getAttribute('data-tanggal');
                const deskripsi = button.getAttribute('data-deskripsi');
                const nominal = button.getAttribute('data-nominal');
                const sumber_dana = button.getAttribute('data-sumber_dana');
                const tahun_ajaran_id = button.getAttribute('data-tahun_ajaran_id');
                const keterangan = button.getAttribute('data-keterangan');

                // Update the modal's content.
                const modalTitle = editModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Pemasukan Dana BOS : ${deskripsi}`;

                // Mengisi modal form dengan data yang didapat
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_tanggal').value = tanggal;
                document.getElementById('edit_deskripsi').value = deskripsi;
                document.getElementById('edit_nominal').value = formatRupiah(nominal);
                document.getElementById('edit_sumber_dana').value = sumber_dana;
                document.getElementById('edit_keterangan').value = keterangan;
                document.getElementById('edit_tahun_ajaran_id').value = tahun_ajaran_id;

            });
        }

        // Handling Delete
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                const id = button.getAttribute('data-id');
                const deskripsi = button.getAttribute('data-deskripsi');

                // Update the modal's content.
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Pemasukan Dana BOS : ${deskripsi}`;

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