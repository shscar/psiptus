<?php
// PHP Section

// Connect to the database
$db = Database::getInstance()->getConnection();

// Ambil data siswa dari database untuk digunakan di Select2
$stmt = $db->query("SELECT nis, nama_lengkap FROM siswa WHERE status = 'Aktif'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Input Siswa dalam Modal</title>

    <!-- Include Select2 and AdminLTE CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0-beta2/css/adminlte.min.css">
    <!-- Sesuaikan path jika perlu -->
</head>

<body>

    <!-- Trigger button to open modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
        Open Form Modal
    </button>

    <!-- Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel">Form Input Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="studentForm">
                        <!-- Dropdown untuk memilih siswa dengan Select2 -->
                        <div class="mb-3 row">
                            <label for="student_name" class="col-sm-3 col-form-label">Nama Siswa</label>
                            <div class="col-sm-9">
                                <select id="student_name" class="form-control select2" style="width: 100%;"
                                    name="student_name">
                                    <option value="">Pilih Nama Siswa</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['nis']; ?>"><?= $student['nama_lengkap']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Input untuk menampilkan NIS setelah pemilihan siswa -->
                        <div class="mb-3 row">
                            <label for="nis_siswa" class="col-sm-3 col-form-label">NIS Siswa</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nis_siswa" name="nis_siswa" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery, Select2, dan AdminLTE JS di akhir body -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/4.0.0-beta2/js/adminlte.min.js"></script>
    <!-- Sesuaikan path jika perlu -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sesuaikan jika perlu -->

    <script>
    $(document).ready(function() {
        // Inisialisasi Select2 saat modal ditampilkan
        $('#studentModal').on('shown.bs.modal', function() {
            $('#student_name').select2({
                placeholder: 'Pilih Nama Siswa',
                dropdownParent: $('#studentModal') // Buat dropdown tampil di dalam modal
            });
        });

        // Saat siswa dipilih, tampilkan NIS secara otomatis
        $('#student_name').on('select2:select', function(e) {
            const nis = e.params.data.id; // Ambil NIS dari id opsi yang dipilih
            $('#nis_siswa').val(nis); // Isi NIS di field input NIS
        });
    });
    </script>

</body>

</html>