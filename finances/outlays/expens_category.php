<?php

include __DIR__ . '/../../layouts/master.php';
$db = Database::getInstance()->getConnection();

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

    // Handle Form Submission for Deletion
    if ($action === 'delete_category') {
        $id = $_POST['id'];

        if (!empty($id)) {
            try {
                $db->beginTransaction();

                // Delete related details from detail_kategori_pengeluaran
                $stmtDetails = $db->prepare("DELETE FROM detail_kategori_pengeluaran WHERE kategori_id = :id");
                $stmtDetails->execute(['id' => $id]);

                // Delete the category from kategori_pengeluaran
                $stmtCategory = $db->prepare("DELETE FROM kategori_pengeluaran WHERE id = :id");
                $stmtCategory->execute(['id' => $id]);

                $db->commit();
                // Redirect to kategori-pengeluaran page
                echo "<script>
                    alert('Kategori dan detail terkait berhasil dihapus.');
                    window.location.href = '/pengeluaran/kategori-pengeluaran';
                </script>";
                exit();
            } catch (PDOException $e) {
                $db->rollBack();
                echo "Gagal menghapus kategori dan detail: " . $e->getMessage();
            }
        } else {
            echo "ID tidak valid!";
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

    // Delete Detail Kategori
    if ($action === 'delete_detail_category') {
        $id = $_POST['id'];

        if (!empty($id)) {
            try {
                $stmt = $db->prepare("DELETE FROM detail_kategori_pengeluaran WHERE id = :id");
                $stmt->execute([':id' => $id]);

                // Redirect 
                echo "<script>
                    alert('Detail Kategori pengeluaran berhasil dihapus.');
                    window.location.href = '/pengeluaran/kategori-pengeluaran';
                </script>";
                exit();
            } catch (PDOException $e) {
                echo "Gagal menghapus detail kategori: " . $e->getMessage();
            }
        } else {
            echo "ID tidak valid!";
        }
    }
}

// Fetch categories and their details in advance
$kategori = $db->query("SELECT k.id, k.nama_kategori, k.icon,
    GROUP_CONCAT(d.judul SEPARATOR ', ') AS detail_list
    FROM kategori_pengeluaran k
    LEFT JOIN detail_kategori_pengeluaran d ON k.id = d.kategori_id
    GROUP BY k.id")->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua detail kategori
try {
    $stmt = $db->query("SELECT dk.*, k.nama_kategori 
        FROM detail_kategori_pengeluaran dk
        INNER JOIN kategori_pengeluaran k ON dk.kategori_id = k.id");
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Gagal mengambil detail kategori: " . $e->getMessage();
}
// var_dump($kategori);

?>

<link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
<link href="../../assets/bootstrap/css/bootstrapicons-iconpicker.css" rel="stylesheet">
<style>
.container {
    margin: 150px auto;
    max-width: 640px;
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
                            expn category
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
                <!-- Kategori -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Kategori </h3>
                            <button class="btn btn-warning btn-sm ms-auto" id="add-menu" data-bs-toggle="modal"
                                data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-lg pe-1"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-0">
                                <div id="list-menu">
                                    <ul class="nav flex-column nav-pills" id="pills-tab" role="tablist"
                                        aria-orientation="vertical">
                                        <?php foreach ($kategori as $kat): ?>
                                        <li class="nav-item mb-2 d-flex align-items-center" role="presentation"
                                            data-id="<?= $kat['id'] ?>">
                                            <a class="nav-link rounded-4 flex-grow-1"
                                                id="pills-kategori-tab-<?= $kat['id'] ?>" data-bs-toggle="pill"
                                                href="javascript:void(0)" role="tab" aria-controls="pills-kategori"
                                                aria-selected="false"
                                                onclick="getDetails(<?= $kat['id'] ?>, '<?= $kat['id'] ?>')">
                                                <i class="bi <?= $kat['icon'] ?>"></i>
                                                <span class="menu-title"><?= $kat['nama_kategori'] ?></span>
                                            </a>
                                            <div class="dropdown ms-auto">
                                                <button class="btn btn-link p-0" type="button"
                                                    id="settings-<?= $kat['id'] ?>" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end"
                                                    aria-labelledby="settings-<?= $kat['id'] ?>">
                                                    <li>
                                                        <button class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#editCategoryModal"
                                                            data-id_kategori="<?= $kat['id'] ?>"
                                                            data-name="<?= $kat['nama_kategori'] ?>"
                                                            data-icon="<?= $kat['icon'] ?>">
                                                            Edit
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#deleteKatModal"
                                                            data-id="<?= $kat['id']; ?>"
                                                            data-name="<?= htmlspecialchars($kat['nama_kategori']); ?>"
                                                            data-details="<?= htmlspecialchars($kat['detail_list']); ?>">
                                                            Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Detail Kategori</h3>
                            <button type="button" class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal"
                                data-bs-target="#addDetailModal">
                                <i class="bi bi-plus-lg pe-1"></i> Tambah Data
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:8%;">#</th>
                                        <th class="">Judul</th>
                                        <th class="text-center" style="width:15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-content">
                                    <!-- Konten detail akan diisi oleh AJAX -->
                                </tbody>
                            </table>
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
                                        <label for="data_icon" class="form-label">Icon Picker</label>
                                        <!-- Create An Input Field For The Icon Picker -->
                                        <input type="text" class="form-control iconpicker" placeholder="Icon Picker"
                                            aria-label="Icone Picker" aria-describedby="basic-addon1" id="icon"
                                            name="icon" value="bi-folder">
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
                                        <label for="edit_data_icon" class="form-label">Icon Picker</label>
                                        <input type="text" class="form-control iconpicker"
                                            aria-describedby="basic-addon1" id="edit_data_icon" name="icon">
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

                <!-- Modal Konfirmasi Hapus Kategori -->
                <div class="modal fade" id="deleteKatModal" tabindex="-1" aria-labelledby="deleteKatModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteKatModalLabel">Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="deleteKatForm" method="POST">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" id="delete-id" name="id">
                                    <p>Apakah Anda yakin ingin menghapus <span id="detail-info"></span>? Semua data
                                        detail juga akan ikut terhapus.</p>
                                    <p>Tindakan ini tidak dapat dikembalikan.</p>
                                    <div id="related-details" class="mt-3">
                                        <h6>Detail Terkait:</h6>
                                        <ul id="details-list" class="list-group"></ul>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" form="deleteKatForm" class="btn btn-danger">Hapus</button>
                            </div>
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
                        <form method="POST" id="editDetailForm">
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

                <!-- Modal Konfirmasi Hapus Detail Kategori -->
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
                                <form id="deleteDetailForm" method="POST">
                                    <input type="hidden" name="action" value="delete_detail_category">
                                    <input type="hidden" id="delete-id" name="id">
                                    <p>Apakah Anda yakin ingin menghapus <span id="detail-info"></span>? Tindakan ini
                                        tidak dapat dikembalikan.</p>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" form="deleteDetailForm" class="btn btn-danger">Hapus</button>
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
document.querySelectorAll('.dropdown-item').forEach(function(button) {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id_kategori');
        const nama_kategori = this.getAttribute('data-name');
        const data_icon = this.getAttribute('data-icon');

        // Mendapatkan modal edit kategori
        const editCategoryModal = document.getElementById('editCategoryModal');

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
// Show modal with populated data
const deleteKatModal = document.getElementById('deleteKatModal');
deleteKatModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;

    // Get data attributes from the button
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const details = button.getAttribute('data-details');

    // Set modal content
    deleteKatModal.querySelector('.modal-title').textContent = `Hapus Kategori: ${name}`;
    deleteKatModal.querySelector('#delete-id').value = id;
    deleteKatModal.querySelector('#detail-info').textContent = name;

    // Populate related details in the list
    const detailsList = deleteKatModal.querySelector('#details-list');
    detailsList.innerHTML = ''; // Clear previous details

    if (details) {
        details.split(', ').forEach(function(detail) {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.textContent = detail;
            detailsList.appendChild(listItem);
        });
    } else {
        const emptyItem = document.createElement('li');
        emptyItem.className = 'list-group-item text-muted';
        emptyItem.textContent = 'Tidak ada detail terkait';
        detailsList.appendChild(emptyItem);
    }
});
</script>

<!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script> -->

<!-- Script AJAX untuk menampilkan detail berdasarkan kategori yang dipilih -->
<script>
function getDetails(kategoriId) {
    // Simpan kategori yang dipilih ke localStorage
    // localStorage.setItem('selectedKategori', kategoriId);

    // AJAX request untuk mendapatkan detail kategori
    $.ajax({
        url: '/child-expens-category',
        method: 'GET',
        data: {
            kategori_id: kategoriId
        },
        success: function(response) {
            $('#detail-content').html(response);
            // Memperbarui URL tanpa memuat ulang halaman
            history.pushState(null, '', '/pengeluaran/kategori-pengeluaran#' + kategoriId);
        },
        error: function() {
            alert('Gagal mengambil data');
        }
    });
}
// Fungsi untuk memuat kategori yang dipilih saat halaman dimuat
$(document).ready(function() {
    var selectedKategori = localStorage.getItem('selectedKategori');
    if (selectedKategori) {
        getDetails(selectedKategori);
        // Menandai kategori yang aktif
        $('#list-menu .nav-item .nav-link').removeClass('active');
        $('#list-menu .nav-item[data-id="' + selectedKategori + '"] .nav-link').addClass('active');
    }
});

// Handling Edit
const editDetailModal = document.getElementById('editDetailModal');
if (editDetailModal) {
    editDetailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-detail-id');
        const kategori_id = button.getAttribute('data-kategori-id');
        const judul = button.getAttribute('data-judul');

        // Update the modal's content.
        const modalTitle = editDetailModal.querySelector('.modal-title');
        modalTitle.textContent = `Edit Kategori: ` + judul;

        const detailIdElement = document.getElementById('edit_detail_id');
        const kategoriIdElement = document.getElementById('edit_kategori_id');
        const judulElement = document.getElementById('edit_judul');

        if (detailIdElement && kategoriIdElement && judulElement) {
            detailIdElement.value = id || '';
            judulElement.value = judul || '';

            // Pilih kategori yang sesuai
            const options = kategoriIdElement.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value == kategori_id) {
                    options[i].selected = true;
                    break;
                }
            }
        } else {
            console.error('Elemen tidak ditemukan di dalam DOM.');
        }

        console.log(`ID: ${id}, IDkat: ${kategori_id}, jud: ${judul}`);
    });
}

