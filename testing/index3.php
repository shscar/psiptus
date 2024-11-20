<?php
$db = Database::getInstance()->getConnection();

// PHP to prepare and fetch data
$stmt = $db->prepare("
    SELECT 
        t.id,
        t.nama_tarif,
        t.nominal,
        t.deskripsi,
        t.status_aktif,
        ta.tahun AS tahun_ajaran,
        t.tahun_ajaran_id,
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
    $tarifId = $row['id'];
    if (!isset($tarifData[$tarifId])) {
        $tarifData[$tarifId] = [
            'id' => $row['id'],
            'nama_tarif' => $row['nama_tarif'],
            'nominal' => $row['nominal'],
            'deskripsi' => $row['deskripsi'],
            'status_aktif' => $row['status_aktif'],
            'tahun_ajaran' => $row['tahun_ajaran'],
            'tahun_ajaran_id' => $row['tahun_ajaran_id'],
            'kelas' => [],
        ];
    }
    if ($row['nama_kelas']) {
        $tarifData[$tarifId]['kelas'][] = $row['nama_kelas'];
    }
}
?>

<!-- Output the data as a JSON array for JavaScript to use -->
<script>
    const tarifData = <?php echo json_encode($tarifData); ?>;
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Input Example</title>
    <!-- AdminLTE 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">

</head>

<body>
    <!-- Button to open modal -->
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editkelasModal"
        data-id="<?= $tarif['id']; ?>" data-nama_tarif="<?= $tarif['nama_tarif']; ?>">
        <s class="bi bi-list-stars"></s>saasa
    </button>

    <!-- JavaScript to handle the modal -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModal = document.getElementById('editkelasModal');

            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const tarifId = button.getAttribute('data-id');
                    const itemListContainer = document.getElementById('itemList');
                    const itemInput = document.getElementById('itemInput');

                    // Clear previous class list items
                    itemListContainer.innerHTML = '';

                    // Populate existing classes for the selected tarif ID
                    if (tarifData[tarifId] && tarifData[tarifId].kelas.length > 0) {
                        tarifData[tarifId].kelas.forEach(kelas => {
                            addClassItem(kelas);
                        });
                    } else {
                        itemListContainer.innerHTML = '<p class="text-muted">No classes associated.</p>';
                    }

                    // Update the hidden field with the selected tarif ID
                    document.getElementById('edit_kelas_id').value = tarifId;

                    // Function to add a class item to the list
                    function addClassItem(kelas) {
                        const listItem = document.createElement('div');
                        listItem.classList.add('d-flex', 'justify-content-between', 'align-items-center',
                            'p-2',
                            'border', 'mb-2', 'rounded', 'bg-light');

                        const className = document.createElement('span');
                        className.textContent = kelas;

                        const deleteButton = document.createElement('button');
                        deleteButton.classList.add('btn', 'btn-dark', 'btn-sm', 'delete-button');
                        deleteButton.textContent = 'Delete';
                        deleteButton.onclick = () => listItem.remove();

                        listItem.appendChild(className);
                        listItem.appendChild(deleteButton);
                        itemListContainer.appendChild(listItem);
                    }

                    // Add new class item from the input
                    document.getElementById('addItemButton').onclick = function () {
                        const newItem = itemInput.value.trim();
                        if (newItem) {
                            addClassItem(newItem);
                            itemInput.value = ''; // Clear the input field
                        }
                    };

                    console.log(
                        `ID: ${tarifId}, Kelas: ${tarifData[tarifId]?.kelas || 'No classes'}`
                    );
                });
            }
        });
    </script>

    <!-- AdminLTE 4 and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
</body>

</html>