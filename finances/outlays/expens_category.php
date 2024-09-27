<?php
include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Ambil semua kategori
try {
    $stmt = $db->query("SELECT * FROM kategori_pengeluaran");
    $kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Gagal mengambil kategori: " . $e->getMessage();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Tambah Kategori
    if ($action === 'create_category') {
        $nama_kategori = $_POST['nama_kategori'];
        $icon = $_POST['icon'];

        try {
            $stmt = $db->prepare("INSERT INTO kategori_pengeluaran (nama_kategori, icon) VALUES (:nama_kategori, :icon)");
            $stmt->execute([
                ':nama_kategori' => $nama_kategori,
                ':icon' => $icon,
            ]);

            // Redirect
            echo "<script>
                alert('Data berhasil ditambahkan.');
                window.location.href = '/pengeluaran/kategori-pengeluaran';
            </script>";
            exit();
        } catch (PDOException $e) {
            echo "Gagal menambah kategori: " . $e->getMessage();
        }
    }

    // Edit Kategori
    if ($action === 'update_category') {
        $id = $_POST['id'];
        $nama_kategori = $_POST['nama_kategori'];
        $icon = $_POST['icon'];

        try {
            $stmt = $db->prepare("UPDATE kategori_pengeluaran SET nama_kategori = :nama_kategori, icon = :icon WHERE id = :id");
            $stmt->execute([
                ':nama_kategori' => $nama_kategori,
                ':icon' => $icon,
                ':id' => $id,
            ]);

            // Redirect
            echo "<script>
                alert('Data berhasil diupdate.');
                window.location.href = '/pengeluaran/kategori-pengeluaran';
            </script>";
            exit();
        } catch (PDOException $e) {
            echo "Gagal mengedit kategori: " . $e->getMessage();
        }
    }


    // Tambah Detail Kategori
    if ($action === 'tambah_detail') {
        $kategori_id = $_POST['kategori_id'];
        $judul = $_POST['judul'];

        try {
            $stmt = $db->prepare("INSERT INTO detail_kategori_pengeluaran (kategori_id, judul) VALUES (:kategori_id, :judul)");
            $stmt->execute([
                ':kategori_id' => $kategori_id,
                ':judul' => $judul,
            ]);
            // Redirect
            echo "<script>
                alert('Berhasil menambahkan detail pengeluaran.');
                window.location.href = '/pengeluaran/kategori-pengeluaran';
            </script>";
            exit();
        } catch (PDOException $e) {
            echo "Gagal menambah detail kategori: " . $e->getMessage();
        }
    }

    // Edit Detail Kategori
    if ($action === 'update_detail') {
        $id = $_POST['id'];
        $kategori_id = $_POST['kategori_id'];
        $judul = $_POST['judul'];

        try {
            $stmt = $db->prepare("UPDATE detail_kategori_pengeluaran SET kategori_id = :kategori_id, judul = :judul WHERE id = :id");
            $stmt->execute([
                ':kategori_id' => $kategori_id,
                ':judul' => $judul,
                ':id' => $id,
            ]);
            // Redirect
            echo "<script>
                alert('Detail pengeluaran berhasil diupdate.');
                window.location.href = '/pengeluaran/kategori-pengeluaran';
            </script>";
            exit();
        } catch (PDOException $e) {
            echo "Gagal mengedit detail kategori: " . $e->getMessage();
        }
    }

}

// Hapus Kategori
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $stmt = $db->prepare("DELETE FROM kategori_pengeluaran WHERE id = :id");
        $stmt->execute([':id' => $id]);
        // Redirect
        echo "<script>
            alert('Data Kategori berhasil dihapus.');
            window.location.href = '/pengeluaran/kategori-pengeluaran';
        </script>";
        exit();
    } catch (PDOException $e) {
        echo "Gagal menghapus kategori: " . $e->getMessage();
    }
}

// Hapus Detail Kategori
if (isset($_GET['delete_detail'])) {
    $id = $_GET['delete_detail'];

    try {
        $stmt = $db->prepare("DELETE FROM detail_kategori_pengeluaran WHERE id = :id");
        $stmt->execute([':id' => $id]);
        // Redirect
        echo "<script>
            alert('Detail pengeluaran berhasil dihapus.');
            window.location.href = '/pengeluaran/kategori-pengeluaran';
        </script>";
        exit();
    } catch (PDOException $e) {
        echo "Gagal menghapus detail kategori: " . $e->getMessage();
    }
}

