<?php
// Memulai output buffering
ob_start();

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Menyiapkan dan mengeksekusi query untuk mengambil data dari tabel siswa_pembayaran_lainnya
// $stmt = $db->prepare("SELECT 
//         pl.id,
//         pl.nama_pembayaran,
//         pl.bisa_diangsur,
//         pl.nominal,
//         pl.keterangan,
//         pl.status_aktif,
//         ta.tahun AS tahun_ajaran,
//         pl.tahun_ajaran_id
//     FROM siswa_pembayaran_lainnya pl
//     LEFT JOIN tahun_ajaran ta ON pl.tahun_ajaran_id = ta.id
//     ORDER BY pl.id DESC
// ");
// $stmt->execute();
// $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
// SQL query untuk mengambil data dari siswa_pembayaran_lainnya dan semua kelas terkait
$stmt = $db->prepare("SELECT 
    pl.id AS pembayaran_id,
    pl.nama_pembayaran,
    pl.bisa_diangsur,
    pl.nominal,
    pl.keterangan,
    pl.status_aktif,
    ta.tahun AS tahun_ajaran,
    pl.tahun_ajaran_id,
    spk.id AS pembayaran_kelas_id,
    k.nama_kelas
FROM siswa_pembayaran_lainnya pl
LEFT JOIN tahun_ajaran ta ON pl.tahun_ajaran_id = ta.id
LEFT JOIN siswa_pembayaran_lainnya_kelas spk ON pl.id = spk.siswa_pembayaran_lainnya_id
LEFT JOIN kelas k ON spk.kelas_id = k.id
ORDER BY pl.id DESC, k.nama_kelas
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengelompokkan data berdasarkan pembayaran_id
$pembayaranData = [];
foreach ($results as $row) {
    $pembayaranId = $row['pembayaran_id']; // Menggunakan alias baru
    if (!isset($pembayaranData[$pembayaranId])) {
        $pembayaranData[$pembayaranId] = [
            'pembayaran_id' => $row['pembayaran_id'],
            'nama_pembayaran' => $row['nama_pembayaran'],
            'bisa_diangsur' => $row['bisa_diangsur'],
            'nominal' => $row['nominal'],
            'keterangan' => $row['keterangan'],
            'status_aktif' => $row['status_aktif'],
            'tahun_ajaran' => $row['tahun_ajaran'],
            'tahun_ajaran_id' => $row['tahun_ajaran_id'],
            'pembayaran_kelas_ids' => [],
            'kelas' => [],
        ];
    }
    if ($row['pembayaran_kelas_id']) {
        $pembayaranData[$pembayaranId]['pembayaran_kelas_ids'][] = $row['pembayaran_kelas_id'];
    }
    if ($row['nama_kelas']) {
        $pembayaranData[$pembayaranId]['kelas'][] = $row['nama_kelas'];
    }
}

// echo '<pre>';
// print_r($pembayaranData);
// echo '</pre>';

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
        $kelas = $_POST['kelas']; // Array of selected classes

        // Validasi input
        if (!empty($nama_pembayaran) && !empty($nominal) && !empty($kelas)) {
            // // Persiapkan query SQL
            // $sql = "INSERT INTO siswa_pembayaran_lainnya (nama_pembayaran, bisa_diangsur, nominal, tahun_ajaran_id, keterangan, status_aktif) 
            //     VALUES (:nama_pembayaran, :bisa_diangsur, :nominal, :tahun_ajaran_id, :keterangan, :status_aktif)";
            // $stmt = $conn->prepare($sql);
            // if (
            //     $stmt->execute([
            //         'nama_pembayaran' => $nama_pembayaran,
            //         'bisa_diangsur' => $bisa_diangsur,
            //         'nominal' => $nominal,
            //         'tahun_ajaran_id' => $tahun_ajaran_id,
            //         'keterangan' => $keterangan,
            //         'status_aktif' => $status_aktif
            //     ])
            // ) {
            //     // Redirect untuk menghindari pengiriman form ulang
            //     echo "<script>
            //         alert('Data pembayaran berhasil ditambahkan.');
            //         window.location.href = '/pendapatan/tagihan-lain-siswa';
            //     </script>";
            //     exit();
            // } else {
            //     echo "Gagal Menyisipkan Data Tagihan!";
            // }

            try {
                // Prepare SQL query to insert into tarif_spp table
                $sql = "INSERT INTO siswa_pembayaran_lainnya (nama_pembayaran, bisa_diangsur, nominal, tahun_ajaran_id, keterangan, status_aktif) 
                    VALUES (:nama_pembayaran, :bisa_diangsur, :nominal, :tahun_ajaran_id, :keterangan, :status_aktif)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'nama_pembayaran' => $nama_pembayaran,
                    'bisa_diangsur' => $bisa_diangsur,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'keterangan' => $keterangan,
                    'status_aktif' => $status_aktif
                ]);
                $siswa_pembayaran_lainnya_id = $db->lastInsertId(); // Get the inserted siswa_pembayaran_lainnya_id

                // Insert into siswa_pembayaran_lainnya_kelas table for each selected class
                $sqlKelas = "INSERT INTO siswa_pembayaran_lainnya_kelas (siswa_pembayaran_lainnya_id, kelas_id) VALUES (:siswa_pembayaran_lainnya_id, :kelas_id)";
                $stmtKelas = $db->prepare($sqlKelas);
                foreach ($kelas as $kelas_id) {
                    $stmtKelas->execute([
                        'siswa_pembayaran_lainnya_id' => $siswa_pembayaran_lainnya_id,
                        'kelas_id' => $kelas_id
                    ]);
                }

                // Commit transaction
                $db->commit();

                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data tarif berhasil ditambahkan.');
                        window.location.href = '/pendapatan/tagihan-lain-siswa';
                    </script>";
                exit();
            } catch (Exception $e) {
                // Rollback transaction if an error occurs
                $db->rollBack();
                echo "Gagal menyisipkan Data Tagihan: " . $e->getMessage();
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

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            try {
                // Delete from tarif_spp_kelas table first (if exists)

                $sqlKelas = "DELETE FROM siswa_pembayaran_lainnya_kelas WHERE siswa_pembayaran_lainnya_id = :id";
                $stmtKelas = $db->prepare($sqlKelas);
                $stmtKelas->execute(['id' => $id]);

                $sqlTarif = "DELETE FROM siswa_pembayaran_lainnya WHERE id = :id";
                $stmtTarif = $db->prepare($sqlTarif);
                $stmtTarif->execute(['id' => $id]);

                // Commit transaction
                $db->commit();

                // Redirect after successful deletion
                echo "<script>
                        alert('Data tarif berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-lain-siswa';
                    </script>";
                exit();
            } catch (Exception $e) {
                // Rollback transaction if an error occurs
                $db->rollBack();
                echo "Gagal menghapus Data Tagihan: " . $e->getMessage();
            }
        } else {
            echo "ID tidak valid!";
        }
    }

    // add datakelas
    if ($action == "addkelas") {
        $id = $_POST['id'];
        $kelas_ids = $_POST['kelas_id'];

        if (!empty($id) && !empty($kelas_ids) && is_array($kelas_ids)) {
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM siswa_pembayaran_lainnya_kelas WHERE siswa_pembayaran_lainnya_id = :siswa_pembayaran_lainnya_id AND kelas_id = :kelas_id");
            $insertStmt = $db->prepare("INSERT INTO siswa_pembayaran_lainnya_kelas (siswa_pembayaran_lainnya_id, kelas_id) VALUES (:siswa_pembayaran_lainnya_id, :kelas_id)");

            foreach ($kelas_ids as $kelas_id) {
                $checkStmt->execute([
                    ':siswa_pembayaran_lainnya_id' => $id,
                    ':kelas_id' => $kelas_id
                ]);
                $exists = $checkStmt->fetchColumn();

                if ($exists > 0) {
                    echo "<script>alert('Data sudah ada');</script>";
                } else {
                    $insertStmt->execute([
                        ':siswa_pembayaran_lainnya_id' => $id,
                        ':kelas_id' => $kelas_id
                    ]);
                    echo "<script>
                            alert('Proses selesai. Data berhasil ditambahkan.');
                            window.location.href = '/pendapatan/tagihan-lain-siswa';
                        </script>";
                    exit();
                }
            }
        } else {
            echo "<script>alert('Data tidak lengkap.');</script>";
        }
    }

    // delete datakelas
    if ($action == 'deletekelas') {
        $tarifPLKelasId = $_POST['tarifPLKelasId'] ?? null;

        if (!empty($tarifPLKelasId)) {
            try {
                // Mulai transaksi
                $db->beginTransaction();

                // Hapus data dari tabel siswa_pembayaran_lainnya_kelas berdasarkan ID
                $deleteStmt = $db->prepare("DELETE FROM siswa_pembayaran_lainnya_kelas WHERE id = :id");
                $deleteStmt->execute([':id' => $tarifPLKelasId]);

                if ($deleteStmt->rowCount() > 0) {
                    $db->commit(); // Commit jika berhasil
                    echo "<script>
                        alert('Kelas berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-lain-siswa';
                    </script>";
                    exit();
                } else {
                    $db->rollBack(); // Rollback jika data tidak ditemukan
                    $deleteMessage = "Data tidak ditemukan.";
                }
            } catch (Exception $e) {
                $db->rollBack(); // Rollback jika terjadi kesalahan
                $deleteMessage = "Kesalahan server: " . $e->getMessage();
            }
        } else {
            $deleteMessage = "Parameter tidak ditemukan.";
        }
    }

}

