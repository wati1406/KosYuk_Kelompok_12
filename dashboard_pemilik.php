<?php
session_start();

// Cek apakah user sudah login sebagai pemilik kos
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'pemilik_kos') {
    header("Location: login.php");
    exit;
}

// Inisialisasi data kos pemilik
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

// Cari data kos pemilik berdasarkan email
$kos_pemilik = [];
$semua_kos = array_merge($_SESSION['pending_owners'] ?? [], $_SESSION['verified_owners'] ?? []);

foreach ($semua_kos as $index => $kos) {
    if (isset($kos['email']) && $kos['email'] === $user_email) {
        $kos['index'] = $index; // Simpan index untuk edit
        $kos_pemilik[] = $kos;
    }
}

$total_kos = count($kos_pemilik);
$kos_verified = 0;
$kos_pending = 0;

foreach ($kos_pemilik as $kos) {
    if (($kos['status'] ?? 'pending') === 'verified') {
        $kos_verified++;
    } else {
        $kos_pending++;
    }
}

// Inisialisasi data notifikasi dengan 3 jenis
if (!isset($_SESSION['owner_notifications'])) {
    $_SESSION['owner_notifications'] = [
        // Notifikasi 1: Pembayaran dari pencari kos
        [
            'id' => 1,
            'title' => 'Pembayaran Diterima',
            'message' => 'Rudi telah membayar sewa kos "Kos Wisma Alden Bu Ning" untuk 1 tahun sebesar Rp 1.000.000.',
            'date' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'read' => false,
            'type' => 'payment',
            'kos_name' => 'Kos Wisma Alden Bu Ning',
            'renter_name' => 'Rudi',
            'amount' => 1000000,
            'duration' => '1 tahun'
        ],
        // Notifikasi 2: Review dari pencari kos
        [
            'id' => 2,
            'title' => 'Review Baru untuk Kos Anda',
            'message' => 'Sari memberikan review 4.5 bintang untuk "Kos Wisma Alden Bu Ning". Ulasan: "Kosnya nyaman dan bersih!"',
            'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'read' => false,
            'type' => 'review',
            'kos_name' => 'Kos Wisma Alden Bu Ning',
            'renter_name' => 'Budi',
            'rating' => 4.5,
            'review_text' => 'Kosnya nyaman dan bersih!'
        ],
        // Notifikasi 3: Pencari kos menghubungi via WA
        [
            'id' => 3,
            'title' => 'Permintaan Kontak via WhatsApp',
            'message' => 'Budi ingin menghubungi Anda via WhatsApp mengenai "Kos Wisma Alden Bu Ning". Nomor WA: 0812-3456-7890',
            'date' => date('Y-m-d H:i:s', strtotime('-5 hours')),
            'read' => false,
            'type' => 'contact',
            'kos_name' => 'Kos Wisma Alden Bu Ning',
            'renter_name' => 'Budi',
            'phone' => '081234567890'
        ],
        // Notifikasi default lainnya
        [
            'id' => 4,
            'title' => 'Pendaftaran Kos Diterima',
            'message' => 'Pendaftaran kos Anda telah diterima dan sedang dalam proses verifikasi.',
            'date' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'read' => true,
            'type' => 'info'
        ]
    ];
}

// Hitung notifikasi yang belum dibaca
$total_notifications = count($_SESSION['owner_notifications']);
$unread_notifications = 0;
foreach ($_SESSION['owner_notifications'] as $notif) {
    if (!$notif['read']) {
        $unread_notifications++;
    }
}

