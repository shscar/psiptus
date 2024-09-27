<?php
$db = Database::getInstance()->getConnection();

// Jika ada request AJAX untuk mengambil detail
if (isset($_GET['kategori_id'])) {
    $kategori_id = $_GET['kategori_id'];

    // Query untuk mendapatkan detail berdasarkan kategori_id
    $query = $db->prepare("SELECT * FROM detail_kategori_pengeluaran WHERE kategori_id = ?");
    $query->execute([$kategori_id]);
    $details = $query->fetchAll(PDO::FETCH_ASSOC);

    // Tampilkan data dalam format tabel
    if ($details) {
        foreach ($details as $index => $detail) {
            echo '<tr class="row">';
            echo '<td class="col-md-1 text-center">' . ($index + 1) . '</td>';
            echo '<td class="col-md-9">' . htmlspecialchars($detail['judul']) . '</td>';
            echo '<td class="col-md-2 text-center">';
            echo '<button class="btn btn-warning btn-sm edit-detail" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editDetailModal" 
                    data-detail-id="' . $detail['id'] . '" 
                    data-detail-kategori="' . $detail['kategori_id'] . '" 
                    data-judul="' . $detail['judul'] . '">
                        <i class="bi bi-pencil-square"></i>
                </button>';

            echo '<button class="btn btn-danger btn-sm delete-detail" data-bs-toggle="modal" data-bs-target="#deleteDetailModal" data-id="' . $detail['id'] . '"><i class="bi bi-trash"></i></button>';
            echo '</td>';
            echo '</tr>';
        }
        echo "<script>
            // Handle klik pada tombol edit detail kategori
            document.querySelectorAll('.edit-detail').forEach(function (button) {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-detail-id');
                    const kategori_id = this.getAttribute('data-detail-kategori');
                    const judul = this.getAttribute('data-judul');

                    // Mendapatkan modal edit kategori
                    const editDetailModal = document.getElementById('editDetailModal');

                    // Up modal's header.
                    const modalTitle = editDetailModal.querySelector('.modal-title');
                    modalTitle.textContent = `Edit Kategori: ${judul}`;

                    // Memastikan elemen ada sebelum mengisi form
                    const detailIdElement = document.getElementById('edit_detail_id');
                    const kategoriIdElement = document.getElementById('edit_kategori_id');
                    const judulElement = document.getElementById('edit_judul');

                    if (detailIdElement && kategoriIdElement && judulElement) {
                        detailIdElement.value = id || '';
                        kategoriIdElement.value = kategori_id || '';
                        judulElement.value = judul || '';
                    } else {
                        console.error('Elemen tidak ditemukan di dalam DOM.');
                    }
                });
            });
        </script>";
    } else {
        echo '<tr><td colspan="4">Data tidak tersedia</td></tr>';
    }
    exit;
}