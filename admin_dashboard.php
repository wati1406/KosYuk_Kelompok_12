<?php
session_start();

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Inisialisasi array jika belum ada
if(!isset($_SESSION['pending_owners'])) {
    $_SESSION['pending_owners'] = [];
}

if(!isset($_SESSION['verified_owners'])) {
    $_SESSION['verified_owners'] = [];
}

// Inisialisasi array pencari kos jika belum ada
if(!isset($_SESSION['registered_seekers'])) {
    $_SESSION['registered_seekers'] = [];
}

// Inisialisasi array pesan jika belum ada
if(!isset($_SESSION['contact_messages'])) {
    $_SESSION['contact_messages'] = [];
}

// Inisialisasi array FAQ - DITAMBAHKAN 6 PERTANYAAN SESUAI PANDUAN
if(!isset($_SESSION['faq_data'])) {
    $_SESSION['faq_data'] = [];
    
    // Tambahkan FAQ default jika kosong
    if(empty($_SESSION['faq_data'])) {
        $default_faqs = [
            [
                'id' => 1,
                'question' => 'Apakah booking di KosYuk benar-benar gratis?',
                'answer' => 'Ya, booking di KosYuk sepenuhnya gratis. Pengguna hanya membayar langsung kepada pemilik kos sesuai ketentuan mereka, tanpa biaya layanan tambahan dari platform.',
                'category' => 'umum',
                'created_date' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'question' => 'Bagaimana cara menghubungi pemilik kos?',
                'answer' => 'Anda dapat menghubungi pemilik kos melalui tombol WhatsApp atau Telepon yang tersedia pada halaman detail kos. Fitur ini memudahkan Anda bertanya langsung tentang ketersediaan dan informasi tambahan.',
                'category' => 'pencari_kos',
                'created_date' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'question' => 'Bagaimana jika terjadi masalah dengan pemilik kos atau kamar?',
                'answer' => 'Jika ada kendala seperti fasilitas tidak sesuai atau pemilik tidak merespons, Anda bisa melapor melalui halaman Bantuan/Kontak. Tim KosYuk akan membantu menindaklanjuti masalah tersebut.',
                'category' => 'pencari_kos',
                'created_date' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'question' => 'Apakah data pribadi saya aman di KosYuk?',
                'answer' => 'Ya, kami menjaga keamanan data pribadi Anda dengan sistem enkripsi yang terbaru. Data Anda hanya digunakan untuk keperluan pemesanan kos dan tidak akan dibagikan ke pihak ketiga tanpa izin Anda.',
                'category' => 'umum',
                'created_date' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'question' => 'Bagaimana cara melakukan pembatalan booking?',
                'answer' => 'Untuk membatalkan booking, Anda dapat menghubungi pemilik kos langsung melalui kontak yang tersedia. Setiap kos mungkin memiliki kebijakan pembatalan yang berbeda, jadi pastikan untuk menanyakan hal ini sebelum melakukan booking.',
                'category' => 'pencari_kos',
                'created_date' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 6,
                'question' => 'Apakah ada biaya tambahan selain harga kos yang tertera?',
                'answer' => 'Harga yang tertera di platform sudah termasuk semua biaya dasar. Namun, beberapa kos mungkin memiliki biaya tambahan untuk listrik, air, atau layanan lainnya. Pastikan untuk menanyakan detail biaya kepada pemilik kos sebelum melakukan booking.',
                'category' => 'pencari_kos',
                'created_date' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach($default_faqs as $faq) {
            $_SESSION['faq_data'][] = $faq;
        }
    }
}

// Gabungkan semua pemilik kos
$all_owners = array_merge($_SESSION['pending_owners'], $_SESSION['verified_owners']);

// Fungsi untuk menghitung statistik
function countByStatus($owners, $status) {
    return count(array_filter($owners, function($owner) use ($status) {
        return ($owner['status'] ?? 'pending') === $status;
    }));
}

$total_owners = count($all_owners);
$verified_count = count($_SESSION['verified_owners']);
$pending_count = count($_SESSION['pending_owners']);
$total_seekers = count($_SESSION['registered_seekers']);
$total_messages = count($_SESSION['contact_messages']);
$total_faq = count($_SESSION['faq_data']);

// Hitung pesan belum dibaca
$unread_messages = 0;
foreach($_SESSION['contact_messages'] as $msg) {
    if(!isset($msg['read']) || $msg['read'] === false) {
        $unread_messages++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KosYuk</title>
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
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--gradient-primary);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .nav-menu {
            list-style: none;
            padding: 15px 0;
            flex: 1;
            overflow-y: auto;
        }

        .nav-item {
            margin: 3px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 20px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-light);
            padding-left: 22px;
        }

        .nav-link.active {
            background: rgba(24, 160, 251, 0.15);
            color: white;
            border-left-color: var(--primary-light);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Badge untuk jumlah pesan */
        .badge-count {
            position: absolute;
            right: 20px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }

        /* Logout Section di bawah */
        .logout-section {
            margin-top: auto;
            padding: 15px 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
        }

        .logout-btn {
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
            min-height: 100vh;
            width: calc(100% - 250px);
        }

        /* Header */
        .content-header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h1 {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--secondary);
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid #eaeaea;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            box-shadow: 0 3px 8px rgba(24, 160, 251, 0.3);
        }

        /* Section Content */
        .section-content {
            display: none;
        }

        .section-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Welcome Card dengan Animasi */
        .welcome-card {
            background: var(--gradient-primary);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(10, 44, 79, 0.3);
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
            animation: gentleGlow 15s linear infinite;
        }

        @keyframes gentleGlow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tambah efek cahaya berdenyut */
        .welcome-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                transparent 0%, 
                rgba(255,255,255,0.03) 25%, 
                rgba(255,255,255,0.05) 50%, 
                rgba(255,255,255,0.03) 75%, 
                transparent 100%);
            animation: pulseLight 4s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes pulseLight {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        .welcome-content {
            display: flex;
            align-items: center;
            gap: 25px;
            position: relative;
            z-index: 2;
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
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .welcome-text {
            flex: 1;
        }

        .welcome-text h3 {
            font-size: 1.8rem;
            margin-bottom: 12px;
            font-weight: 700;
            text-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { text-shadow: 0 2px 8px rgba(0,0,0,0.2); }
            to { text-shadow: 0 2px 12px rgba(255,255,255,0.4), 0 2px 8px rgba(0,0,0,0.2); }
        }

        .welcome-text p {
            font-size: 1.05rem;
            line-height: 1.6;
            opacity: 0.9;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Stats Grid - DIUBAH: tanpa garis pinggir */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none; /* Pastikan tidak ada border */
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        /* HAPUS: garis warna di pinggir stat-card */
        /* .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
        }

        .stat-card.total-owners::before { background: var(--primary); }
        .stat-card.verified-owners::before { background: var(--success); }
        .stat-card.pending-owners::before { background: var(--warning); }
        .stat-card.total-seekers::before { background: var(--primary-light); }
        .stat-card.total-messages::before { background: var(--info); }
        .stat-card.total-faq::before { background: #6f42c1; } */

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .total-owners .stat-icon { 
            background: linear-gradient(135deg, var(--primary), #1a4a7a);
        }
        .verified-owners .stat-icon { 
            background: linear-gradient(135deg, var(--success), #20c997);
        }
        .pending-owners .stat-icon { 
            background: linear-gradient(135deg, var(--warning), #fd7e14);
        }
        .total-seekers .stat-icon { 
            background: linear-gradient(135deg, var(--primary-light), #0dcaf0);
        }
        .total-messages .stat-icon { 
            background: linear-gradient(135deg, var(--info), #17a2b8);
        }
        .total-faq .stat-icon { 
            background: linear-gradient(135deg, #6f42c1, #d63384);
        }

        .stat-content {
            flex: 1;
            padding-left: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            line-height: 1;
        }

        .total-owners .stat-number { color: var(--primary); }
        .verified-owners .stat-number { color: var(--success); }
        .pending-owners .stat-number { color: var(--warning); }
        .total-seekers .stat-number { color: var(--primary-light); }
        .total-messages .stat-number { color: var(--info); }
        .total-faq .stat-number { color: #6f42c1; }

        .stat-label {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .stat-subtext {
            font-size: 0.85rem;
            color: var(--danger);
            margin-top: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            border: none; /* Pastikan tidak ada border */
        }

        .table-header {
            padding: 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: #fafafa;
        }

        .table-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .search-box {
            position: relative;
            min-width: 280px;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 18px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(24, 160, 251, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            color: var(--dark);
            font-weight: 700;
            border-bottom: 2px solid #eee;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
            vertical-align: middle;
            color: #495057;
        }

        tbody tr {
            transition: all 0.2s;
        }

        tbody tr:hover {
            background: #f8f9fa;
            transform: translateX(3px);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .status-pending {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-verified {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-unread {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            font-weight: bold;
            border: 1px solid #bee5eb;
        }

        .status-read {
            background: linear-gradient(135deg, #e2e3e5, #d6d8db);
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-action i {
            font-size: 0.9rem;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
        }

        .btn-verify:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--primary-light), #0dcaf0);
            color: white;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, #0b7dda, #0a58ca);
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger), #dc3545);
            color: white;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        .btn-mark-read {
            background: linear-gradient(135deg, var(--info), #17a2b8);
            color: white;
        }

        .btn-mark-read:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger), #dc3545);
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        .btn-add {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .btn-add:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--warning), #ffc107);
            color: black;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #e0a800, #d39e00);
        }

        .btn-cancel {
            background: linear-gradient(135deg, var(--gray), #6c757d);
            color: white;
        }

        .btn-cancel:hover {
            background: linear-gradient(135deg, #5a6268, #545b62);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            width: 100%;
            max-width: 700px;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalSlide 0.4s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: none; /* Pastikan tidak ada border */
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 22px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            border-radius: 15px 15px 0 0;
            background: #f8f9fa;
        }

        .modal-header h3 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.8rem;
            color: var(--gray);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: #f8f9fa;
            color: var(--danger);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
        }

        .detail-item {
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid #eee;
            animation: detailFadeIn 0.5s ease forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        @keyframes detailFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .detail-item:nth-child(1) { animation-delay: 0.1s; }
        .detail-item:nth-child(2) { animation-delay: 0.2s; }
        .detail-item:nth-child(3) { animation-delay: 0.3s; }
        .detail-item:nth-child(4) { animation-delay: 0.4s; }
        .detail-item:nth-child(5) { animation-delay: 0.5s; }
        .detail-item:nth-child(6) { animation-delay: 0.6s; }
        .detail-item:nth-child(7) { animation-delay: 0.7s; }
        .detail-item:nth-child(8) { animation-delay: 0.8s; }
        .detail-item:nth-child(9) { animation-delay: 0.9s; }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: var(--primary-light);
            font-size: 1rem;
        }

        .detail-value {
            color: var(--gray);
            line-height: 1.6;
            font-size: 1rem;
            padding-left: 26px;
        }

        /* Form dalam modal */
        .form-group {
            margin-bottom: 20px;
            animation: formFadeIn 0.5s ease;
        }

        @keyframes formFadeIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(24, 160, 251, 0.2);
            background: white;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 50px 30px;
            color: var(--gray);
            animation: noDataFade 1s ease;
        }

        @keyframes noDataFade {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
            color: var(--primary-light);
        }

        .no-data h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--dark);
            font-weight: 600;
        }

        .no-data p {
            font-size: 1rem;
            opacity: 0.8;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Message preview */
        .message-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* FAQ Item */
        .faq-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            animation: faqSlide 0.5s ease;
            opacity: 0;
            transform: translateX(-20px);
        }

        @keyframes faqSlide {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .faq-item:hover {
            background: #f8f9fa;
            transform: translateX(8px) translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .faq-question {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
            font-size: 1.1rem;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .faq-question::before {
            content: "Q:";
            color: var(--primary-light);
            font-weight: 800;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .faq-answer {
            color: var(--gray);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
        }

        .faq-answer::before {
            content: "A:";
            color: var(--success);
            font-weight: 800;
            position: absolute;
            left: 0;
            top: 0;
        }

        .faq-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 12px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            padding-left: 30px;
        }

        .faq-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .faq-meta i {
            font-size: 0.9rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 6px 20px rgba(10, 44, 79, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            animation: pulseToggle 2s infinite;
        }

        @keyframes pulseToggle {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .mobile-menu-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(10, 44, 79, 0.4);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .welcome-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .welcome-icon {
                width: 65px;
                height: 65px;
                font-size: 2rem;
            }
            
            .welcome-text h3 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar:hover {
                width: 250px;
            }
            
            .sidebar-header h2,
            .nav-link span,
            .badge-count {
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .sidebar:hover .sidebar-header h2,
            .sidebar:hover .nav-link span,
            .sidebar:hover .badge-count {
                opacity: 1;
            }
            
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar:hover + .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }
            
            .user-info {
                align-self: stretch;
                justify-content: space-between;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
                max-width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .modal-content {
                max-width: 95%;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .content-header {
                padding: 18px;
            }
            
            .content-header h1 {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-action {
                justify-content: center;
                padding: 10px;
            }
            
            .modal-content {
                margin: 10px;
                max-height: 90vh;
            }
            
            .welcome-card {
                padding: 25px 20px;
            }
            
            .welcome-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
            
            .welcome-text h3 {
                font-size: 1.4rem;
            }
            
            .welcome-text p {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar.active {
                width: 250px;
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .table-header h2 {
                font-size: 1.3rem;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            th, td {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            
            .status-badge {
                font-size: 0.8rem;
                padding: 5px 10px;
            }
        }

        /* Animasi untuk section content */
        .section-content {
            animation: sectionSlide 0.5s ease forwards;
        }

        @keyframes sectionSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar dengan Logout di Bawah -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-home"></i>
            </div>
            <h2>Admin Panel</h2>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#dashboard" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard Utama</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#verifikasi" class="nav-link">
                    <i class="fas fa-user-check"></i>
                    <span>Verifikasi Pemilik Kos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#pemilik-kos" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Data Pemilik Kos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#pencari-kos" class="nav-link">
                    <i class="fas fa-user-graduate"></i>
                    <span>Data Pencari Kos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#kelola-pesan" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Kelola Pesan</span>
                    <?php if($unread_messages > 0): ?>
                    <span class="badge-count"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="#faq-panduan" class="nav-link">
                    <i class="fas fa-question-circle"></i>
                    <span>FAQ Panduan</span>
                </a>
            </li>
        </ul>
        
        <!-- Logout Section di Bawah Sidebar -->
        <div class="logout-section">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_logout.php" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout Admin</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="content-header">
            <h1 id="section-title">Dashboard Utama</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--primary);">Administrator</div>
                    <div style="font-size: 0.85rem; color: var(--gray);">
                        <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1: Dashboard Utama -->
        <div id="dashboard" class="section-content active">
            <!-- Welcome Card dengan Animasi -->
            <div class="welcome-card">
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="welcome-text">
                        <h3>Selamat Datang, Admin KosYuk!</h3>
                        <p>Anda dapat kelola data pemilik dan pencari kos, melakukan verifikasi, melihat data, dan mengelola sistem KosYuk dengan mudah dan efisien.</p>
                    </div>
                </div>
            </div>

            <!-- Statistik Dashboard -->
            <div class="stats-grid">
                <div class="stat-card total-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_owners; ?></div>
                            <div class="stat-label">Total Pemilik Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card verified-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $verified_count; ?></div>
                            <div class="stat-label">Terverifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card pending-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Menunggu Verifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-seekers">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_seekers; ?></div>
                            <div class="stat-label">Total Pencari Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-messages">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_messages; ?></div>
                            <div class="stat-label">Total Pesan</div>
                            <?php if($unread_messages > 0): ?>
                            <div class="stat-subtext">
                                <i class="fas fa-envelope"></i>
                                <?php echo $unread_messages; ?> belum dibaca
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-faq">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_faq; ?></div>
                            <div class="stat-label">FAQ Panduan</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Verifikasi Pemilik Kos -->
        <div id="verifikasi" class="section-content">
            <!-- Statistik Verifikasi -->
            <div class="stats-grid">
                <div class="stat-card pending-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Menunggu Verifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card verified-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $verified_count; ?></div>
                            <div class="stat-label">Sudah Diverifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_owners; ?></div>
                            <div class="stat-label">Total Pemilik Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Verifikasi Pemilik Kos -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Verifikasi Pemilik Kos</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchVerification" placeholder="Cari nama kos atau pemilik...">
                    </div>
                </div>
                
                <?php if(empty($_SESSION['pending_owners'])): ?>
                    <div class="no-data">
                        <i class="fas fa-user-check"></i>
                        <h3>Tidak ada pemilik kos yang menunggu verifikasi</h3>
                        <p>Semua pemilik kos sudah diverifikasi</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kos</th>
                                    <th>Pemilik</th>
                                    <th>No. HP</th>
                                    <th>Email</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="verificationTable">
                                <?php foreach($_SESSION['pending_owners'] as $index => $owner): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($owner['kos_name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($owner['nama'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($owner['registration_date'] ?? 'now')); ?></td>
                                    <td>
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock me-1"></i>Menunggu
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" onclick="viewOwnerDetails('pending', <?php echo $index; ?>)">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <button class="btn-action btn-verify" onclick="verifyOwner(<?php echo $index; ?>)">
                                                <i class="fas fa-check"></i> Verifikasi
                                            </button>
                                            <button class="btn-action btn-reject" onclick="rejectOwner(<?php echo $index; ?>)">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 3: Data Pemilik Kos -->
        <div id="pemilik-kos" class="section-content">
            <!-- Statistik Data Pemilik Kos -->
            <div class="stats-grid">
                <div class="stat-card total-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_owners; ?></div>
                            <div class="stat-label">Total Pemilik Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card verified-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $verified_count; ?></div>
                            <div class="stat-label">Terverifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card pending-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Menunggu Verifikasi</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Pemilik Kos Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Data Pemilik Kos</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchOwner" placeholder="Cari nama kos atau pemilik...">
                    </div>
                </div>
                
                <?php if(empty($all_owners)): ?>
                    <div class="no-data">
                        <i class="fas fa-users"></i>
                        <h3>Belum ada data pemilik kos</h3>
                        <p>Belum ada pemilik kos yang terdaftar di sistem</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kos</th>
                                    <th>Pemilik</th>
                                    <th>No. HP</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ownerTable">
                                <?php foreach($all_owners as $index => $owner): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($owner['kos_name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($owner['nama'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $status = $owner['status'] ?? 'pending';
                                        $status_class = $status === 'verified' ? 'status-verified' : 'status-pending';
                                        $status_text = $status === 'verified' ? 'Terverifikasi' : 'Menunggu';
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas <?php echo $status === 'verified' ? 'fa-check-circle' : 'fa-clock'; ?> me-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" onclick="viewOwnerDetails('all', <?php echo $index; ?>)">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <?php if($status === 'pending'): ?>
                                                <button class="btn-action btn-verify" onclick="verifyOwner(<?php echo $index; ?>)">
                                                    <i class="fas fa-check"></i> Verifikasi
                                                </button>
                                                <button class="btn-action btn-reject" onclick="rejectOwner(<?php echo $index; ?>)">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 4: Data Pencari Kos -->
        <div id="pencari-kos" class="section-content">
            <!-- Statistik Pencari Kos -->
            <div class="stats-grid">
                <div class="stat-card total-seekers">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_seekers; ?></div>
                            <div class="stat-label">Total Pencari Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Pencari Kos Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Data Pencari Kos</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchPencari" placeholder="Cari nama atau email...">
                    </div>
                </div>
                
                <?php if(empty($_SESSION['registered_seekers'])): ?>
                    <div class="no-data">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Belum ada data pencari kos</h3>
                        <p>Belum ada pengguna yang mendaftar sebagai pencari kos</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. HP</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody id="pencariTable">
                                <?php foreach($_SESSION['registered_seekers'] as $index => $pencari): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($pencari['nama']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pencari['email']); ?></td>
                                    <td><?php echo htmlspecialchars($pencari['phone']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pencari['register_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 5: Kelola Pesan -->
        <div id="kelola-pesan" class="section-content">
            <!-- Statistik Pesan -->
            <div class="stats-grid">
                <div class="stat-card total-messages">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_messages; ?></div>
                            <div class="stat-label">Total Pesan</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card pending-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $unread_messages; ?></div>
                            <div class="stat-label">Belum Dibaca</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card verified-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_messages - $unread_messages; ?></div>
                            <div class="stat-label">Sudah Dibaca</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Pesan -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Kelola Pesan Pengunjung</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchMessages" placeholder="Cari nama, email, atau subjek...">
                    </div>
                </div>
                
                <?php if(empty($_SESSION['contact_messages'])): ?>
                    <div class="no-data">
                        <i class="fas fa-envelope"></i>
                        <h3>Belum ada pesan dari pengunjung</h3>
                        <p>Tidak ada pesan yang masuk melalui halaman kontak</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Subjek</th>
                                    <th>Pesan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="messagesTable">
                                <?php 
                                // Reverse untuk menampilkan pesan terbaru di atas
                                $reversed_messages = array_reverse($_SESSION['contact_messages']);
                                foreach($reversed_messages as $display_index => $message): 
                                    // Hitung indeks asli
                                    $actual_index = count($_SESSION['contact_messages']) - $display_index - 1;
                                ?>
                                <tr>
                                    <td><?php echo $display_index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($message['name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($message['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject'] ?? 'N/A'); ?></td>
                                    <td class="message-preview" title="<?php echo htmlspecialchars($message['message'] ?? ''); ?>">
                                        <?php echo substr($message['message'] ?? '', 0, 50) . (strlen($message['message'] ?? '') > 50 ? '...' : ''); ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($message['date'] ?? 'now')); ?></td>
                                    <td>
                                        <?php 
                                        $is_read = isset($message['read']) && $message['read'] === true;
                                        $status_class = $is_read ? 'status-read' : 'status-unread';
                                        $status_text = $is_read ? 'Dibaca' : 'Baru';
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas <?php echo $is_read ? 'fa-envelope-open' : 'fa-envelope'; ?> me-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" onclick="viewMessage(<?php echo $actual_index; ?>)">
                                                <i class="fas fa-eye"></i> Baca
                                            </button>
                                            <?php if(!$is_read): ?>
                                            <button class="btn-action btn-mark-read" onclick="markAsRead(<?php echo $actual_index; ?>)">
                                                <i class="fas fa-check"></i> Tandai
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn-action btn-delete" onclick="deleteMessage(<?php echo $actual_index; ?>)">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 6: FAQ Panduan -->
        <div id="faq-panduan" class="section-content">
            <!-- Statistik FAQ -->
            <div class="stats-grid">
                <div class="stat-card total-faq">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_faq; ?></div>
                            <div class="stat-label">Total FAQ</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card total-messages">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php 
                            $faq_categories = array_count_values(array_column($_SESSION['faq_data'], 'category'));
                            echo $faq_categories['umum'] ?? 0;
                            ?></div>
                            <div class="stat-label">FAQ Umum</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card verified-owners">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $faq_categories['pencari_kos'] ?? 0; ?></div>
                            <div class="stat-label">FAQ Pencari Kos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Tambah FAQ -->
            <div class="table-container">
                <div class="table-header">
                    <h2>FAQ Panduan KosYuk</h2>
                    <button class="btn-action btn-add" onclick="showAddFAQModal()">
                        <i class="fas fa-plus"></i> Tambah FAQ Baru
                    </button>
                </div>
                
                <div style="padding: 25px;">
                    <?php if(empty($_SESSION['faq_data'])): ?>
                        <div class="no-data">
                            <i class="fas fa-question-circle"></i>
                            <h3>Belum ada FAQ</h3>
                            <p>Klik tombol "Tambah FAQ Baru" untuk membuat FAQ pertama</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($_SESSION['faq_data'] as $index => $faq): ?>
                        <div class="faq-item" style="animation-delay: <?php echo $index * 0.1; ?>s">
                            <div class="faq-question">
                                <?php echo htmlspecialchars($faq['question']); ?>
                            </div>
                            <div class="faq-answer">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                            <div class="faq-meta">
                                <span>
                                    <i class="fas fa-tag"></i>
                                    Kategori: <?php echo ucfirst(str_replace('_', ' ', $faq['category'])); ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar-plus"></i>
                                    Dibuat: <?php echo date('d/m/Y H:i', strtotime($faq['created_date'])); ?>
                                </span>
                                <?php if(isset($faq['modified_date'])): ?>
                                <span>
                                    <i class="fas fa-calendar-edit"></i>
                                    Diedit: <?php echo date('d/m/Y H:i', strtotime($faq['modified_date'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="action-buttons" style="margin-top: 15px;">
                                <button class="btn-action btn-edit" onclick="editFAQ(<?php echo $index; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteFAQ(<?php echo $index; ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pemilik Kos -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pemilik Kos</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Detail akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesan -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pesan</h3>
                <button class="modal-close" onclick="closeMessageModal()">&times;</button>
            </div>
            <div class="modal-body" id="messageModalBody">
                <!-- Detail pesan akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit FAQ -->
    <div id="faqModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="faqModalTitle">Tambah FAQ Baru</h3>
                <button class="modal-close" onclick="closeFAQModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="faqForm" onsubmit="saveFAQ(event)">
                    <input type="hidden" id="faqIndex" value="">
                    <div class="form-group">
                        <label for="faqQuestion">
                            <i class="fas fa-question"></i> Pertanyaan
                        </label>
                        <input type="text" id="faqQuestion" required placeholder="Masukkan pertanyaan...">
                    </div>
                    <div class="form-group">
                        <label for="faqAnswer">
                            <i class="fas fa-comment-dots"></i> Jawaban
                        </label>
                        <textarea id="faqAnswer" required placeholder="Masukkan jawaban..." rows="5"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="faqCategory">
                            <i class="fas fa-tag"></i> Kategori
                        </label>
                        <select id="faqCategory" required>
                            <option value="">Pilih Kategori</option>
                            <option value="umum">Umum</option>
                            <option value="pencari_kos">Pencari Kos</option>
                            <option value="pemilik_kos">Pemilik Kos</option>
                            <option value="booking">Booking</option>
                            <option value="pembayaran">Pembayaran</option>
                        </select>
                    </div>
                    <div class="action-buttons" style="margin-top: 25px; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-action btn-cancel" onclick="closeFAQModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn-action btn-add">
                            <i class="fas fa-save"></i> Simpan FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Data dari PHP
        const pendingOwners = <?php echo json_encode($_SESSION['pending_owners']); ?>;
        const verifiedOwners = <?php echo json_encode($_SESSION['verified_owners']); ?>;
        const allOwners = <?php echo json_encode($all_owners); ?>;
        const contactMessages = <?php echo json_encode($_SESSION['contact_messages']); ?>;
        const faqData = <?php echo json_encode($_SESSION['faq_data']); ?>;
        
        // Toggle Sidebar untuk Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
            
            // Toggle icon
            const toggleIcon = document.querySelector('.mobile-menu-toggle i');
            if (sidebar.classList.contains('active')) {
                toggleIcon.className = 'fas fa-times';
            } else {
                toggleIcon.className = 'fas fa-bars';
            }
        }
        
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
                    
                    const targetSection = document.querySelector(href);
                    if (targetSection) {
                        targetSection.classList.add('active');
                        
                        // Animate FAQ items
                        if (href === '#faq-panduan') {
                            setTimeout(() => {
                                const faqItems = targetSection.querySelectorAll('.faq-item');
                                faqItems.forEach((item, index) => {
                                    item.style.animationDelay = (index * 0.1) + 's';
                                    item.style.animationFillMode = 'forwards';
                                });
                            }, 100);
                        }
                    }
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Close sidebar on mobile
                    if (window.innerWidth <= 480) {
                        document.getElementById('sidebar').classList.remove('active');
                        document.querySelector('.mobile-menu-toggle i').className = 'fas fa-bars';
                    }
                }
            });
        });
        
        // Search functionality
        function setupSearch(inputId, tableId) {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll(`#${tableId} tr`);
                    let foundCount = 0;
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                            foundCount++;
                            
                            // Add highlight animation
                            row.style.animation = 'none';
                            setTimeout(() => {
                                row.style.animation = 'highlightRow 0.5s ease';
                            }, 10);
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Update no data message if needed
                    const tableContainer = input.closest('.table-container');
                    const noDataMessage = tableContainer.querySelector('.no-data');
                    if (noDataMessage) {
                        noDataMessage.style.display = foundCount === 0 ? 'block' : 'none';
                    }
                });
                
                // Add highlight animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes highlightRow {
                        0% { background-color: rgba(24, 160, 251, 0.1); }
                        100% { background-color: transparent; }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Setup all search inputs
        setupSearch('searchVerification', 'verificationTable');
        setupSearch('searchOwner', 'ownerTable');
        setupSearch('searchPencari', 'pencariTable');
        setupSearch('searchMessages', 'messagesTable');
        
        // View owner details
        function viewOwnerDetails(type, index) {
            let owner;
            if (type === 'pending') {
                owner = pendingOwners[index];
            } else if (type === 'all') {
                owner = allOwners[index];
            } else {
                owner = verifiedOwners[index];
            }
            
            if (!owner) {
                alert('Data tidak ditemukan!');
                return;
            }
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-user"></i> Nama Pemilik
                    </div>
                    <div class="detail-value">${escapeHtml(owner.nama || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-envelope"></i> Email
                    </div>
                    <div class="detail-value">${escapeHtml(owner.email || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-phone"></i> No. Telepon
                    </div>
                    <div class="detail-value">${escapeHtml(owner.phone || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-home"></i> Nama Kos
                    </div>
                    <div class="detail-value">${escapeHtml(owner.kos_name || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-map-marker-alt"></i> Alamat Kos
                    </div>
                    <div class="detail-value">${escapeHtml(owner.kos_address || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-building"></i> Tipe Kos
                    </div>
                    <div class="detail-value">${escapeHtml(owner.kos_type || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-door-closed"></i> Jumlah Kamar
                    </div>
                    <div class="detail-value">${escapeHtml(owner.rooms || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-file-alt"></i> Deskripsi
                    </div>
                    <div class="detail-value">${escapeHtml(owner.description || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-badge-check"></i> Status
                    </div>
                    <div class="detail-value">
                        <span class="status-badge ${owner.status === 'verified' ? 'status-verified' : 'status-pending'}">
                            <i class="fas ${owner.status === 'verified' ? 'fa-check-circle' : 'fa-clock'} me-1"></i>
                            ${owner.status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi'}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar-plus"></i> Tanggal Pendaftaran
                    </div>
                    <div class="detail-value">${escapeHtml(owner.registration_date || 'N/A')}</div>
                </div>
                ${owner.verification_date ? `
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar-check"></i> Tanggal Verifikasi
                    </div>
                    <div class="detail-value">${escapeHtml(owner.verification_date)}</div>
                </div>
                ` : ''}
                ${owner.rejection_reason ? `
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-times-circle"></i> Alasan Penolakan
                    </div>
                    <div class="detail-value" style="color: var(--danger); font-weight: 500;">${escapeHtml(owner.rejection_reason)}</div>
                </div>
                ` : ''}
                <div class="action-buttons" style="margin-top: 25px; justify-content: center;">
                    <button class="btn-action btn-cancel" onclick="closeModal()">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    ${owner.status !== 'verified' ? `
                    <button class="btn-action btn-verify" onclick="verifyOwner(${index})">
                        <i class="fas fa-check"></i> Verifikasi
                    </button>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('detailModal').style.display = 'flex';
        }
        
        // Helper function untuk escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Verify owner
        function verifyOwner(index) {
            if (confirm('Apakah Anda yakin ingin memverifikasi pemilik kos ini?\nSetelah diverifikasi, pemilik dapat login dan mengelola kosnya.')) {
                showLoading('Memverifikasi...');
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'admin_verify.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    hideLoading();
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                showSuccess('Pemilik kos berhasil diverifikasi!');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showError('Gagal memverifikasi: ' + (response.message || 'Terjadi kesalahan'));
                            }
                        } catch (e) {
                            showError('Terjadi kesalahan dalam memproses respons server.');
                        }
                    } else {
                        showError('Gagal menghubungi server. Status: ' + this.status);
                    }
                };
                xhr.onerror = function() {
                    hideLoading();
                    showError('Tidak dapat terhubung ke server. Pastikan file admin_verify.php ada.');
                };
                xhr.send(`index=${index}&action=verify`);
            }
        }
        
        // Reject owner
        function rejectOwner(index) {
            const reason = prompt('Masukkan alasan penolakan:');
            if (reason !== null && reason.trim() !== '') {
                showLoading('Menolak pendaftaran...');
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'admin_verify.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    hideLoading();
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                showSuccess('Pemilik kos berhasil ditolak!');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showError('Gagal menolak: ' + (response.message || 'Terjadi kesalahan'));
                            }
                        } catch (e) {
                            showError('Terjadi kesalahan dalam memproses respons server.');
                        }
                    }
                };
                xhr.onerror = function() {
                    hideLoading();
                    showError('Tidak dapat terhubung ke server. Pastikan file admin_verify.php ada.');
                };
                xhr.send(`index=${index}&action=reject&reason=${encodeURIComponent(reason)}`);
            } else if (reason !== null) {
                showError('Alasan penolakan tidak boleh kosong!');
            }
        }
        
        // View message details
        function viewMessage(index) {
            const message = contactMessages[index];
            if (!message) {
                showError('Pesan tidak ditemukan!');
                return;
            }
            
            const modalBody = document.getElementById('messageModalBody');
            modalBody.innerHTML = `
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-user"></i> Nama Pengirim
                    </div>
                    <div class="detail-value">${escapeHtml(message.name || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-envelope"></i> Email
                    </div>
                    <div class="detail-value">${escapeHtml(message.email || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-heading"></i> Subjek
                    </div>
                    <div class="detail-value">${escapeHtml(message.subject || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-comment-dots"></i> Pesan
                    </div>
                    <div class="detail-value" style="white-space: pre-wrap; background: #f8f9fa; padding: 18px; border-radius: 8px; border-left: 4px solid var(--primary-light);">
                        ${escapeHtml(message.message || 'N/A')}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar-alt"></i> Tanggal Kirim
                    </div>
                    <div class="detail-value">${escapeHtml(message.date || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-eye"></i> Status
                    </div>
                    <div class="detail-value">
                        <span class="status-badge ${message.read ? 'status-read' : 'status-unread'}">
                            <i class="fas ${message.read ? 'fa-envelope-open' : 'fa-envelope'} me-1"></i>
                            ${message.read ? 'Sudah Dibaca' : 'Belum Dibaca'}
                        </span>
                    </div>
                </div>
                <div class="action-buttons" style="margin-top: 25px; justify-content: center; gap: 12px;">
                    ${!message.read ? `
                    <button class="btn-action btn-mark-read" onclick="markAsRead(${index}, true)">
                        <i class="fas fa-check"></i> Tandai sebagai Dibaca
                    </button>
                    ` : ''}
                    <button class="btn-action btn-delete" onclick="deleteMessage(${index})">
                        <i class="fas fa-trash"></i> Hapus Pesan
                    </button>
                    <button class="btn-action btn-cancel" onclick="closeMessageModal()">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            `;
            
            // Mark as read when viewing
            if (!message.read) {
                markAsRead(index, false);
            }
            
            document.getElementById('messageModal').style.display = 'flex';
        }
        
        // Mark message as read
        function markAsRead(index, reload = true) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin_messages.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    if (reload) {
                        showSuccess('Pesan ditandai sebagai telah dibaca!');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                }
            };
            xhr.onerror = function() {
                showError('Tidak dapat terhubung ke server. Pastikan file admin_messages.php ada.');
            };
            xhr.send(`index=${index}&action=mark_read`);
        }
        
        // Delete message
        function deleteMessage(index) {
            if (confirm('Apakah Anda yakin ingin menghapus pesan ini?\nTindakan ini tidak dapat dibatalkan.')) {
                showLoading('Menghapus pesan...');
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'admin_messages.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    hideLoading();
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                showSuccess('Pesan berhasil dihapus!');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            }
                        } catch (e) {
                            showError('Terjadi kesalahan dalam memproses respons server.');
                        }
                    }
                };
                xhr.onerror = function() {
                    hideLoading();
                    showError('Tidak dapat terhubung ke server. Pastikan file admin_messages.php ada.');
                };
                xhr.send(`index=${index}&action=delete`);
            }
        }
        
        // Show add FAQ modal
        function showAddFAQModal() {
            document.getElementById('faqModalTitle').textContent = 'Tambah FAQ Baru';
            document.getElementById('faqIndex').value = '';
            document.getElementById('faqQuestion').value = '';
            document.getElementById('faqAnswer').value = '';
            document.getElementById('faqCategory').value = '';
            document.getElementById('faqModal').style.display = 'flex';
            
            // Focus on first input
            setTimeout(() => {
                document.getElementById('faqQuestion').focus();
            }, 300);
        }
        
        // Edit FAQ
        function editFAQ(index) {
            const faq = faqData[index];
            if (!faq) {
                showError('FAQ tidak ditemukan!');
                return;
            }
            
            document.getElementById('faqModalTitle').textContent = 'Edit FAQ';
            document.getElementById('faqIndex').value = index;
            document.getElementById('faqQuestion').value = faq.question;
            document.getElementById('faqAnswer').value = faq.answer;
            document.getElementById('faqCategory').value = faq.category;
            document.getElementById('faqModal').style.display = 'flex';
            
            // Focus on first input
            setTimeout(() => {
                document.getElementById('faqQuestion').focus();
            }, 300);
        }
        
        // Save FAQ
        function saveFAQ(e) {
            e.preventDefault();
            
            const index = document.getElementById('faqIndex').value;
            const question = document.getElementById('faqQuestion').value.trim();
            const answer = document.getElementById('faqAnswer').value.trim();
            const category = document.getElementById('faqCategory').value;
            
            if (!question) {
                showError('Pertanyaan tidak boleh kosong!');
                document.getElementById('faqQuestion').focus();
                return;
            }
            
            if (!answer) {
                showError('Jawaban tidak boleh kosong!');
                document.getElementById('faqAnswer').focus();
                return;
            }
            
            if (!category) {
                showError('Kategori harus dipilih!');
                document.getElementById('faqCategory').focus();
                return;
            }
            
            showLoading(index === '' ? 'Menambahkan FAQ...' : 'Menyimpan perubahan...');
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin_faq.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                hideLoading();
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            showSuccess(index === '' ? 'FAQ berhasil ditambahkan!' : 'FAQ berhasil diperbarui!');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showError('Gagal menyimpan: ' + (response.message || 'Terjadi kesalahan'));
                        }
                    } catch (e) {
                        showError('Terjadi kesalahan dalam memproses respons server.');
                    }
                }
            };
            xhr.onerror = function() {
                hideLoading();
                showError('Tidak dapat terhubung ke server. Pastikan file admin_faq.php ada.');
            };
            xhr.send(`index=${index}&question=${encodeURIComponent(question)}&answer=${encodeURIComponent(answer)}&category=${encodeURIComponent(category)}`);
        }
        
        // Delete FAQ
        function deleteFAQ(index) {
            if (confirm('Apakah Anda yakin ingin menghapus FAQ ini?\nTindakan ini tidak dapat dibatalkan.')) {
                showLoading('Menghapus FAQ...');
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'admin_faq.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    hideLoading();
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                showSuccess('FAQ berhasil dihapus!');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showError('Gagal menghapus FAQ: ' + (response.message || 'Terjadi kesalahan'));
                            }
                        } catch (e) {
                            showError('Terjadi kesalahan dalam memproses respons server.');
                        }
                    }
                };
                xhr.onerror = function() {
                    hideLoading();
                    showError('Tidak dapat terhubung ke server. Pastikan file admin_faq.php ada.');
                };
                xhr.send(`index=${index}&action=delete`);
            }
        }
        
        // Close modals
        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        function closeFAQModal() {
            document.getElementById('faqModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['detailModal', 'messageModal', 'faqModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    if (modalId === 'detailModal') closeModal();
                    if (modalId === 'messageModal') closeMessageModal();
                    if (modalId === 'faqModal') closeFAQModal();
                }
            });
        }
        
        // Stat cards hover effect
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
            });
        });
        
        // Notification functions
        function showLoading(message) {
            // Remove existing loading
            hideLoading();
            
            const loading = document.createElement('div');
            loading.id = 'global-loading';
            loading.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                ">
                    <div style="
                        background: white;
                        padding: 30px;
                        border-radius: 15px;
                        text-align: center;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                        animation: pulse 1.5s infinite;
                    ">
                        <div style="
                            width: 60px;
                            height: 60px;
                            border: 5px solid #f3f3f3;
                            border-top: 5px solid var(--primary-light);
                            border-radius: 50%;
                            margin: 0 auto 20px;
                            animation: spin 1s linear infinite;
                        "></div>
                        <div style="
                            color: var(--primary);
                            font-weight: 600;
                            font-size: 1.1rem;
                        ">${message}</div>
                    </div>
                </div>
            `;
            document.body.appendChild(loading);
            
            // Add animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes pulse {
                    0%, 100% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.05); opacity: 0.9; }
                }
            `;
            document.head.appendChild(style);
        }
        
        function hideLoading() {
            const loading = document.getElementById('global-loading');
            if (loading) {
                loading.remove();
            }
        }
        
        function showSuccess(message) {
            showNotification(message, 'success');
        }
        
        function showError(message) {
            showNotification(message, 'error');
        }
        
        function showNotification(message, type) {
            // Remove existing notifications
            const existing = document.querySelectorAll('.global-notification');
            existing.forEach(el => el.remove());
            
            const notification = document.createElement('div');
            notification.className = 'global-notification';
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 'linear-gradient(135deg, #dc3545, #fd7e14)'};
                    color: white;
                    padding: 15px 25px;
                    border-radius: 10px;
                    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    z-index: 9998;
                    animation: slideIn 0.3s ease;
                ">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" 
                       style="font-size: 1.3rem;"></i>
                    <div style="font-weight: 500;">${message}</div>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, 3000);
            
            // Add animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Auto-hide mobile menu toggle on desktop
        function checkScreenSize() {
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            if (mobileToggle) {
                if (window.innerWidth > 480) {
                    mobileToggle.style.display = 'none';
                } else {
                    mobileToggle.style.display = 'flex';
                }
            }
        }
        
        // Animate FAQ items on page load
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach((item, index) => {
                item.style.animationDelay = (index * 0.1) + 's';
                item.style.animationFillMode = 'forwards';
            });
        });
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize(); // Initial check
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to close modals
            if (e.key === 'Escape') {
                closeModal();
                closeMessageModal();
                closeFAQModal();
            }
            
            // Ctrl/Cmd + S to save FAQ form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const faqForm = document.getElementById('faqForm');
                if (faqForm && document.getElementById('faqModal').style.display === 'flex') {
                    const submitBtn = faqForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.click();
                    }
                }
            }
        });
    </script>
</body>
</html>