// Proses tindakan notifikasi jika ada
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    foreach ($_SESSION['owner_notifications'] as &$notif) {
        $notif['read'] = true;
    }
    $unread_notifications = 0;
    header("Location: dashboard_pemilik.php#notifikasi");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemilik Kos - KosYuk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0A2C4F;
            --primary-light: #18a0fb;
            --secondary: #f8f9fa;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #ffffff;
            --dark: #343a40;
            --gray: #6c757d;
            --info: #17a2b8;
            --gradient-primary: linear-gradient(135deg, #0A2C4F 0%, #1a4a7a 50%, #18a0fb 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--gradient-primary);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo {
            width: 80px;
            margin-bottom: 15px;
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
            color: white;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            padding: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-light);
        }

        .nav-link.active {
            background: rgba(24, 160, 251, 0.1);
            color: white;
            border-left-color: var(--primary-light);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Badge untuk notifikasi */
        .badge-count {
            position: absolute;
            right: 15px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }

        .logout-btn {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(220, 53, 69, 0.1);
            border-left: 3px solid var(--danger);
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.2);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        /* Header */
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h1 {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Section Content */
        .section-content {
            display: none;
        }

        .section-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Welcome Card */
        .welcome-card {
            background: var(--gradient-primary);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(10, 44, 79, 0.3);
            margin-bottom: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: gentleGlow 10s linear infinite;
        }

        @keyframes gentleGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .welcome-content {
            display: flex;
            align-items: center;
            gap: 25px;
            position: relative;
            z-index: 1;
        }

        .welcome-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .welcome-text {
            flex: 1;
        }

        .welcome-text h3 {
            font-size: 1.6rem;
            margin-bottom: 10px;
            font-weight: 600;
            text-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .welcome-text p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            margin: 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .total-kos .stat-icon { background: var(--primary); }
        .kos-verified .stat-icon { background: var(--success); }
        .kos-pending .stat-icon { background: var(--warning); }
        .total-notifications .stat-icon { background: var(--info); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .total-kos .stat-number { color: var(--primary); }
        .kos-verified .stat-number { color: var(--success); }
        .kos-pending .stat-number { color: var(--warning); }
        .total-notifications .stat-number { color: var(--info); }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .btn-add-kos {
            padding: 10px 20px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-add-kos:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            color: var(--dark);
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-verified {
            background: #d4edda;
            color: #155724;
        }

        .status-unread {
            background: #d1ecf1;
            color: #0c5460;
            font-weight: bold;
        }

        .status-read {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Notification Type Badge */
        .notification-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .type-payment { background: #d4edda; color: #155724; }
        .type-review { background: #fff3cd; color: #856404; }
        .type-contact { background: #d1ecf1; color: #0c5460; }
        .type-info { background: #e2e3e5; color: #383d41; }
        .type-booking { background: #d1ecf1; color: #0c5460; }

        /* Action Button - Hanya Detail saja */
        .btn-detail {
            padding: 6px 12px;
            background: var(--primary-light);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-detail:hover {
            background: #0b7dda;
            transform: translateY(-2px);
        }

        .btn-mark-read {
            background: var(--info);
            color: white;
        }

        .btn-mark-read:hover {
            background: #138496;
        }

        /* Profile Form */
        .profile-form {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .form-group input:read-only {
            background: #f8f9fa;
            color: #6c757d;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-save {
            padding: 12px 30px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        /* Form Section Title */
        .form-section-title {
            color: var(--primary);
            font-size: 1.2rem;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: var(--primary-light);
        }

        /* Notifications */
        .notification-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
            cursor: pointer;
        }

        .notification-item.unread {
            background: #f8f9fa;
            border-left-color: var(--primary-light);
        }

        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .notification-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-message {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .notification-meta {
            font-size: 0.8rem;
            color: #6c757d;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h2,
            .nav-link span,
            .badge-count {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                width: 100%;
                margin-top: 10px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .welcome-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .welcome-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
            
            .welcome-text h3 {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 480px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-info {
                align-self: flex-end;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .btn-add-kos {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Pemilik Kos -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo.png" alt="Logo" class="sidebar-logo">
            <h2>Pemilik Kos</h2>
        </div>
        
        <ul class="nav-menu">
            <!-- MENU 1: Dashboard Utama -->
            <li class="nav-item">
                <a href="#dashboard" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard Utama</span>
                </a>
            </li>
            <!-- MENU 2: Kelola Profil -->
            <li class="nav-item">
                <a href="#kelola-profil" class="nav-link">
                    <i class="fas fa-user-cog"></i>
                    <span>Kelola Profil</span>
                </a>
            </li>
            <!-- MENU 3: Notifikasi -->
            <li class="nav-item">
                <a href="#notifikasi" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifikasi</span>
                    <?php if($unread_notifications > 0): ?>
                    <span class="badge-count" id="notificationBadge"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="content-header">
            <h1 id="section-title">Dashboard Utama</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 0.8rem; color: var(--gray);">Pemilik Kos</div>
                </div>
            </div>
        </div>

        <!-- SECTION 1: Dashboard Utama -->
        <div id="dashboard" class="section-content active">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="welcome-text">
                        <h3>Selamat Datang, <?php echo htmlspecialchars($user_name); ?>!</h3>
                        <p>Kelola kos Anda dengan mudah melalui dashboard ini. Pantau status, terima booking, dan kelola informasi kos.</p>
                    </div>
                </div>
            </div>

            <!-- Statistik Kepemilikan Kos -->
            <div class="stats-grid">
                <div class="stat-card total-kos">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo $total_kos; ?></div>
                            <div class="stat-label">Total Kos Anda</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card kos-verified">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo $kos_verified; ?></div>
                            <div class="stat-label">Kos Terverifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card kos-pending">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number"><?php echo $kos_pending; ?></div>
                            <div class="stat-label">Menunggu Verifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-notifications">
                    <div class="stat-header">
                        <div>
                            <div class="stat-number" id="totalNotificationsCount"><?php echo $total_notifications; ?></div>
                            <div class="stat-label">Total Notifikasi</div>
                            <?php if($unread_notifications > 0): ?>
                            <div style="font-size: 0.8rem; color: var(--danger); margin-top: 5px;" id="unreadCountDisplay">
                                <span id="unreadCount"><?php echo $unread_notifications; ?></span> belum dibaca
                            </div>
                            <?php else: ?>
                            <div style="font-size: 0.8rem; color: var(--success); margin-top: 5px;" id="unreadCountDisplay">
                                Semua sudah dibaca
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Kos Anda -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Daftar Kos Anda</h2>
                    <a href="tambah_kos.php" class="btn-add-kos">
                        <i class="fas fa-plus"></i> Tambah Kos Baru
                    </a>
                </div>
                
                <!-- Search Box -->
                <div class="search-box" style="margin: 0 20px 20px 20px;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchKos" placeholder="Cari nama kos atau alamat...">
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kos</th>
                            <th>Alamat</th>
                            <th>Tipe</th>
                            <th>Jumlah Kamar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="kosTable">
                        <?php if(empty($kos_pemilik)): ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-building"></i>
                                    <h3>Belum ada kos yang terdaftar</h3>
                                    <p>Klik tombol "Tambah Kos Baru" untuk mendaftarkan kos pertama Anda</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($kos_pemilik as $index => $kos): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($kos['kos_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($kos['kos_address'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($kos['kos_type'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($kos['rooms'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    $status = $kos['status'] ?? 'pending';
                                    $status_class = $status === 'verified' ? 'status-verified' : 'status-pending';
                                    $status_text = $status === 'verified' ? 'Terverifikasi' : 'Menunggu';
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Generate link dengan parameter yang benar
                                    $kos_id = $kos['id'] ?? 'KOS_' . uniqid();
                                    $kos_index = $kos['index'] ?? $index;
                                    ?>
                                    <a href="detail_kos_pemilik.php?id=<?php echo urlencode($kos_id); ?>&index=<?php echo $kos_index; ?>" class="btn-detail">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: Kelola Profil -->
        <div id="kelola-profil" class="section-content">
            <div class="profile-form">
                <h2 style="color: var(--primary); margin-bottom: 25px;">Kelola Profil</h2>
                
                <form id="profileForm" onsubmit="updateProfile(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileName">Nama Lengkap</label>
                            <input type="text" id="profileName" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="profileEmail">Email</label>
                            <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="profilePhone">No. Telepon</label>
                            <input type="tel" id="profilePhone" value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? '081234567890'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="profileKTP">No. KTP</label>
                            <input type="text" id="profileKTP" value="<?php echo htmlspecialchars($_SESSION['user_ktp'] ?? '3273xxxxxxxxxxxx'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profileAddress">Alamat Lengkap</label>
                        <textarea id="profileAddress" rows="3"><?php echo htmlspecialchars($_SESSION['user_address'] ?? 'Jl. Raya Mayjen Sungkono No.KM 5, Purbalingga'); ?></textarea>
                    </div>

                    <!-- INFORMASI BANK - HANYA 4 BANK -->
                    <div class="form-section-title">
                        <i class="fas fa-university"></i> Informasi Bank
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bankName">Nama Bank *</label>
                            <select id="bankName" name="bankName" required>
                                <option value="">Pilih Bank</option>
                                <option value="bri" <?php echo ($_SESSION['user_bank_name'] ?? '') === 'bri' ? 'selected' : ''; ?>>BRI</option>
                                <option value="bca" <?php echo ($_SESSION['user_bank_name'] ?? '') === 'bca' ? 'selected' : ''; ?>>BCA</option>
                                <option value="mandiri" <?php echo ($_SESSION['user_bank_name'] ?? '') === 'mandiri' ? 'selected' : ''; ?>>Mandiri</option>
                                <option value="bni" <?php echo ($_SESSION['user_bank_name'] ?? '') === 'bni' ? 'selected' : ''; ?>>BNI</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="accountName">Nama Pemilik Rekening *</label>
                            <input type="text" id="accountName" name="accountName" 
                                   value="<?php echo htmlspecialchars($_SESSION['user_account_name'] ?? $user_name); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="accountNumber">Nomor Rekening *</label>
                        <input type="text" id="accountNumber" name="accountNumber" 
                               value="<?php echo htmlspecialchars($_SESSION['user_account_number'] ?? ''); ?>"
                               required>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- SECTION 3: Notifikasi -->
        <div id="notifikasi" class="section-content">
            <div class="table-container">
                <div class="table-header">
                    <h2>Notifikasi</h2>
                    <?php if($unread_notifications > 0): ?>
                    <a href="?action=mark_all_read#notifikasi" class="btn-detail btn-mark-read" onclick="return confirm('Tandai semua notifikasi sebagai dibaca?')">
                        <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                    </a>
                    <?php endif; ?>
                </div>
                
                <div style="padding: 20px;">
                    <?php if(empty($_SESSION['owner_notifications'])): ?>
                        <div class="no-data">
                            <i class="fas fa-bell-slash"></i>
                            <h3>Tidak ada notifikasi</h3>
                        </div>
                    <?php else: ?>
                        <?php 
                        // Urutkan notifikasi berdasarkan tanggal terbaru
                        $sorted_notifications = $_SESSION['owner_notifications'];
                        usort($sorted_notifications, function($a, $b) {
                            return strtotime($b['date']) - strtotime($a['date']);
                        });
                        ?>
                        <?php foreach($sorted_notifications as $index => $notif): ?>
                        <div class="notification-item <?php echo !$notif['read'] ? 'unread' : ''; ?>" onclick="viewNotification(<?php echo array_search($notif['id'], array_column($_SESSION['owner_notifications'], 'id')); ?>)">
                            <div class="notification-title">
                                <span><?php echo htmlspecialchars($notif['title']); ?></span>
                                <?php if(!$notif['read']): ?>
                                <span class="status-badge status-unread">Baru</span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>
                            <div class="notification-meta">
                                <span><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['date'])); ?></span>
                                <span class="notification-type type-<?php echo $notif['type']; ?>">
                                    <?php 
                                    $type_text = '';
                                    switch($notif['type']) {
                                        case 'payment': $type_text = 'Pembayaran'; break;
                                        case 'review': $type_text = 'Review'; break;
                                        case 'contact': $type_text = 'Kontak'; break;
                                        case 'info': $type_text = 'Informasi'; break;
                                        case 'booking': $type_text = 'Booking'; break;
                                        default: $type_text = 'Lainnya';
                                    }
                                    echo $type_text;
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if (href.startsWith('#')) {
                    e.preventDefault();
                    
                    // Update active nav
                    document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update section title
                    const title = this.querySelector('span').textContent;
                    document.getElementById('section-title').textContent = title;
                    
                    // Show selected section
                    document.querySelectorAll('.section-content').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    document.querySelector(href).classList.add('active');
                    
                    // Scroll to top
                    window.scrollTo(0, 0);
                }
            });
        });

        // Update Profile
        function updateProfile(e) {
            e.preventDefault();
            
            // Validasi informasi bank
            const bankName = document.getElementById('bankName').value;
            const accountName = document.getElementById('accountName').value.trim();
            const accountNumber = document.getElementById('accountNumber').value.trim();
            
            if (!bankName) {
                alert('Harap pilih nama bank');
                return;
            }
            
            if (!accountName) {
                alert('Harap isi nama pemilik rekening');
                return;
            }
            
            if (!accountNumber) {
                alert('Harap isi nomor rekening');
                return;
            }
            
            // Simpan data ke session (dalam aplikasi nyata, ini akan disimpan ke database)
            const formData = {
                phone: document.getElementById('profilePhone').value,
                ktp: document.getElementById('profileKTP').value,
                address: document.getElementById('profileAddress').value,
                bankName: bankName,
                accountName: accountName,
                accountNumber: accountNumber
            };
            
            // Simpan ke session storage untuk demo
            sessionStorage.setItem('userProfile', JSON.stringify(formData));
            
            alert('Profil berhasil diperbarui!');
        }

        // View Notification
        function viewNotification(index) {
            // Mark as read when clicking
            markNotificationAsRead(index);
        }

        // Mark Notification as Read
        function markNotificationAsRead(index) {
            // Mark as read in UI
            const notifElement = document.querySelectorAll('.notification-item')[index];
            if (notifElement) {
                notifElement.classList.remove('unread');
                
                // Remove "Baru" badge
                const badge = notifElement.querySelector('.status-badge');
                if (badge) {
                    badge.remove();
                }
                
                // Update counter
                updateNotificationCounter();
            }
        }

        // Update Notification Counter
        function updateNotificationCounter() {
            // Hitung ulang notifikasi yang belum dibaca
            const unreadNotifications = document.querySelectorAll('.notification-item.unread').length;
            const badge = document.querySelector('.badge-count');
            
            if (unreadNotifications === 0) {
                // Hapus badge jika tidak ada notifikasi belum dibaca
                if (badge) {
                    badge.remove();
                }
                // Update statistik
                const unreadCountDisplay = document.getElementById('unreadCountDisplay');
                if (unreadCountDisplay) {
                    unreadCountDisplay.innerHTML = 'Semua sudah dibaca';
                    unreadCountDisplay.style.color = 'var(--success)';
                }
            } else {
                // Update badge count
                if (badge) {
                    badge.textContent = unreadNotifications;
                }
                // Update statistik
                const unreadCount = document.getElementById('unreadCount');
                if (unreadCount) {
                    unreadCount.textContent = unreadNotifications;
                }
            }
        }

        // Search functionality for kos table
        const searchKosInput = document.getElementById('searchKos');
        
        if (searchKosInput) {
            searchKosInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#kosTable tr');
                
                rows.forEach(row => {
                    if (row.classList.contains('no-data')) return;
                    
                    const kosName = row.children[1].textContent.toLowerCase();
                    const kosAddress = row.children[2].textContent.toLowerCase();
                    
                    if (kosName.includes(searchTerm) || kosAddress.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Check if all rows are hidden
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
                if (visibleRows.length === 0 && rows.length > 0) {
                    // Show no results message
                    const tableBody = document.getElementById('kosTable');
                    if (!tableBody.querySelector('.no-results')) {
                        const noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results';
                        noResultsRow.innerHTML = `
                            <td colspan="7" class="no-data">
                                <i class="fas fa-search"></i>
                                <h3>Tidak ditemukan</h3>
                                <p>Tidak ada kos yang sesuai dengan pencarian "${searchTerm}"</p>
                            </td>
                        `;
                        tableBody.appendChild(noResultsRow);
                    }
                } else {
                    // Remove no results message if exists
                    const noResults = document.querySelector('.no-results');
                    if (noResults) {
                        noResults.remove();
                    }
                }
            });
        }

        // Confirm sebelum logout
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin logout?')) {
                e.preventDefault();
            }
        });

        // Load profile data from session storage
        document.addEventListener('DOMContentLoaded', function() {
            // Load profile data if exists
            const savedProfile = sessionStorage.getItem('userProfile');
            if (savedProfile) {
                const profileData = JSON.parse(savedProfile);
                document.getElementById('profilePhone').value = profileData.phone || '';
                document.getElementById('profileKTP').value = profileData.ktp || '';
                document.getElementById('profileAddress').value = profileData.address || '';
                document.getElementById('bankName').value = profileData.bankName || '';
                document.getElementById('accountName').value = profileData.accountName || '';
                document.getElementById('accountNumber').value = profileData.accountNumber || '';
            }
            
            // Update notifikasi counter setelah refresh
            updateNotificationCounter();
        });
    </script>
</body>
</html>