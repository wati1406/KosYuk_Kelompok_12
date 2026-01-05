<?php
// admin_access.php - Untuk handle akses admin ke dashboard user

// JANGAN start session di sini - biarkan file yang memanggil yang mengatur session

// Cek apakah admin sedang login
$is_admin_logged_in = false;
if (session_status() === PHP_SESSION_ACTIVE) {
    $is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Jika admin mengakses dashboard user, set session user khusus admin
if ($is_admin_logged_in && !isset($_SESSION['user_role'])) {
    // Set session temporary untuk admin sebagai pencari kos
    $_SESSION['logged_in'] = true;
    $_SESSION['user_role'] = 'pencari_kos';
    $_SESSION['user_name'] = 'Admin (Preview Mode)';
    $_SESSION['user_email'] = 'admin@kosyuk.com';
    $_SESSION['user_phone'] = '081234567890';
    $_SESSION['admin_preview_mode'] = true; // Flag khusus untuk admin preview
}

// Return status
return $is_admin_logged_in;
?>