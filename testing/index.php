<?php
$db = Database::getInstance()->getConnection();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Navigation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>

<body>
    <div class="container mt-4">
        <ul class="nav nav-tabs" id="formTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual"
                    type="button" role="tab" aria-controls="manual" aria-selected="true">
                    <i class="bi bi-gear-fill"></i> Masukan Data Form Manual
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="excel-tab" data-bs-toggle="tab" data-bs-target="#excel" type="button"
                    role="tab" aria-controls="excel" aria-selected="false">
                    <i class="bi bi-people-fill"></i> Masukan dengan Excel
                </button>
            </li>
        </ul>
        <div class="tab-content" id="formTabsContent">
            <!-- Form Manual -->
            <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                <div class="mt-3">
                    <h4>Form Manual</h4>
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="name" placeholder="Masukkan Nama">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Masukkan Email">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>

            <!-- Form Excel -->
            <div class="tab-pane fade" id="excel" role="tabpanel" aria-labelledby="excel-tab">
                <div class="mt-3">
                    <h4>Form Excel</h4>
                    <form>
                        <div class="mb-3">
                            <label for="upload" class="form-label">Upload File Excel</label>
                            <input type="file" class="form-control" id="upload" accept=".xlsx, .xls">
                        </div>
                        <button type="submit" class="btn btn-warning">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>