<?php
// Memulai buffering
ob_start();

// echo ini_get('session.save_path');

// include __DIR__ . '/../layouts/master.php';

// Dapatkan koneksi database
$db = Database::getInstance()->getConnection();

// Konfigurasi session yang sama dengan login
ini_set('session.cookie_lifetime', 86400); // 1 hari
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email dan password wajib diisi!";
        header("Location: login.php");
        exit;
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, guru_staff_id, username, email, password, role, status FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Akun Anda tidak aktif. Hubungi admin.";
            echo "<script>
                alert('Akun Anda tidak aktif. Hubungi admin.');
                window.location.href = '/';
            </script>";
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['guru_staff_id'] = $user['guru_staff_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['status'] = $user['status'];

        // Update last login timestamp
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        // Debugging: cek apakah session benar-benar tersimpan
        // var_dump($_SESSION);
        // exit;

        echo "<script>
            alert('Selamat Datang.');
            window.location.href = '/dashboard';
        </script>";
        exit;
    } else {
        $_SESSION['error'] = "Email atau password salah.";
        echo "<script>
            alert('Email atau password salah.');
            window.location.href = '/';
        </script>";
        // header("Location: login.php");
        exit;
    }
}

// Mengakhiri buffering
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5 py-4 py-md-6">
        <div class="row mb-5">
            <div class="col-md-6 align-self-center">
                <div class="lc-block text-center">
                    <img class="img-fluid mb-4" src="https://cdn.livecanvas.com/media/svg/undraw/analytics.svg" style=""
                        loading="lazy" width="350" height="350">
                </div>
            </div>
            <div class="col-md-6">
                <div class="lc-block">
                    <div editable="rich">
                        <h2>The quick brown fox jumps over the lazy cat</h2>
                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="floatingInput"
                                    placeholder="name@example.com">
                                <label for="floatingInput">Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" name="password" class="form-control" id="floatingPassword"
                                    placeholder="Password">
                                <label for="floatingPassword">Password</label>
                            </div>
                            <div class="d-grid gap-2 col-6 mx-auto mt-4">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>

                        <p><br></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block">
                    <img class="img-fluid mb-3" src="https://cdn.livecanvas.com/media/svg/undraw/tweetstorm.svg"
                        loading="lazy" width="92" height="92" style="height:10vh">
                </div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/playful-cat.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/broadcast.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="lc-block"><img class="img-fluid mb-3"
                        src="https://cdn.livecanvas.com/media/svg/undraw/android.svg" loading="lazy" width="92"
                        height="92" style="height:10vh"></div>
                <div class="lc-block">
                    <div editable="rich">

                        <h4>The quick brown</h4>

                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>