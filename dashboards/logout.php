<?php
// Mulai output buffering untuk mencegah header already sent error
ob_start();

// Dapatkan koneksi database
$db = Database::getInstance()->getConnection();

// Konfigurasi session yang sama dengan login
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, redirect ke halaman utama
    header("Location: /");
    exit;
}

// Opsional: Update logout timestamp di database
try {
    $stmt = $db->prepare("UPDATE users SET last_logout = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
} catch (PDOException $e) {
    // Log error jika diperlukan, tapi tetap lanjutkan logout
    error_log("Error updating logout timestamp: " . $e->getMessage());
}

// Simpan pesan sukses sebelum menghapus session
$logout_message = "Anda telah berhasil logout.";

// Hapus semua data session
$_SESSION = array();

// Hapus session cookie jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect dengan pesan
echo "<script>
    alert('$logout_message');
    window.location.href = '/';
</script>";

// Mengakhiri buffering
ob_end_flush();
exit;
?>