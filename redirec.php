<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

switch ($_SESSION['user_role']) {
    case 'admin':
        header("Location: admin_dashboard.php");
        break;

    case 'pencari_kos':
        header("Location: dashboard_pencarikos.php");
        break;

    case 'pemilik_kos':
        header("Location: dashboard_pemilik.php");
        break;

    default:
        session_destroy();
        header("Location: login.php");
}
exit;
?>