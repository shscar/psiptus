<?php
// Memulai output buffering
ob_start();

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// SQL query to join tables and get all associated class names for each tarif_spp record
$stmt = $db->prepare("
    SELECT 
        t.id AS tarif_spp_id,
        t.nama_tarif,
        t.nominal,
        t.deskripsi,
        t.status_aktif,
        ta.tahun AS tahun_ajaran,
        t.tahun_ajaran_id,
        ts_k.id AS tarif_spp_kelas_id,
        k.nama_kelas
    FROM tarif_spp t
    LEFT JOIN tahun_ajaran ta ON t.tahun_ajaran_id = ta.id
    LEFT JOIN tarif_spp_kelas ts_k ON t.id = ts_k.tarif_spp_id
    LEFT JOIN kelas k ON ts_k.kelas_id = k.id
    ORDER BY t.id DESC, k.nama_kelas
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group data by tarif_spp ID to handle multiple classes per tarif
$tarifData = [];
foreach ($results as $row) {
    $tarifId = $row['tarif_spp_id']; // Menggunakan alias baru
    if (!isset($tarifData[$tarifId])) {
        $tarifData[$tarifId] = [
            'tarif_spp_id' => $row['tarif_spp_id'],
            'nama_tarif' => $row['nama_tarif'],
            'nominal' => $row['nominal'],
            'deskripsi' => $row['deskripsi'],
            'status_aktif' => $row['status_aktif'],
            'tahun_ajaran' => $row['tahun_ajaran'],
            'tahun_ajaran_id' => $row['tahun_ajaran_id'],
            'tarif_spp_kelas_ids' => [],
            'kelas' => [],
        ];
    }
    if ($row['tarif_spp_kelas_id']) {
        $tarifData[$tarifId]['tarif_spp_kelas_ids'][] = $row['tarif_spp_kelas_id'];
    }
    if ($row['nama_kelas']) {
        $tarifData[$tarifId]['kelas'][] = $row['nama_kelas'];
    }
}

// Query for the "tahun_ajaran" table
$stmt = $db->prepare("SELECT id, tahun FROM tahun_ajaran ORDER BY tahun DESC");
$stmt->execute();
$tahun_ajaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        $kelas = $_POST['kelas']; // Array of selected classes

        // Validate input
        if (!empty($nama_tarif) && !empty($nominal) && !empty($tahun_ajaran_id) && !empty($kelas)) {
            // Begin transaction
            $conn->beginTransaction();

            try {
                // Prepare SQL query to insert into tarif_spp table
                $sql = "INSERT INTO tarif_spp (nama_tarif, nominal, tahun_ajaran_id, deskripsi, status_aktif) 
                        VALUES (:nama_tarif, :nominal, :tahun_ajaran_id, :deskripsi, :status_aktif)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'nama_tarif' => $nama_tarif,
                    'nominal' => $nominal,
                    'tahun_ajaran_id' => $tahun_ajaran_id,
                    'deskripsi' => $deskripsi,
                    'status_aktif' => $status_aktif
                ]);
                $tarif_spp_id = $conn->lastInsertId(); // Get the inserted tarif_spp_id

                // Insert into tarif_spp_kelas table for each selected class
                $sqlKelas = "INSERT INTO tarif_spp_kelas (tarif_spp_id, kelas_id) VALUES (:tarif_spp_id, :kelas_id)";
                $stmtKelas = $conn->prepare($sqlKelas);
                foreach ($kelas as $kelas_id) {
                    $stmtKelas->execute([
                        'tarif_spp_id' => $tarif_spp_id,
                        'kelas_id' => $kelas_id
                    ]);
                }

                // Commit transaction
                $conn->commit();

                // Redirect to avoid form resubmission
                echo "<script>
                        alert('Data tarif berhasil ditambahkan.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
                    </script>";
                exit();
            } catch (Exception $e) {
                // Rollback transaction if an error occurs
                $conn->rollBack();
                echo "Gagal menyisipkan Data Tagihan: " . $e->getMessage();
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
            $stmt = $db->prepare($sql);

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
                echo "Gagal memperbarui Data Tagihan!";
            }
        } else {
            echo "Silakan isi semua bidang yang wajib diisi!";
        }
    }

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];

        if (!empty($id)) {
            // Begin transaction
            $conn->beginTransaction();

            try {
                // Delete from tarif_spp_kelas table first (if exists)
                $sqlKelas = "DELETE FROM tarif_spp_kelas WHERE tarif_spp_id = :id";
                $stmtKelas = $conn->prepare($sqlKelas);
                $stmtKelas->execute(['id' => $id]);

                // Then, delete from tarif_spp table
                $sqlTarif = "DELETE FROM tarif_spp WHERE id = :id";
                $stmtTarif = $conn->prepare($sqlTarif);
                $stmtTarif->execute(['id' => $id]);

                // Commit transaction
                $conn->commit();

                // Redirect after successful deletion
                echo "<script>
                        alert('Data tarif berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
                    </script>";
                exit();
            } catch (Exception $e) {
                // Rollback transaction if an error occurs
                $conn->rollBack();
                echo "Gagal menghapus Data Tagihan: " . $e->getMessage();
            }
        } else {
            echo "ID is required!";
        }
    }

    // add datakelas
    if ($action == "addkelas") {
        $id = $_POST['id'];
        $kelas_ids = $_POST['kelas_id'];

        if (!empty($id) && !empty($kelas_ids) && is_array($kelas_ids)) {
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM tarif_spp_kelas WHERE tarif_spp_id = :tarif_spp_id AND kelas_id = :kelas_id");
            $insertStmt = $db->prepare("INSERT INTO tarif_spp_kelas (tarif_spp_id, kelas_id) VALUES (:tarif_spp_id, :kelas_id)");

            foreach ($kelas_ids as $kelas_id) {
                $checkStmt->execute([
                    ':tarif_spp_id' => $id,
                    ':kelas_id' => $kelas_id
                ]);
                $exists = $checkStmt->fetchColumn();

                if ($exists > 0) {
                    echo "<script>alert('Data sudah ada');</script>";
                } else {
                    $insertStmt->execute([
                        ':tarif_spp_id' => $id,
                        ':kelas_id' => $kelas_id
                    ]);
                    echo "<script>
                            alert('Proses selesai. Data berhasil ditambahkan.');
                            window.location.href = '/pendapatan/tagihan-spp-siswa';
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
        $tarifSppKelasId = $_POST['tarifSppKelasId'] ?? null;

        if (!empty($tarifSppKelasId)) {
            try {
                // Mulai transaksi
                $db->beginTransaction();

                // Hapus data dari tabel tarif_spp_kelas berdasarkan ID
                $deleteStmt = $db->prepare("DELETE FROM tarif_spp_kelas WHERE id = :id");
                $deleteStmt->execute([':id' => $tarifSppKelasId]);

                if ($deleteStmt->rowCount() > 0) {
                    $db->commit(); // Commit jika berhasil
                    echo "<script>
                        alert('Kelas berhasil dihapus.');
                        window.location.href = '/pendapatan/tagihan-spp-siswa';
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

<!-- Output the data as a JSON array for JavaScript to use -->
<script>
    const tarifData = <?php echo json_encode($tarifData); ?>;
    const kelasData = <?php echo json_encode($kelasData); ?>;
</script>

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
        padding: 0.375rem 0.75rem;
    }

    .select2-container .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem;
        min-height: 2.5rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        color: #fff;
        border-radius: 0.25rem;
        margin-top: 0.25rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background-color: #0b5ed7;
    }
</style>

<!-- App Main -->
<main class="app-main">
    <!-- App Content Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Tagihan SPP Siswa

                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle rounded-5"
                                    data-bs-toggle="dropdown" aria-expanded="false">?</button>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Update Kelas
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Create
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Edit
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-check2 me-2"></i>
                                        Aksi Delete
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-dash me-2"></i>
                                        Export
                                    </li>
                                    <li class="dropdown-item">
                                        <i class="bi bi-dash me-2"></i>
                                        Import
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            spp_student bil
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
                            <?php if (!empty($tarifData)): ?>
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Tarif</th>
                                            <th>Nominal</th>
                                            <th>Tahun Ajaran</th>
                                            <th>Kelas</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($tarifData as $tarif):
                                            ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= $tarif['nama_tarif']; ?></td>
                                                <td>Rp. <?= number_format($tarif['nominal'], 2, ',', '.'); ?></td>
                                                <td><?= $tarif['tahun_ajaran']; ?></td>
                                                <td>
                                                    <?php if (!empty($tarif['kelas'])): ?>
                                                        <ul class="list-circle m-0">
                                                            <?php foreach ($tarif['kelas'] as $kelas): ?>
                                                                <li><?= $kelas; ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        -- Tidak ada kelas --
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= $tarif['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-link p-0" type="button"
                                                            id="settings-<?= $kat['id'] ?>" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end"
                                                            aria-labelledby="settings-<?= $kat['id'] ?>">
                                                            <li>
                                                                <button class="btn btn-primary btn-sm m-1" style="width:95%"
                                                                    data-bs-toggle="modal" data-bs-target="#editkelasModal"
                                                                    data-bs-id="<?= $tarif['tarif_spp_id']; ?>"
                                                                    data-nama_tarif="<?= $tarif['nama_tarif']; ?>">
                                                                    <i class="bi bi-list-stars"></i>
                                                                    Edit kelas
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="btn btn-warning btn-sm m-1" style="width:95%"
                                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                                    data-id="<?= $tarif['tarif_spp_id']; ?>"
                                                                    data-nama_tarif="<?= $tarif['nama_tarif']; ?>"
                                                                    data-nominal="<?= $tarif['nominal']; ?>"
                                                                    data-tahun_ajaran_id="<?= $tarif['tahun_ajaran_id']; ?>"
                                                                    data-deskripsi="<?= $tarif['deskripsi']; ?>"
                                                                    data-status_aktif="<?= $tarif['status_aktif']; ?>"
                                                                    data-kelas='<?= json_encode($tarif['kelas']); ?>'>
                                                                    <i class="bi bi-pencil"></i>
                                                                    Edit
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="btn btn-danger btn-sm m-1" style="width:95%"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                    data-bs-id="<?= $tarif['tarif_spp_id']; ?>"
                                                                    data-nama_tarif="<?= $tarif['nama_tarif']; ?>">
                                                                    <i class="bi bi-trash"></i>
                                                                    Delete
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
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
                            <form id="createForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                <div class="form-group mb-3">
                                    <label for="nama_tarif" class="form-label">Nama Tarif</label>
                                    <input type="text" class="form-control" id="nama_tarif" name="nama_tarif" required
                                        placeholder="SPP Bulan September">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="nominal" class="form-label">Nominal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control" id="nominal" name="nominal" required
                                            aria-label="Jumlah (ke rupiah)" />
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                                    <select class="form-select" id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        <?php foreach ($tahun_ajaran as $ta): ?>
                                            <option value="<?php echo $ta['id']; ?>"><?php echo $ta['tahun']; ?></option>
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
                                <div class="form-group mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                        placeholder="Masukkan Deskripsi (opsional)"></textarea>
                                </div>
                                <div class="form-group mb-3 form-check">
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
                                            <button type="submit" class="btn btn-success"
                                                id="addItemButton">Add</button>
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
                            <h5 class="modal-title" id="deleteModalLabel">Hapus Tagihan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.</p>
                            <p>Daftar Kelas:</p>
                            <ul class="class-list"></ul> <!-- Placeholder for class list -->
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

<script>
    // Inisialisasi DataTables
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

    // format mata uang rupiah 
    const nominal = document.getElementById('nominal');
    nominal.addEventListener('keyup', function (e) {
        nominal.value = formatRupiah(this.value);
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
                document.getElementById('edit_nominal').value = formatRupiah(nominal);
                document.getElementById('edit_tahun_ajaran_id').value = tahun_ajaran_id;
                document.getElementById('edit_deskripsi').value = deskripsi;
                document.getElementById('edit_status_aktif').checked = status_aktif;

                // Men-debug untuk memastikan nilai diambil dengan benar
                // console.log(
                //     `ID: ${id}, A: ${tahun_ajaran_id}`
                // );

            });
        }

        // Handling Deleteconst deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const nama_tarif = button.getAttribute('data-nama_tarif');

                // Update the modal's title
                const modalTitle = deleteModal.querySelector('.modal-title');
                modalTitle.textContent = `Hapus Data Tagihan: ${nama_tarif}`;

                // Populate the form with the id
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;

                // Get the list of classes for the selected tarif
                const classListContainer = deleteModal.querySelector('.class-list');
                classListContainer.innerHTML = ''; // Clear previous content

                // Retrieve and display associated classes
                if (tarifData[id] && tarifData[id].kelas.length > 0) {
                    tarifData[id].kelas.forEach(kelas => {
                        const listItem = document.createElement('li');
                        listItem.textContent = kelas;
                        classListContainer.appendChild(listItem);
                    });
                } else {
                    const noClassItem = document.createElement('li');
                    noClassItem.textContent = 'Tidak ada kelas terkait';
                    classListContainer.appendChild(noClassItem);
                }

                // Debugging output
                // console.log(
                //     `ID: ${id}, Tarif: ${nama_tarif}, Kelas: ${tarifData[id]?.kelas || 'No classes'}`);
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
            dropdownParent: $('#editkelasModal')
        });

        if (editkelasModal) {
            editkelasModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const nama_tarif = button.getAttribute('data-nama_tarif');

                // Update the modal's title
                const modalTitle = editkelasModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Data kelas: ${nama_tarif}`;

                // Populate the form with the id
                const form = editkelasModal;
                form.querySelector('#editkelas-id').value = id;

                // Clear the previous list items and set the hidden field to track updated data
                $('#itemList').empty();
                // updatedItemsDataInput.value = '';

                // Retrieve and display associated classes (if any)
                if (tarifData[id] && tarifData[id].kelas.length > 0) {
                    tarifData[id].kelas.forEach((kelas, index) => {
                        const listItem = createListItem(kelas, tarifData[id].tarif_spp_kelas_ids[
                            index]);
                        $('#itemList').append(listItem);
                    });
                }

            });
        }

        function createListItem(itemText, tarifSppKelasId) {
            const div = document.createElement('div');
            div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'p-2', 'border', 'mb-2',
                'rounded', 'bg-light');
            div.innerHTML = `
                <span>${itemText}</span>
                <button type="button" class="btn btn-danger btn-sm delete-button" data-id="${tarifSppKelasId}" data-kelas="${itemText}">
                    Delete
                </button>
            `;

            div.querySelector('.delete-button').addEventListener('click', function () {
                const tarifSppKelasId = this.getAttribute('data-id');
                const form = document.querySelector('form');

                if (confirm(`Apakah Anda yakin ingin menghapus kelas: "${itemText}"?`)) {
                    // Tambahkan input hidden untuk `tarifSppKelasId`
                    let hiddenInput = form.querySelector('input[name="tarifSppKelasId"]');
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'tarifSppKelasId';
                        form.appendChild(hiddenInput);
                    }
                    hiddenInput.value = tarifSppKelasId;

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

    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

<!-- DataTables CSS/JS Dependencies -->
<link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>