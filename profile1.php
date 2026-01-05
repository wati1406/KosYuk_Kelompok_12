<?php
// Profil Pencari Kos - KosYuk

// Start session
session_start();

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Jika admin dalam preview mode
$is_admin_preview = isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true;

// Cek role
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'pencari_kos' && !$is_admin_preview)) {
    header("Location: login.php");
    exit;
}

// Inisialisasi session untuk favorit jika belum ada
if (!isset($_SESSION['favorit_kos'])) {
    $_SESSION['favorit_kos'] = [];
}

// Data dummy kos yang sama dengan di detail_kos.php
$semua_kos = [];
for ($i = 1; $i <= 45; $i++) {
    $jenis = ($i % 2 == 0) ? 'putra' : 'putri';
    
    $semua_kos[$i] = [
        'id' => $i,
        'nama' => 'Kos ' . ['Harmoni', 'Merpati', 'Sejahtera', 'Anggrek', 'Dahlia', 'Kenanga', 
                           'Seruni', 'Cendana', 'Teratai', 'Mawar', 'Melati', 'Sakura'][$i % 12],
        'alamat' => ['Karangwangkal', 'Grendeng', 'Purwokerto Timur', 'Kecamatan Salaman', 'Kaliwungu'][$i % 5],
        'alamat_lengkap' => 'Jl. Contoh No.' . $i . ', ' . ['Karangwangkal', 'Grendeng', 'Purwokerto Timur', 'Kecamatan Salaman', 'Kaliwungu'][$i % 5] . ', Purwokerto',
        'harga' => rand(5000000, 15000000),
        'deskripsi' => "Kos yang nyaman dan strategis untuk mahasiswa.",
        'tipe' => ['Kamar Mandi Dalam', 'Kamar Mandi Luar'][$i % 2],
        'jenis_kelamin' => $jenis,
        'ukuran' => rand(12, 25),
        'kapasitas' => rand(1, 3),
        'pemilik_nama' => ['Budi Santoso', 'Sari Dewi', 'Agus Wijaya', 'Ratna Sari', 'Joko Prasetyo'][$i % 5],
        'pemilik_telepon' => '0812' . rand(1000000, 9999999),
        'lat' => -7.423 + (rand(-100, 100) / 10000),
        'lng' => 109.236 + (rand(-100, 100) / 10000),
        'rating' => number_format(rand(30, 50) / 10, 1),
        'total_review' => rand(5, 50)
    ];
}

// Handle hapus favorit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_favorit'])) {
    $kos_id = intval($_POST['kos_id']);
    
    // Cari dan hapus dari favorit
    $key = array_search($kos_id, $_SESSION['favorit_kos']);
    if ($key !== false) {
        unset($_SESSION['favorit_kos'][$key]);
        $_SESSION['favorit_kos'] = array_values($_SESSION['favorit_kos']); // Reset index
        $hapus_success = true;
    }
}

// Ambil data kos favorit dari session
$kos_favorit = [];
foreach ($_SESSION['favorit_kos'] as $kos_id) {
    if (isset($semua_kos[$kos_id])) {
        $kos = $semua_kos[$kos_id];
        $kos_favorit[] = [
            'id' => $kos['id'],
            'nama' => $kos['nama'],
            'lokasi' => $kos['alamat'],
            'harga' => 'Rp ' . number_format($kos['harga'], 0, ',', '.') . '/tahun',
            'gambar' => 'assets/images/kos' . (($kos['id'] % 4) + 1) . '.jpg',
            'jenis' => $kos['jenis_kelamin'],
            'fasilitas' => ['WiFi', 'AC', 'Kamar Mandi Dalam'], // Data dummy fasilitas
            'rating' => $kos['rating']
        ];
    }
}