// Ambil semua detail kategori
try {
    $stmt = $db->query("SELECT detail_kategori_pengeluaran.*, kategori_pengeluaran.nama_kategori 
                         FROM detail_kategori_pengeluaran 
                         INNER JOIN kategori_pengeluaran ON detail_kategori_pengeluaran.kategori_id = kategori_pengeluaran.id");
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Gagal mengambil detail kategori: " . $e->getMessage();
}
// var_dump($kategori);

?>

<link rel="stylesheet" type="text/css"
    href="https://codeliro.com/demo/aplikasi-spp/public/vendors/jquery-nestable/jquery.nestable.min.css?r=1726984088?r=1726984088" />

<style>
    .nested-category {
        margin-left: 20px;
    }

    .category-actions {
        float: right;
    }

    .category-actions button {
        margin-left: 5px;
    }
</style>

<!--begin::App Main-->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Kategori Pengeluaran</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Pengeluaran
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Kategori dan Subkategori -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Kategori </h3>
                            <button class="btn btn-primary btn-sm ms-auto" id="add-menu" data-bs-toggle="modal"
                                data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus pe-1"></i> Tambah Kategori
                            </button>

                        </div>
                        <div class="card-body">

                            <div id="list-menu">
                                <ol class="dd-list">
                                    <?php foreach ($kategori as $kat): ?>
                                        <li class="dd-item" data-id="<?= $kat['id'] ?>">
                                            <div class="dd-handle">
                                                <i class="<?= $kat['icon'] ?>"></i>
                                                <span class="menu-title"><?= $kat['nama_kategori'] ?></span>
                                                <button class="btn btn-info btn-sm edit-category float-end"
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                    data-id_kategori="<?= $kat['id'] ?>"
                                                    data-name="<?= $kat['nama_kategori'] ?>"
                                                    data-icon="<?= $kat['icon'] ?>"><i class="bi bi-pencil-square"></i>
                                                </button>

                                                <a href="?delete=<?= $kat['id'] ?>"
                                                    class="btn btn-danger btn-sm float-end mx-1"
                                                    onclick="return confirm('Hapus kategori ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal Tambah Kategori -->
                <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST">
                            <input type="hidden" name="action" value="create_category">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="bi-folder">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit Kategori -->
                <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_category">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit_id" name="id">
                                    <div class="mb-3">
                                        <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                                        <input type="text" class="form-control" id="edit_nama_kategori"
                                            name="nama_kategori" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_data_icon" class="form-label">Icon</label>
                                        <input type="text" class="form-control" id="edit_data_icon" name="icon">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary" name="update">Simpan
                                        Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Bagian Detail Kategori -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Grade Level </h3>
                            <button type="button" class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal"
                                data-bs-target="#addDetailModal">
                                <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                            </button>
                        </div>
                        <div class="card-body">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kategori</th>
                                        <th>Judul</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $index => $detail): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= $detail['nama_kategori'] ?? '-' ?></td>
                                            <td><?= $detail['judul'] ?? '-' ?></td>
                                            <td>
                                                <button class="btn btn-info btn-sm edit-detail" data-bs-toggle="modal"
                                                    data-bs-target="#editDetailModal" data-id="<?= $detail['id'] ?>"
                                                    data-kategori="<?= $detail['kategori_id'] ?? '-' ?>"
                                                    data-judul="<?= $detail['judul'] ?? '-' ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-detail" data-bs-toggle="modal"
                                                    data-bs-target="#deleteDetailModal"
                                                    data-id="<?= $detail['id'] ?? '-' ?>"
                                                    data-judul="<?= $detail['judul'] ?? '-' ?>">
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

                <!-- Modal Tambah Detail -->
                <div class="modal fade" id="addDetailModal" tabindex="-1" aria-labelledby="addDetailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST">
                            <input type="hidden" name="action" value="tambah_detail">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addDetailModalLabel">Tambah Detail Kategori</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="kategori_id" class="form-label">Pilih Kategori</label>
                                        <select class="form-select" id="kategori_id" name="kategori_id">
                                            <?php foreach ($kategori as $kat): ?>
                                                <option value="<?= $kat['id'] ?>"><?= $kat['nama_kategori'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="judul" class="form-label">Judul</label>
                                        <input type="text" class="form-control" id="judul" name="judul" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary" name="tambah_detail">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit Detail -->
                <div class="modal fade" id="editDetailModal" tabindex="-1" aria-labelledby="editDetailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_detail">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editDetailModalLabel">Edit Detail Kategori</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit_detail_id" name="id">
                                    <div class="mb-3">
                                        <label for="edit_kategori_id" class="form-label">Pilih Kategori</label>
                                        <select class="form-select" id="edit_kategori_id" name="kategori_id">
                                            <?php foreach ($kategori as $kat): ?>
                                                <option value="<?= $kat['id'] ?>"><?= $kat['nama_kategori'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_judul" class="form-label">Judul</label>
                                        <input type="text" class="form-control" id="edit_judul" name="judul" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary" name="update_detail">Simpan
                                        Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus -->
                <div class="modal fade" id="deleteDetailModal" tabindex="-1" aria-labelledby="deleteDetailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteDetailModalLabel">Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" id="delete-id" name="id">
                                    <p>Apakah Anda yakin ingin menghapus <span id="detail-info"></span>? Tindakan ini
                                        tidak dapat dikembalikan.</p>
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

    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mengisi Form Edit dengan Data dari Baris yang Dipilih
        // Menangani klik pada tombol edit kategori
        document.querySelectorAll('.edit-category').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id_kategori');
                const nama_kategori = this.getAttribute('data-name');
                const data_icon = this.getAttribute('data-icon');

                // Up modal's header.
                const modalTitle = editCategoryModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Kategori: ${nama_kategori}`;

                // Memastikan elemen ada sebelum mengisi form
                if (document.getElementById('edit_id') && document.getElementById(
                    'edit_nama_kategori') && document.getElementById('edit_data_icon')) {
                    document.getElementById('edit_id').value = id || '';
                    document.getElementById('edit_nama_kategori').value = nama_kategori || '';
                    document.getElementById('edit_data_icon').value = data_icon || '';
                }
            });
        });
        // Menangani klik pada tombol edit detail kategori
        document.querySelectorAll('.edit-detail').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const kategori_id = this.getAttribute('data-kategori');
                const judul = this.getAttribute('data-judul');

                // Up modal's header.
                const modalTitle = editDetailModal.querySelector('.modal-title');
                modalTitle.textContent = `Edit Kategori: ${judul}`;

                // Memastikan elemen ada sebelum mengisi form
                if (document.getElementById('edit_detail_id') && document.getElementById(
                    'edit_kategori_id') && document.getElementById('edit_judul')) {
                    document.getElementById('edit_detail_id').value = id || '';
                    document.getElementById('edit_kategori_id').value = kategori_id || '';
                    document.getElementById('edit_judul').value = judul || '';
                }
            });
        });

        // Menghapus Data dengan Konfirmasi
        document.querySelectorAll('.delete-kategori').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const judul = this.getAttribute('data-judul');

                // Up modal's header.
                const modalTitle = deleteDetailModal.querySelector('.modal-title');
                modalTitle.textContent = `Delete Detail Kategori: ${judul}`;

                console.log(
                    `ID: ${id}, Judul: ${judul}`
                );

                // Mengisi form dengan id yang akan dihapus
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;
            });
        });
        // Konfirmasi penghapusan detail kategori
        const deleteModal = document.getElementById('deleteDetailModal');
        document.querySelectorAll('.delete-detail').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const judul = this.getAttribute('data-judul');

                // Up modal's header.
                const modalTitle = deleteDetailModal.querySelector('.modal-title');
                modalTitle.textContent = `Delete Detail Kategori: ${judul}`;

                console.log(
                    `ID: ${id}, Judul: ${judul}`
                );

                // Mengisi form dengan id yang akan dihapus
                const form = deleteModal.querySelector('#deleteForm');
                form.querySelector('#delete-id').value = id;
            });
        });

        // Menampilkan Modal Tambah dan Reset Form
        // Reset form modal tambah kategori
        const addCategoryButton = document.getElementById('add-category');
        if (addCategoryButton) {
            addCategoryButton.addEventListener('click', function () {
                const addCategoryModal = document.getElementById('addCategoryModal');
                if (addCategoryModal) {
                    addCategoryModal.querySelector('form').reset();
                }
            });
        }
        // Reset form modal tambah detail
        const addDetailButton = document.getElementById('add-detail');
        if (addDetailButton) {
            addDetailButton.addEventListener('click', function () {
                const addDetailModal = document.getElementById('addDetailModal');
                if (addDetailModal) {
                    addDetailModal.querySelector('form').reset();
                }
            });
        }
    });
</script>