// Handling Delete
const deleteDetailModal = document.getElementById('deleteDetailModal');
if (deleteDetailModal) {
    deleteDetailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        const id = button.getAttribute('data-id');
        const judul = button.getAttribute('data-judul');

        // Update the modal's content.
        const modalTitle = deleteDetailModal.querySelector('.modal-title');
        modalTitle.textContent = `Delete Kategori: ` + judul;

        // Populate the form with the id
        const form = deleteDetailModal.querySelector('#deleteDetailForm');
        form.querySelector('#delete-id').value = id;

        // Update the confirmation message with category judul
        const detailInfo = deleteDetailModal.querySelector('#detail-info');
        detailInfo.textContent = judul;
    });
}
</script>

<script src="../../assets/bootstrap/js/bootstrapicon-iconpicker.js"></script>
<script>
// initialize the icon picker and done
$('.iconpicker').iconpicker({
    // customize the icon picker with the following options
    title: 'My Icon Picker',
    selected: false,
    defaultValue: false,
    placement: "bottom",
    collision: "none",
    animation: true,
    hideOnSelect: true,
    showFooter: true,
    searchInFooter: false,
    mustAccept: false,
    selectedCustomClass: "bg-primary",
    fullClassFormatter: function(e) {
        return e;
    },
    input: "input,.iconpicker-input",
    inputSearch: false,
    container: false,
    component: ".input-group-addon,.iconpicker-component",
    templates: {
        popover: '<div class="iconpicker-popover popover" role="tooltip"><div class="arrow"></div>' +
            '<div class="popover-title"></div><div class="popover-content"></div></div>',
        footer: '<div class="popover-footer"></div>',
        buttons: '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button>',
        search: '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
        iconpicker: '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
        iconpickerItem: '<a role="button" href="javascript:;" class="iconpicker-item"><i></i></a>'
    }
});
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-1VDDWMRSTH"></script>
<script>
window.dataLayer = window.dataLayer || [];

function gtag() {
    dataLayer.push(arguments);
}
gtag('js', new Date());
gtag('config', 'G-1VDDWMRSTH');
</script>
<script>
try {
    fetch(new Request("https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js", {
        method: 'HEAD',
        mode: 'no-cors'
    })).then(function(response) {
        return true;
    }).catch(function(e) {
        var carbonScript = document.createElement("script");
        carbonScript.src = "//cdn.carbonads.com/carbon.js?serve=CK7DKKQU&placement=wwwjqueryscriptnet";
        carbonScript.id = "_carbonads_js";
        document.getElementById("carbon-block").appendChild(carbonScript);
    });
} catch (error) {
    console.log(error);
}
</script>