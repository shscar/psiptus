<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style untuk menampilkan autocomplete */
        .autocomplete-items {
            border: 1px solid #ddd;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            position: absolute;
            background-color: white;
            max-height: 200px;
            overflow-y: auto;
        }

        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
        }

        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Pencarian Siswa</h2>
        <div class="mb-3">
            <label for="nama_siswa" class="form-label">Nama Siswa</label>
            <input type="text" class="form-control" id="nama_siswa" placeholder="Masukkan nama siswa"
                autocomplete="off">
            <div id="autocomplete-list" class="autocomplete-items"></div>
        </div>

        <div id="spp_details" class="mt-4">
            <!-- Tempat untuk menampilkan detail SPP setelah siswa dipilih -->
        </div>
    </div>

    <script>
        // Data siswa
        const siswaData = [{
            id: 1,
            nama: 'Andi',
            kelas: 'X RPL 1',
            sppBelumBayar: [500000, 400000]
        },
        {
            id: 2,
            nama: 'Budi',
            kelas: 'XI RPL 1',
            sppBelumBayar: [600000]
        },
        {
            id: 3,
            nama: 'Cici',
            kelas: 'XII RPL 2',
            sppBelumBayar: []
        },
        {
            id: 4,
            nama: 'Samporna',
            kelas: 'XII RPL 2',
            sppBelumBayar: []
        },
        {
            id: 5,
            nama: 'Saputro',
            kelas: 'XII RPL 2',
            sppBelumBayar: []
        },
        {
            id: 6,
            nama: 'Samsu',
            kelas: 'XII RPL 2',
            sppBelumBayar: []
        },
        {
            id: 7,
            nama: 'Samsul',
            kelas: 'XII RPL 2',
            sppBelumBayar: []
        }
        ];

        // Fungsi untuk memulai autocomplete
        function autocomplete(input) {
            input.addEventListener('input', function () {
                let inputValue = this.value;
                closeAutocomplete();

                if (!inputValue) {
                    return false;
                }

                let autocompleteList = document.getElementById('autocomplete-list');
                let foundItems = siswaData.filter(siswa => siswa.nama.toLowerCase().includes(inputValue
                    .toLowerCase()));

                foundItems.forEach(siswa => {
                    let itemDiv = document.createElement('div');
                    itemDiv.innerHTML = siswa.nama;
                    itemDiv.addEventListener('click', function () {
                        input.value = siswa.nama;
                        displaySPPDetails(siswa);
                        closeAutocomplete();
                    });
                    autocompleteList.appendChild(itemDiv);
                });
            });
        }

        // Fungsi untuk menampilkan detail SPP siswa yang dipilih
        function displaySPPDetails(siswa) {
            const sppDetailsDiv = document.getElementById('spp_details');
            sppDetailsDiv.innerHTML = `
        <h3>Data Siswa</h3>
        <p>Nama: ${siswa.nama}</p>
        <p>Kelas: ${siswa.kelas}</p>
        <h4>SPP Belum Dibayar</h4>
        ${siswa.sppBelumBayar.length > 0 ?
                    `<ul>${siswa.sppBelumBayar.map(spp => `<li>Rp ${new Intl.NumberFormat('id-ID').format(spp)}</li>`).join('')}</ul>`
                    : '<p>Semua SPP sudah dibayar.</p>'}
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