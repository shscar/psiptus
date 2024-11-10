<?php
$db = Database::getInstance()->getConnection();

// Assuming you are using PDO or a similar DB library
$query = "SELECT id, nama_kelas FROM kelas";
$stmt = $db->prepare($query);
$stmt->execute();
$kelasData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Output the data as a JSON array for JavaScript to use -->
<script>
const kelasData = <?php echo json_encode($kelasData); ?>;
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
    <style>
    /* Styling dasar untuk input Select2 */
    #kelas_tags {
        width: 100% !important;
        /* Pastikan input Select2 menggunakan lebar penuh */
        border-radius: 0.375rem;
        /* Sesuaikan dengan Bootstrap */
        padding: 0.375rem 0.75rem;
    }

    /* Styling agar Select2 lebih menyatu dengan desain form */
    .select2-container .select2-selection--multiple {
        border: 1px solid #ced4da;
        /* Warna border yang cocok dengan Bootstrap */
        border-radius: 0.375rem;
        padding: 0.375rem;
        min-height: 2.5rem;
    }

    /* Styling untuk tag yang dipilih */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        /* Warna tag dipilih sesuai dengan warna utama Bootstrap */
        color: #fff;
        border-radius: 0.25rem;
        padding: 0.25rem;
        margin-top: 0.25rem;
    }

    /* Hover effect untuk tag */
    .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
        background-color: #0b5ed7;
    }

    /* Atur font-size di dalam modal untuk input Select2 */
    .modal-body .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 0.875rem;
    }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3>Select Kelas</h3>
        <!-- Tag Input -->
        <div class="form-group">
            <label for="kelas_tags">Kelas</label>
            <select class="form-control" id="kelas_tags" name="kelas[]" multiple="multiple"
                data-placeholder="Select Kelas">
                <!-- Options will be dynamically added here -->
            </select>
        </div>
    </div>

    <!-- AdminLTE 4 and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Prepare valid kelas options
        const validKelas = kelasData.map(function(item) {
            return {
                id: item.id, // ID of the kelas
                text: item.nama_kelas // Text to display
            };
        });

        // Initialize Select2 with tags option
        $('#kelas_tags').select2({
            tags: true, // Allow tagging
            data: validKelas,
            createTag: function(params) {
                // Check if the entered tag exists in the valid data
                const existingTag = validKelas.find(function(kelas) {
                    return kelas.text.toLowerCase() === params.term.toLowerCase();
                });

                // If the entered tag is valid, allow it, otherwise return null
                if (existingTag) {
                    return {
                        id: existingTag.id,
                        text: existingTag.text
                    };
                } else {
                    return null;
                }
            }
        });
    });
    </script>
</body>

</html>