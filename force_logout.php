<?php
// force_logout.php - Hanya untuk debugging
session_start();

echo "<h2>Session sebelum logout:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Hapus semua session
$_SESSION = array();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

echo "<h2>Session berhasil dihapus!</h2>";
echo "<a href='index.php'>Kembali ke Beranda</a>";
?>