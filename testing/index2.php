<?php
$db = Database::getInstance()->getConnection();


// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_item') {
        $tarifSppKelasId = $_POST['tarif_spp_kelas_id'] ?? null;

        if ($tarifSppKelasId) {
            try {
                // Delete specific item from `tarif_spp_kelas`
                $stmt = $db->prepare('DELETE FROM tarif_spp_kelas WHERE id = :id');
                $stmt->bindParam(':id', $tarifSppKelasId, PDO::PARAM_INT);
                $stmt->execute();

                echo json_encode(['status' => 'success', 'message' => 'Data deleted successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID provided.']);
        }
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <!-- Trigger Modal -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editkelasModal" data-bs-id="1"
            data-nama_tarif="Tarif SPP 2023">Edit</button>

        <!-- Modal -->
        <div class="modal fade" id="editkelasModal" tabindex="-1" aria-labelledby="editkelasModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editkelasModalLabel">Edit Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editkelas-id">

                        <!-- List Items -->
                        <div id="itemList" class="mt-3"></div>

                        <!-- Add Item -->
                        <input type="text" id="itemInput" class="form-control my-3" placeholder="Add new item">
                        <button id="addItemButton" class="btn btn-dark">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const editkelasModal = document.getElementById('editkelasModal');
        const itemList = $('#itemList');

        if (editkelasModal) {
            editkelasModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const nama_tarif = button.getAttribute('data-nama_tarif');

                $('#editkelas-id').val(id);
                $('#itemList').empty();

                // Fetch associated items from the server (replace with real AJAX call)
                const dummyItems = [{
                        id: 101,
                        name: 'Kelas A'
                    },
                    {
                        id: 102,
                        name: 'Kelas B'
                    },
                ];

                dummyItems.forEach(item => {
                    const listItem = createListItem(item.id, item.name);
                    itemList.append(listItem);
                });
            });

            // Add item (to be implemented if needed)
            $('#addItemButton').click(function() {
                const itemText = $('#itemInput').val().trim();
                if (itemText) {
                    const listItem = createListItem(null, itemText);
                    itemList.append(listItem);
                    $('#itemInput').val('');
                }
            });

            // Delete item
            $('#itemList').on('click', '.delete-button', function() {
                const tarifSppKelasId = $(this).data('id');
                const listItem = $(this).closest('div');

                if (tarifSppKelasId) {
                    $.post('', {
                        action: 'delete_item',
                        tarif_spp_kelas_id: tarifSppKelasId
                    }, function(response) {
                        const data = JSON.parse(response);
                        if (data.status === 'success') {
                            alert(data.message);
                            listItem.remove();
                        } else {
                            alert(data.message);
                        }
                    });
                }
            });
        }

        function createListItem(id, name) {
            return `<div class="d-flex justify-content-between align-items-center p-2 border mb-2 rounded bg-light">
                            <span>${name}</span>
                            <button class="btn btn-dark btn-sm delete-button" data-id="${id}">Delete</button>
                        </div>`;
        }
    });
    </script>
</body>

</html>



<!-- // var_dump($results);

// echo '
<pre>';
// print_r($combinedData);
// // print_r($results['pengeluaran_id']);
// echo '</pre>'; -->