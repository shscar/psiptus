<?php
// Memulai output buffering
ob_start();
include __DIR__ . '/../layouts/master.php';
$db = Database::getInstance()->getConnection();

// Mengakhiri output buffering
ob_end_flush();
?>
<!-- App Main -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Simple Tables</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Simple Tables
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
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch flex-column">
                            <div class="card bg-light d-flex flex-fill">
                                <div class="card-header text-muted border-bottom-0 d-flex justify-content-between align-items-center w-100">
                                    <span>Digital Strategist</span>
                                    <a href="#" class="btn btn-sm btn-primary ml-auto">
                                        <i class="fas fa-user"></i> View Profile
                                    </a>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <img src="../assets/dist/img/user1-128x128.jpg" alt="user-avatar" class="img-circle img-fluid">
                                        </div>
                                        <div class="col-12">
                                            <h2 class="lead text-center"><b>Nicole Pearson</b></h2>
                                            <div class="text-muted">
                                                <p class="mb-0">
                                                    <b>Mobile: </b> 
                                                    081298347652
                                                </p>
                                                <p class="mb-0">
                                                    <b>Email: </b> 
                                                    example@gmail.id
                                                </p>
                                                <p class="mb-0">
                                                    <b>About: </b> 
                                                    Web Designer / UX / Graphic Artist
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>