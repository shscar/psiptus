<?php
    // Memulai output buffering
    ob_start();
    include __DIR__ . '/../layouts/master.php';
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT u.username, g.nama_lengkap, g.profile
        FROM users u
        JOIN guru_staff g ON u.guru_staff_id = g.id
        WHERE u.status = 'active'
        ORDER BY u.last_login DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mengakhiri output buffering
    ob_end_flush();
?>

<!-- <style>
    .lc-block img {
        width: auto;
        max-height: 250px;
    }
</style> -->

<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">User</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            tec-employ
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- App Content -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Default box -->
            <div class="card card-solid">

                <!-- <div class="container-fluid">
                    <div class="row pb-4">
                        <?php foreach ($users as $user): ?>
                            <div class="col-md-6 col-lg-3 my-2">
                                <div class="lc-block">
                                    <?php
                                        $profileImage = 'assets/images/profile/' . htmlspecialchars($user['profile']);
                                        // Memeriksa apakah file gambar ada
                                        if (!file_exists($profileImage) || empty($user['profile'])) {
                                            $profileImage = 'https://placehold.co/6090x200';
                                        }
                                    ?>
                                    <img src="<?= $profileImage ?>" title="" alt="<?= htmlspecialchars($user['nama_lengkap'] ?? 'No name') ?>" loading="lazy" class="img-fluid">
                                <div class="lc-block position-relative text-center mx-2 mt-n4 py-4 bg-light shadow" style="">
                                    <h4 editable="inline"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                                    <p editable="inline" class="small"> <?= htmlspecialchars($user['username']) ?></p>
                                    <p editable="inline" class="small">Illustrator Designer</p>
                                    <div class="nav justify-content-center">
                                        <i class="bi bi-whatsapp ms-1 me-1"></i>
                                        <i class="bi bi-linkedin ms-1 me-1"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div> -->

                <div class="card-body pb-0">
                    <div class="row">

                        <?php foreach ($users as $user): ?>
                            <div class="col-md-8 col-lg-4 mb-4">
                                <div class="card border-0 shadow">
                                    <div class="card-body py-4">
                                        <div class="d-flex">
                                            <img style="width:48px;height:48px"
                                                src="assets/images/profile/<?= htmlspecialchars($user['profile']) ?>"
                                                alt="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                                class="rounded-2 shadow">
                                            <div class="ps-2">
                                                <h4 class="rfs-7 ms-2"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                                            </div>
                                        </div>
                                        <div class="lc-block mt-4 text-muted">
                                            <p><b>Nama:</b> <?= htmlspecialchars($user['username']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</main>