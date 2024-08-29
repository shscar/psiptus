<?php

    include __DIR__ . '/../../layouts/master.php';

    $db = Database::getInstance();
    $users = $db->query("SELECT id, username, email, last_login, status, role FROM users");
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Administrator</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">user admin</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Administrator</h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Tambah Admin
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped projects">
                    <thead>
                        <tr>
                            <th style="width: 1%">#</th>
                            <th style="width: 20%">Username</th>
                            <th style="width: 20%">Email</th>
                            <th style="width: 20%">Last Login</th>
                            <th style="width: 10%" class="text-center">Status</th>
                            <th style="width: 10%">Role</th>
                            <th style="width: 20%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center">-</td>
                        </tr>
                        <?php else: 
                            foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']) ?: '-'; ?></td>
                            <td><?php echo htmlspecialchars($user['username']) ?: '-'; ?></td>
                            <td><?php echo htmlspecialchars($user['email']) ?: '-'; ?></td>
                            <td><?php echo htmlspecialchars($user['last_login']) ?: '-'; ?></td>
                            <td class="project-state">
                                <?php
                                    $status = htmlspecialchars($user['status']) ?: '-';
                                    switch ($status) {
                                        case 'active':
                                            $badgeClass = 'badge-success';
                                            $statusText = 'Active';
                                            break;
                                        case 'inactive':
                                            $badgeClass = 'badge-warning';
                                            $statusText = 'Inactive';
                                            break;
                                        case 'blocked':
                                            $badgeClass = 'badge-danger';
                                            $statusText = 'Blocked';
                                            break;
                                        default:
                                            $badgeClass = 'badge-secondary';
                                            $statusText = 'Unknown';
                                    }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($user['role']) ?: '-'; ?></td>
                            <td class="project-actions text-right">
                                <a class="btn btn-info btn-sm" href="edit_user.php?id=<?php echo urlencode($user['id']); ?>">
                                    <i class="fas fa-pencil-alt"></i> Edit
                                </a>
                                <a class="btn btn-danger btn-sm" href="delete_user.php?id=<?php echo urlencode($user['id']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->