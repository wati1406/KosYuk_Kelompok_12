<?php
session_start();

// Jika admin dalam preview mode, hanya clear preview session
if (isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true) {
    // Hanya hapus session preview, biarkan admin session tetap
    unset($_SESSION['logged_in']);
    unset($_SESSION['user_role']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_phone']);
    unset($_SESSION['admin_preview_mode']);
    
    // Redirect kembali ke admin dashboard
    header("Location: admin_dashboard.php");
    exit;
}

// Hapus semua data session USER biasa (pencari/pemilik kos)
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_role']);
unset($_SESSION['logged_in']);
unset($_SESSION['kos_data']);
unset($_SESSION['owner_index']);

// JANGAN hapus data owners (verified_owners, pending_owners, rejected_owners)
// Data ini harus tetap ada untuk admin

// Hapus cookie session jika perlu
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session untuk user biasa
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>