// Data user dari session
$user_data = [
    'nama_lengkap' => $_SESSION['user_name'] ?? 'Budi Santoso',
    'email' => $_SESSION['user_email'] ?? 'budi.santoso@example.com',
    'no_handphone' => '081234567890',
    'jenis_kelamin' => 'Laki-laki',
    'tanggal_lahir' => '2000-05-15',
    'alamat' => 'Jl. Merdeka No. 123, Purwokerto',
    'universitas' => 'Universitas Jenderal Soedirman (UNSOED)',
    'fakultas' => 'Fakultas Teknik',
    'jurusan' => 'Teknik Informatika',
    'tanggal_daftar' => '2024-01-15',
    'foto_profil' => $_SESSION['foto_profil'] ?? 'assets/images/avatar1.png',
    'status_verifikasi' => 'Terverifikasi',
    'kos_difavoritkan' => count($kos_favorit), // Update dengan jumlah favorit yang sebenarnya
    'booking_aktif' => isset($_SESSION['booking_data']) ? 1 : 0
];

// Handle form submissions
$update_success = false;
$upload_success = false;
$password_success = false;
$upload_error = '';
$password_error = '';
$is_editing = isset($_POST['edit_mode']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profil'])) {
        // Simpan data yang diupdate
        $user_data['nama_lengkap'] = $_POST['nama_lengkap'] ?? $user_data['nama_lengkap'];
        $user_data['email'] = $_POST['email'] ?? $user_data['email'];
        $user_data['no_handphone'] = $_POST['no_handphone'] ?? $user_data['no_handphone'];
        $user_data['jenis_kelamin'] = $_POST['jenis_kelamin'] ?? $user_data['jenis_kelamin'];
        $user_data['alamat'] = $_POST['alamat'] ?? $user_data['alamat'];
        $user_data['universitas'] = $_POST['universitas'] ?? $user_data['universitas'];
        $user_data['fakultas'] = $_POST['fakultas'] ?? $user_data['fakultas'];
        $user_data['jurusan'] = $_POST['jurusan'] ?? $user_data['jurusan'];
        
        // Simpan ke session
        $_SESSION['user_name'] = $user_data['nama_lengkap'];
        $_SESSION['user_email'] = $user_data['email'];
        
        $update_success = true;
        $is_editing = false;
    }
    
    if (isset($_POST['edit_profile'])) {
        $is_editing = true;
    }
    
    if (isset($_POST['cancel_edit'])) {
        $is_editing = false;
    }
    
    // Handle upload foto
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto_profil']['type'];
        $file_size = $_FILES['foto_profil']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) { // Max 5MB
            $upload_dir = 'assets/uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path)) {
                // Simpan path foto ke session
                $_SESSION['foto_profil'] = $upload_path;
                $user_data['foto_profil'] = $upload_path;
                $upload_success = true;
            } else {
                $upload_error = "Gagal mengupload foto.";
            }
        } else {
            $upload_error = "File harus berupa gambar (JPEG, PNG, GIF) dan maksimal 5MB.";
        }
    }
    
    if (isset($_POST['ubah_password'])) {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
        
        // Validasi sederhana
        if ($password_baru === $konfirmasi_password && strlen($password_baru) >= 6) {
            $password_success = true;
        } else {
            $password_error = "Password baru harus minimal 6 karakter dan sesuai dengan konfirmasi password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pencari Kos - KosYuk</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Profil Pencari Kos Styles - REVISI */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Header styles - REVISI dengan gradasi biru */
        .main-header {
            background: linear-gradient(135deg, #0A2C4F 0%, #1a5fb4 100%);
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 8px 15px;
            border-radius: 5px;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-wrapper {
            display: flex;
            min-height: calc(100vh - 70px);
            margin-top: 70px;
        }
        
        .profile-sidebar {
            width: 300px;
            background: white;
            border-right: 1px solid #e9ecef;
            height: calc(100vh - 70px);
            position: fixed;
            overflow-y: auto;
            padding: 25px 20px;
            top: 70px;
            left: 0;
        }
        
        .profile-content-wrapper {
            flex: 1;
            margin-left: 300px;
            min-height: calc(100vh - 70px);
            padding: 20px;
            background: #f8f9fa;
        }
        
        .profile-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Profile Header Sidebar */
        .profile-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-photo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #0A2C4F;
        }
        
        .profile-name {
            font-size: 1.4rem;
            color: #0A2C4F;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .profile-role {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .verification-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Navigation */
        .profile-nav {
            margin-top: 20px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .nav-item:hover, .nav-item.active {
            background: #0A2C4F;
            color: white;
        }
        
        .nav-item i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .content-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .content-header h1 {
            color: #0A2C4F;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .content-subtitle {
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0A2C4F;
            font-size: 0.95rem;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .view-mode {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0A2C4F;
            box-shadow: 0 0 0 3px rgba(10, 44, 79, 0.1);
        }
        
        .readonly-field {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #0A2C4F;
            color: white;
        }
        
        .btn-primary:hover {
            background: #083162;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        /* Tabs - PERBAIKAN */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .profile-sidebar {
                width: 100%;
                height: auto;
                position: static;
                margin-left: 0;
            }
            
            .profile-content-wrapper {
                margin-left: 0;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .profile-content {
                padding: 20px;
            }
            
            .nav-menu {
                display: none;
            }
        }
        
        /* Section Titles */
        .section-title {
            font-size: 1.4rem;
            color: #0A2C4F;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        /* Foto Upload Button */
        .edit-photo-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(10, 44, 79, 0.9);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .edit-photo-btn:hover {
            background: #0A2C4F;
            transform: scale(1.1);
        }
        
        /* Foto Upload Modal */
        .upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            align-items: center;
            justify-content: center;
        }
        
        .upload-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        /* Custom scrollbar */
        .profile-sidebar::-webkit-scrollbar,
        .profile-content-wrapper::-webkit-scrollbar {
            width: 6px;
        }
        
        .profile-sidebar::-webkit-scrollbar-track,
        .profile-content-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .profile-sidebar::-webkit-scrollbar-thumb,
        .profile-content-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .profile-sidebar::-webkit-scrollbar-thumb:hover,
        .profile-content-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Kos Favorit Styles */
        .favorit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .kos-favorit-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .kos-favorit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .kos-favorit-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .kos-favorit-info {
            padding: 15px;
        }
        
        .kos-favorit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .kos-favorit-nama {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0A2C4F;
            margin: 0;
        }
        
        .kos-favorit-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffc107;
            font-weight: 600;
        }
        
        .kos-favorit-lokasi {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .kos-favorit-harga {
            color: #28a745;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .kos-favorit-fasilitas {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .fasilitas-tag {
            background: #e9ecef;
            color: #495057;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .kos-favorit-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-kos {
            flex: 1;
            padding: 8px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        
        .btn-detail {
            background: #0A2C4F;
            color: white;
        }
        
        .btn-detail:hover {
            background: #083162;
        }
        
        .btn-hapus-favorit {
            background: #dc3545;
            color: white;
        }
        
        .btn-hapus-favorit:hover {
            background: #c82333;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        /* Footer dihapus sesuai permintaan */
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .edit-btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        /* Booking Styles */
        .booking-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-header h3 {
            margin: 0;
            color: #0A2C4F;
        }
        
        .booking-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .booking-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .booking-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .booking-status.canceled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .booking-details p {
            margin: 8px 0;
            color: #555;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-booking {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-booking-primary {
            background: #0A2C4F;
            color: white;
        }
        
        .btn-booking-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-booking-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-booking-primary:hover {
            background: #083162;
        }
        
        .btn-booking-warning:hover {
            background: #e0a800;
        }
        
        .btn-booking-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <nav class="nav-menu">
                <a href="dashboard_pencarikos.php" class="nav-link">kembali</a>
            </nav>
            <div class="user-menu">
                <a href="logout.php" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Main Wrapper -->
    <div class="profile-wrapper">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-header">
                <div class="profile-photo-container">
                    <img src="<?php echo $user_data['foto_profil']; ?>" alt="Foto Profil" class="profile-photo" 
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['nama_lengkap']); ?>&background=0A2C4F&color=fff'">
                    <div class="edit-photo-btn" onclick="showUploadModal()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></h2>
                <p class="profile-role">Pencari Kos</p>
                <span class="verification-badge">
                    <i class="fas fa-check-circle"></i> <?php echo $user_data['status_verifikasi']; ?>
                </span>
            </div>
            
            <nav class="profile-nav">
                <a href="#data-diri" class="nav-item active" data-tab="data-diri" onclick="switchTab('data-diri')">
                    <i class="fas fa-user"></i> Data Diri
                </a>
                <a href="#favorit" class="nav-item" data-tab="favorit" onclick="switchTab('favorit')">
                    <i class="fas fa-heart"></i> Kos Favorit
                </a>
                <a href="#booking-saya" class="nav-item" data-tab="booking-saya" onclick="switchTab('booking-saya')">
                    <i class="fas fa-calendar-check"></i> Booking Saya
                </a>
                <a href="#keamanan" class="nav-item" data-tab="keamanan" onclick="switchTab('keamanan')">
                    <i class="fas fa-shield-alt"></i> Keamanan
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="profile-content-wrapper">
            <div class="profile-content">
                <!-- Alerts -->
                <?php if($update_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Profil berhasil diperbarui!
                </div>
                <?php endif; ?>
                
                <?php if($upload_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Foto profil berhasil diupload!
                </div>
                <?php endif; ?>
                
                <?php if(!empty($upload_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $upload_error; ?>
                </div>
                <?php endif; ?>
                
                <?php if($password_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Password berhasil diubah!
                </div>
                <?php endif; ?>
                
                <?php if(!empty($password_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($hapus_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Kos berhasil dihapus dari favorit!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Tab: Data Diri -->
                <div id="data-diri" class="tab-content active">
                    <div class="content-header">
                        <h1>Data Diri</h1>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" 
                                       value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" 
                                       class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                       <?php echo !$is_editing ? 'readonly' : ''; ?> required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                                       class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                       <?php echo !$is_editing ? 'readonly' : ''; ?> required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="no_handphone">Nomor Handphone</label>
                                <input type="tel" id="no_handphone" name="no_handphone" 
                                       value="<?php echo htmlspecialchars($user_data['no_handphone']); ?>" 
                                       class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                       <?php echo !$is_editing ? 'readonly' : ''; ?> required>
                            </div>
                            
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select id="jenis_kelamin" name="jenis_kelamin" 
                                        class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                        <?php echo !$is_editing ? 'disabled' : ''; ?> required>
                                    <option value="Laki-laki" <?php echo $user_data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo $user_data['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                                       value="<?php echo $user_data['tanggal_lahir']; ?>" class="readonly-field" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" name="alamat" rows="3" 
                                          class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                          <?php echo !$is_editing ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($user_data['alamat']); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Universitas, Fakultas, dan Jurusan dalam satu grup -->
                        <div class="form-group">
                            <label for="universitas">Universitas</label>
                            <select id="universitas" name="universitas" 
                                    class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                    <?php echo !$is_editing ? 'disabled' : ''; ?> required>
                                <option value="Universitas Jenderal Soedirman (UNSOED)" <?php echo $user_data['universitas'] == 'Universitas Jenderal Soedirman (UNSOED)' ? 'selected' : ''; ?>>Universitas Jenderal Soedirman (UNSOED)</option>
                                <option value="Universitas Muhammadiyah Purwokerto">Universitas Muhammadiyah Purwokerto</option>
                                <option value="Universitas Wijayakusuma">Universitas Wijayakusuma</option>
                                <option value="Universitas lain">Universitas lain</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fakultas">Fakultas</label>
                                <input type="text" id="fakultas" name="fakultas" 
                                       value="<?php echo htmlspecialchars($user_data['fakultas']); ?>" 
                                       class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                       <?php echo !$is_editing ? 'readonly' : ''; ?> required>
                            </div>
                            
                            <div class="form-group">
                                <label for="jurusan">Jurusan/Program Studi</label>
                                <input type="text" id="jurusan" name="jurusan" 
                                       value="<?php echo htmlspecialchars($user_data['jurusan']); ?>" 
                                       class="<?php echo !$is_editing ? 'view-mode' : ''; ?>" 
                                       <?php echo !$is_editing ? 'readonly' : ''; ?> required>
                            </div>
                        </div>
                        
                        <?php if(!$is_editing): ?>
                        <div class="edit-btn-container">
                            <button type="submit" name="edit_profile" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Profil
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="btn-group">
                            <button type="submit" name="update_profil" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <button type="submit" name="cancel_edit" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Tab: Kos Favorit -->
                <div id="favorit" class="tab-content">
                    <div class="content-header">
                        <h1>Kos Favorit</h1>
                        <p class="content-subtitle">Daftar kos yang telah Anda tandai sebagai favorit</p>
                    </div>
                    
                    <?php if(empty($kos_favorit)): ?>
                    <div class="empty-state">
                        <i class="fas fa-heart"></i>
                        <h3>Belum ada kos favorit</h3>
                        <p>Tambahkan kos ke favorit untuk melihatnya di sini</p>
                        <a href="semua_kos.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-search"></i> Cari Kos
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="favorit-grid">
                        <?php foreach($kos_favorit as $kos): ?>
                        <div class="kos-favorit-card">
                            <img src="<?php echo $kos['gambar']; ?>" alt="<?php echo $kos['nama']; ?>" class="kos-favorit-img"
                                 onerror="this.src='https://via.placeholder.com/280x180/0A2C4F/FFFFFF?text=KosYuk'">
                            <div class="kos-favorit-info">
                                <div class="kos-favorit-header">
                                    <h3 class="kos-favorit-nama"><?php echo $kos['nama']; ?></h3>
                                    <div class="kos-favorit-rating">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo $kos['rating']; ?></span>
                                    </div>
                                </div>
                                
                                <p class="kos-favorit-lokasi">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $kos['lokasi']; ?>
                                </p>
                                
                                <p class="kos-favorit-harga"><?php echo $kos['harga']; ?></p>
                                
                                <div class="kos-favorit-fasilitas">
                                    <?php foreach($kos['fasilitas'] as $fasilitas): ?>
                                    <span class="fasilitas-tag"><?php echo $fasilitas; ?></span>
                                    <?php endforeach; ?>
                                    <span class="fasilitas-tag">Kos <?php echo ucfirst($kos['jenis']); ?></span>
                                </div>
                                
                                <div class="kos-favorit-actions">
                                    <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="btn-kos btn-detail">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                    <form method="POST" action="" style="display: inline; width: 100%;">
                                        <input type="hidden" name="kos_id" value="<?php echo $kos['id']; ?>">
                                        <button type="submit" name="hapus_favorit" class="btn-kos btn-hapus-favorit w-100">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Booking Saya -->
                <div id="booking-saya" class="tab-content">
                    <div class="content-header">
                        <h1>Booking Saya</h1>
                        <p class="content-subtitle">Daftar booking kos yang telah Anda lakukan</p>
                    </div>
                    
                    <?php if(empty($_SESSION['booking_data'] ?? [])): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Belum ada booking</h3>
                        <p>Anda belum melakukan booking kos</p>
                        <a href="semua_kos.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-search"></i> Cari Kos
                        </a>
                    </div>
                    <?php else: 
                        $booking_data = $_SESSION['booking_data'];
                    ?>
                    <div class="booking-list">
                        <?php foreach($booking_data as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3><?php echo htmlspecialchars($booking['nama_kos']); ?></h3>
                                <span class="booking-status <?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <p><strong>Kode Booking:</strong> <?php echo $booking['kode_booking']; ?></p>
                                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($booking['alamat_kos']); ?></p>
                                <p><strong>Harga Kos per Tahun:</strong> Rp <?php echo number_format($booking['harga_kos'], 0, ',', '.'); ?></p>
                                <p><strong>Jenis Pembayaran:</strong> <?php echo $booking['jenis_pembayaran'] == 'dp' ? 'Down Payment (DP)' : 'Lunas'; ?></p>
                                <p><strong>Durasi Sewa:</strong> 
                                    <?php 
                                    if($booking['durasi_sewa'] == '6bulan') echo '6 Bulan';
                                    elseif($booking['durasi_sewa'] == '1tahun') echo '1 Tahun';
                                    else echo '2 Tahun';
                                    ?>
                                </p>
                                <p><strong>Tanggal Masuk:</strong> <?php echo date('d M Y', strtotime($booking['tanggal_masuk'])); ?></p>
                                <p><strong>Tanggal Booking:</strong> <?php echo date('d M Y H:i', strtotime($booking['tanggal_booking'])); ?></p>
                                <?php if(!empty($booking['catatan'])): ?>
                                <p><strong>Catatan:</strong> <?php echo htmlspecialchars($booking['catatan']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="booking-actions">
                                <button class="btn-booking btn-booking-primary" onclick="printBooking()">
                                    <i class="fas fa-print"></i> Cetak Invoice
                                </button>
                                <?php if($booking['status'] == 'pending'): ?>
                                <button class="btn-booking btn-booking-warning" onclick="batalkanBooking()">
                                    <i class="fas fa-times"></i> Batalkan Booking
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Keamanan -->
                <div id="keamanan" class="tab-content">
                    <div class="content-header">
                        <h1>Keamanan Akun</h1>
                    </div>
                    
                    <form method="POST" action="">
                        <h3 class="section-title">Ubah Kata Sandi</h3>
                        
                        <div class="form-group">
                            <label for="password_lama">Kata Sandi Lama</label>
                            <input type="password" id="password_lama" name="password_lama" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password_baru">Kata Sandi Baru</label>
                                <input type="password" id="password_baru" name="password_baru" required>
                                <small style="color: #666; font-size: 0.85rem; display: block; margin-top: 5px;">
                                    Minimal 6 karakter
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="konfirmasi_password">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="ubah_password" class="btn btn-success">
                                <i class="fas fa-key"></i> Ubah Kata Sandi
                            </button>
                        </div>
                    </form>
                    
                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                        <h3 class="section-title">Sesi Aktif</h3>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>Perangkat ini</strong>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 0.9rem;">
                                        <i class="fas fa-desktop"></i> Browser • <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?>
                                    </p>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 0.85rem;">
                                        Login terakhir: <?php echo date('d M Y H:i'); ?>
                                    </p>
                                </div>
                                <span style="color: #28a745; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Foto Modal -->
    <div id="uploadModal" class="upload-modal">
        <div class="upload-modal-content">
            <span class="close-modal" onclick="closeUploadModal()">&times;</span>
            <h2 style="color: #0A2C4F; margin-bottom: 20px;">Upload Foto Profil</h2>
            
            <form id="uploadForm" method="POST" action="" enctype="multipart/form-data">
                <div style="margin: 20px 0;">
                    <input type="file" id="foto_profil" name="foto_profil" 
                           accept="image/jpeg,image/jpg,image/png,image/gif" required>
                </div>
                
                <div style="margin: 20px 0; color: #666; font-size: 0.9rem;">
                    <p><i class="fas fa-info-circle"></i> Format: JPG, PNG, GIF</p>
                    <p><i class="fas fa-info-circle"></i> Ukuran maksimal: 5MB</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Fungsi untuk mengganti tab
    function switchTab(tabId) {
        console.log('Switching to tab:', tabId);
        
        // Sembunyikan semua tab
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => {
            tab.classList.remove('active');
            tab.style.display = 'none';
        });
        
        // Tampilkan tab yang dipilih
        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.add('active');
            activeTab.style.display = 'block';
        }
        
        // Update navigasi
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.classList.remove('active');
        });
        
        const activeNav = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeNav) {
            activeNav.classList.add('active');
        }
        
        // Update URL hash
        window.location.hash = tabId;
    }
    
    // Inisialisasi tab saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Cek hash URL saat pertama kali dimuat
        const hash = window.location.hash.substring(1);
        if (hash) {
            setTimeout(() => {
                switchTab(hash);
            }, 100);
        } else {
            // Default ke tab Data Diri
            switchTab('data-diri');
        }
        
        // Tambahkan event listener untuk semua nav item
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                if (tabId) {
                    switchTab(tabId);
                }
            });
        });
    });
    
    // Handle perubahan hash URL
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.substring(1);
        if (hash) {
            switchTab(hash);
        }
    });
    
    // Foto Upload Functions
    function showUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }
    
    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
        document.getElementById('uploadForm').reset();
    }
    
    // Print booking
    function printBooking() {
        alert('Fitur print booking akan diimplementasikan');
    }
    
    // Batalkan booking
    function batalkanBooking() {
        if (confirm('Yakin ingin membatalkan booking ini?')) {
            alert('Booking berhasil dibatalkan. Status akan diubah.');
            location.reload();
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('uploadModal');
        if (event.target === modal) {
            closeUploadModal();
        }
    }
    
    // Validasi upload form
    document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('foto_profil');
        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Silakan pilih file terlebih dahulu');
            return false;
        }
        
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            e.preventDefault();
            alert('Ukuran file terlalu besar. Maksimal 5MB');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>