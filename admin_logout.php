<?php
session_start();

// Hanya hapus session admin, TAPI JANGAN hapus data owners
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

// Jangan hapus user_role dan logged_in jika user lain login
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    unset($_SESSION['user_role']);
    unset($_SESSION['logged_in']);
    unset($_SESSION['user_name']);
}

// Redirect ke halaman login
header("Location: login.php");
exit;
?>