$stmt = $db->prepare("SELECT id, nama_kelas FROM kelas");
$stmt->execute();
$kelasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengakhiri output buffering
ob_end_flush();
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
<style>
    td {
        padding: 20px;
        background: #eaeaea;
        max-width: 400px;
        margin: 50px auto;
    }

    .list-circle {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    #kelas_tags {
        width: 100% !important;
        border-radius: 0.375rem;
        /* padding: 0.375rem 0.75rem; */
    }

    .select2-container .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.150rem;
        min-height: 2.5rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        color: #fff;
        border-radius: 0.25rem;
        margin-top: 0.25rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        background-color: red;
        color: #fff;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background-color: #0b5ed7;
    }
</style>

<script>
    const pembayaranData = <?php echo json_encode($pembayaranData); ?>;
    const kelasData = <?php echo json_encode($kelasData); ?>;
</script>

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
                            oth student bil
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
                                            <th>Nama Pembayaran</th>
                                            <th>Diangsur</th>
                                            <th>Nominal</th>
                                            <th>Ajaran</th>
                                            <th>Status</th>
                                            <th>Kelas</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($pembayaranData as $row): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $row['nama_pembayaran'] ?? '-'; ?></td>
                                                <td><?= $row['bisa_diangsur'] ? 'Ya' : 'Tidak'; ?></td>
                                                <td>Rp. <?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                                                <td><?= $row['tahun_ajaran'] ?? '-'; ?></td>
                                                <td class="text-center">
                                                    <?= $row['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['kelas'])): ?>
                                                        <ul class="list-circle m-0">
                                                            <?php foreach ($row['kelas'] as $kelas): ?>
                                                                <li><?= $kelas; ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        -- Tidak ada kelas --
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-warning btn-sm edit-button"
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        data-id="<?= $row['pembayaran_id'] ?? '-'; ?>"
                                                        data-nama_pembayaran="<?= $row['nama_pembayaran'] ?? '-'; ?>"
                                                        data-nominal="<?= $row['nominal'] ?? '-'; ?>"
                                                        data-tahun_ajaran_id="<?= $row['tahun_ajaran_id'] ?? '-'; ?>"
                                                        data-keterangan="<?= $row['keterangan'] ?? '-'; ?>"
                                                        data-bisa_diangsur="<?= $row['bisa_diangsur'] ?? '-'; ?>"
                                                        data-status_aktif="<?= $row['status_aktif'] ?? '-'; ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-id="<?= $row['pembayaran_id'] ?? '-'; ?>"
                                                        data-nama_pembayaran="<?= $row['nama_pembayaran'] ?? '-'; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#editkelasModal"
                                                        data-bs-id="<?= $row['pembayaran_id']; ?>"
                                                        data-nama_pembayaran="<?= $row['nama_pembayaran']; ?>">
                                                        <i class="bi bi-list-stars"></i>
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
                                        <input type="number" class="form-control" id="nominal" name="nominal" required
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

                                <div class="form-group mb-3">
                                    <label for="kelas_tags" class="form-label">Kelas</label>
                                    <select class="form-select" id="kelas_tags" name="kelas[]" multiple="multiple"
                                        data-placeholder="Pilih Kelas">
                                        <!-- Options will be dynamically added here by JavaScript -->
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
                                        <input type="number" class="form-control" id="edit_nominal" name="nominal"
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

            <!-- Modal for Editing Classes -->
            <div class="modal fade" id="editkelasModal" tabindex="-1" aria-labelledby="editkelasModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editkelasModalLabel">Edit Kelas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="addkelas">
                                <input type="hidden" id="editkelas-id" name="id">

                                <div class="card card-outline card-info">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <select class="form-select" id="itemSelect" name="kelas_id[]"
                                                style="width: 87%;"></select>
                                            <button type="submit" class="btn btn-success" id="addItemButton">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <!-- Daftar Item Kelas -->
                            <div class="mt-3" id="itemList">
                                <!-- Item akan ditambahkan di sini -->
                            </div>

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

    // Memetakan data kelas untuk Select2
    const validKelas = kelasData.map(function (item) {
        return {
            id: item.id,
            text: item.nama_kelas
        };
    });
    $('#createModal').on('shown.bs.modal', function () {
        $('#kelas_tags').select2({
            data: validKelas,
            width: '100%',
            dropdownParent: $('#createModal')
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Handling Update
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
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
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                const id = button.getAttribute('data-id');
                const nama_pembayaran = button.getAttribute('data-nama_pembayaran');

                // Update the modal's content.
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Hapus Data Pembayaran: ${nama_pembayaran}`;

                // Populate the form with the id
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;
                console.log(
                    `ID: ${id}, A: ${nama_pembayaran}`
                );
            });
        }
    })
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editkelasModal = document.getElementById('editkelasModal');
        const updatedItemsDataInput = document.getElementById('updatedItemsData');

        // Initialize Select2 with dropdownParent
        $('#itemSelect').select2({
            placeholder: 'Pilih Kelas',
            data: kelasData.map(kelas => ({
                id: kelas.id,
                text: kelas.nama_kelas
            })),
            dropdownParent: $('#editkelasModal') // Mengaitkan dropdown dengan modal
        });

        if (editkelasModal) {
            editkelasModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const nama_pembayaran = button.getAttribute('data-nama_pembayaran');

                // Update the modal's title
                const modalTitle = editkelasModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Data kelas: ${nama_pembayaran}`;

                // Populate the form with the id
                const form = editkelasModal;
                form.querySelector('#editkelas-id').value = id;

                // Clear the previous list items and set the hidden field to track updated data
                $('#itemList').empty();
                // updatedItemsDataInput.value = '';

                // const associatedClasses = pembayaranData[id]?.kelas || [];
                // console.log(`Data Kelas Terkait:`, associatedClasses);

                // Retrieve and display associated classes (if any)
                if (pembayaranData[id] && pembayaranData[id].kelas.length > 0) {
                    pembayaranData[id].kelas.forEach((kelas, index) => {
                        const listItem = createListItem(kelas, pembayaranData[id]
                            .pembayaran_kelas_ids[
                            index]);
                        $('#itemList').append(listItem);
                    });
                }

                // console.log(
                //     `ID: ${id}, Tarif: ${nama_pembayaran}, Kelas: ${pembayaranData[id]?.kelas || 'No classes'}`);

            });
        }

        // Helper function to create list items
        function createListItem(itemText, tarifPLKelasId) {
            const div = document.createElement('div');
            div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'p-2', 'border', 'mb-2',
                'rounded', 'bg-light');
            div.innerHTML = `
                <span>${itemText}</span>
                <button type="button" class="btn btn-danger btn-sm delete-button" data-id="${tarifPLKelasId}" data-kelas="${itemText}">
                    Delete
                </button>
            `;

            div.querySelector('.delete-button').addEventListener('click', function () {
                const tarifPLKelasId = this.getAttribute('data-id');
                const form = document.querySelector('form');

                if (confirm(`Apakah Anda yakin ingin menghapus kelas: "${itemText}"?`)) {
                    // Tambahkan input hidden untuk `tarifPLKelasId`
                    let hiddenInput = form.querySelector('input[name="tarifPLKelasId"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'tarifPLKelasId';
                        form.appendChild(hiddenInput);
                    }
                    hiddenInput.value = tarifPLKelasId;

                    // Tambahkan input hidden untuk `action` jika belum ada
                    let actionInput = form.querySelector('input[name="action"]');
                    if (!actionInput) {
                        actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        form.appendChild(actionInput);
                    }
                    actionInput.value = 'deletekelas';

                    // Submit form
                    form.submit();
                }
            });

            return div;
        }


        // Update the hidden input with the current list of items
        // function updateItemsData() {
        //     const items = [];
        //     $('#itemList .d-flex').each(function () {
        //         const itemText = $(this).find('span').text();
        //         items.push(itemText);
        //     });
        //     updatedItemsDataInput.value = JSON.stringify(items); // Store updated items in JSON format
        // }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>