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
            echo '<tr class="row">
                    <td class="col-md-1 text-center">' . ($index + 1) . '</td>
                    <td class="col-md-9">' . htmlspecialchars($detail['judul']) . '</td>
                    <td class="col-md-2 text-center">
                        <button class="btn btn-warning btn-sm edit-detail" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editDetailModal" 
                            data-detail-id="' . $detail['id'] . '" 
                            data-kategori-id="' . $detail['kategori_id'] . '" 
                            data-judul="' . $detail['judul'] . '">
                                <i class="bi bi-pencil-square"></i>
                        </button>

                        <button class="btn btn-danger btn-sm delete-detail" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteDetailModal" 
                            data-id="' . $detail['id'] . '"
                            data-judul="' . $detail['judul'] . '">
                                <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            ';
        }
        echo "<script>
            // Handle klik pada tombol edit detail kategori

            // function handleEditDetailButtons() {
                document.querySelectorAll('.edit-detail').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const id = this.getAttribute('data-detail-id');
                        const kategori_id = this.getAttribute('data-kategori-id');
                        const judul = this.getAttribute('data-judul');

                        // Mendapatkan modal edit kategori
                        const editDetailModal = document.getElementById('editDetailModal');

                        // Up modal's header.
                        const modalTitle = editDetailModal.querySelector('.modal-title');
                        modalTitle.textContent = `Edit Kategori: ` + judul;

                        // Memastikan elemen ada sebelum mengisi form
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
                    });
                });
            // }


            // Handling Delete
            const deleteDetailModal = document.getElementById('deleteDetailModal');
            if (deleteDetailModal) {
                deleteDetailModal.addEventListener('show.bs.modal', function (event) {
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
        </script>";
    } else {
        echo '<tr><td colspan="4">Data tidak tersedia</td></tr>';
    }
    exit;
}