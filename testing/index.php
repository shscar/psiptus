<?php
// PHP Section

// Connect to the database
$db = Database::getInstance()->getConnection();

// Fungsi untuk mengambil data siswa dari tabel 'siswa'
function getSiswaData($db) {
    $stmt = $db->prepare("SELECT nis, nama_lengkap AS nama FROM siswa");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mengambil data siswa untuk digunakan di JavaScript
$siswaData = getSiswaData($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autocomplete Siswa</title>
    <style>
        /* Styling untuk autocomplete list */
        #autocomplete-list {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            z-index: 1000;
            background: #fff;
        }
        #autocomplete-list div {
            padding: 8px;
            cursor: pointer;
        }
        #autocomplete-list div:hover {
            background-color: #e9e9e9;
        }
    </style>
</head>
<body>

<!-- Input untuk autocomplete nama siswa -->
<label for="nama_siswa">Cari Nama Siswa:</label>
<input type="text" id="nama_siswa" name="nama_siswa" autocomplete="off">
<div id="autocomplete-list"></div>

<!-- Tempat untuk menampilkan detail NIS siswa -->
<div id="siswa_details"></div>

<script>
    // Data siswa dari PHP
    const siswaData = <?php echo json_encode($siswaData); ?>;

    // Fungsi untuk memulai autocomplete
    function autocomplete(input) {
        input.addEventListener('input', function () {
            let inputValue = this.value;
            closeAutocomplete();

            if (!inputValue) {
                return false;
            }

            let autocompleteList = document.getElementById('autocomplete-list');
            let foundItems = siswaData.filter(siswa => siswa.nama.toLowerCase().includes(inputValue.toLowerCase()));

            foundItems.forEach(siswa => {
                let itemDiv = document.createElement('div');
                itemDiv.innerHTML = siswa.nama;
                itemDiv.addEventListener('click', function () {
                    input.value = siswa.nama;
                    displaySiswaDetails(siswa);
                    closeAutocomplete();
                });
                autocompleteList.appendChild(itemDiv);
            });
        });
    }

    // Fungsi untuk menampilkan detail NIS siswa yang dipilih
    function displaySiswaDetails(siswa) {
        const siswaDetailsDiv = document.getElementById('siswa_details');
        siswaDetailsDiv.innerHTML = `
            <h3>Data Siswa</h3>
            <p>Nama: ${siswa.nama}</p>
            <p>NIS: ${siswa.nis}</p>
        `;
    }

    // Fungsi untuk menutup hasil autocomplete
    function closeAutocomplete() {
        let items = document.getElementById('autocomplete-list');
        if (items) {
            items.innerHTML = '';
        }
    }

    // Inisialisasi autocomplete pada input
    autocomplete(document.getElementById('nama_siswa'));
</script>

</body>
</html>
