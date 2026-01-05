<?php
session_start();

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pencari_kos') {
    // Jika admin preview mode, boleh akses
    if (!isset($_SESSION['admin_preview_mode']) || $_SESSION['admin_preview_mode'] !== true) {
        header("Location: login.php");
        exit;
    }
}

// Jika admin dalam preview mode
$is_admin_preview = isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true;

// Ambil parameter pencarian dengan sanitasi
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$harga_filter = isset($_GET['harga']) ? $_GET['harga'] : '';
$fasilitas_filter = isset($_GET['fasilitas']) ? $_GET['fasilitas'] : '';
$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$jarak_filter = isset($_GET['jarak']) ? $_GET['jarak'] : '';

// DEBUG: Tampilkan parameter yang diterima
error_log("Parameter pencarian diterima:");
error_log("Keyword: " . $keyword);
error_log("Harga: " . $harga_filter);
error_log("Fasilitas: " . $fasilitas_filter);
error_log("Jenis: " . $jenis_filter);
error_log("Jarak: " . $jarak_filter);

// Data kos lengkap (simulasi database) - Pastikan semua fasilitas menggunakan nilai yang sama dengan form
$semua_kos = [
    [
        'id' => 1,
        'nama' => 'Mawar Indah',
        'lokasi' => 'Karangwangkal, Purwokerto',
        'jenis' => 'putra',
        'harga' => 6000000,
        'jarak' => 300, // dalam meter
        'fasilitas' => ['wifi', 'ac', 'km_dalam', 'parkir', 'dapur'],
        'gambar' => 'assets/images/kos1.jpg',
        'sisa_kamar' => 3,
        'deskripsi' => 'Kos nyaman untuk mahasiswa teknik, dekat kampus',
        'kontak' => '081234567890'
    ],
    [
        'id' => 2,
        'nama' => 'Pelangi Asri',
        'lokasi' => 'Grendeng, Purwokerto',
        'jenis' => 'putri',
        'harga' => 7500000,
        'jarak' => 800,
        'fasilitas' => ['wifi', 'laundry', 'dapur', 'ac', 'km_dalam'],
        'gambar' => 'assets/images/kos2.jpg',
        'sisa_kamar' => 2,
        'deskripsi' => 'Kos strategis, 5 menit jalan kaki ke kampus',
        'kontak' => '081234567891'
    ],
    [
        'id' => 3,
        'nama' => 'Melati Sejahtera',
        'lokasi' => 'Purwokerto Timur',
        'jenis' => 'putra',
        'harga' => 5500000,
        'jarak' => 1200,
        'fasilitas' => ['wifi', 'parkir'],
        'gambar' => 'assets/images/kos3.jpg',
        'sisa_kamar' => 5,
        'deskripsi' => 'Kos ekonomis dengan fasilitas cukup',
        'kontak' => '081234567892'
    ],
    [
        'id' => 4,
        'nama' => 'Anggrek Harmoni',
        'lokasi' => 'Kecamatan Salaman',
        'jenis' => 'putri',
        'harga' => 8500000,
        'jarak' => 400,
        'fasilitas' => ['wifi', 'ac', 'dapur', 'laundry', 'km_dalam'],
        'gambar' => 'assets/images/kos4.jpg',
        'sisa_kamar' => 1,
        'deskripsi' => 'Kos eksklusif dengan fasilitas lengkap',
        'kontak' => '081234567893'
    ],
    [
        'id' => 5,
        'nama' => 'Harmoni Residence',
        'lokasi' => 'Karangwangkal No. 200',
        'jenis' => 'putra',
        'harga' => 5000000,
        'jarak' => 600,
        'fasilitas' => ['wifi', 'km_dalam', 'parkir'],
        'gambar' => 'assets/images/kos5.jpg',
        'sisa_kamar' => 4,
        'deskripsi' => 'Kos baru dengan desain modern',
        'kontak' => '081234567894'
    ],
    [
        'id' => 6,
        'nama' => 'Merpati Indah',
        'lokasi' => 'Jl. Grendeng No. 45',
        'jenis' => 'putri',
        'harga' => 9000000,
        'jarak' => 1500,
        'fasilitas' => ['wifi', 'ac', 'laundry', 'dapur', 'km_dalam'],
        'gambar' => 'assets/images/kos6.jpg',
        'sisa_kamar' => 3,
        'deskripsi' => 'Kos dengan taman dan area bersantai',
        'kontak' => '081234567895'
    ],
    [
        'id' => 7,
        'nama' => 'Sejahtera Abadi',
        'lokasi' => 'Jl. Purwokerto Timur No. 89',
        'jenis' => 'putra',
        'harga' => 4500000,
        'jarak' => 3000,
        'fasilitas' => ['wifi', 'dapur', 'parkir'],
        'gambar' => 'assets/images/kos7.jpg',
        'sisa_kamar' => 6,
        'deskripsi' => 'Kos keluarga, cocok untuk mahasiswa S2/S3',
        'kontak' => '081234567896'
    ],
    [
        'id' => 8,
        'nama' => 'Anggrek Asri',
        'lokasi' => 'Jl. Salaman No. 34',
        'jenis' => 'putri',
        'harga' => 11000000,
        'jarak' => 2500,
        'fasilitas' => ['wifi', 'ac', 'km_dalam', 'dapur', 'laundry'],
        'gambar' => 'assets/images/kos8.jpg',
        'sisa_kamar' => 2,
        'deskripsi' => 'Kos premium dengan view bagus dan udara sejuk',
        'kontak' => '081234567897'
    ],
    [
        'id' => 9,
        'nama' => 'Dahlia Residence',
        'lokasi' => 'Jl. Kaliwungu No. 56',
        'jenis' => 'putra',
        'harga' => 4000000,
        'jarak' => 4500,
        'fasilitas' => ['wifi'],
        'gambar' => 'assets/images/kos9.jpg',
        'sisa_kamar' => 8,
        'deskripsi' => 'Kos ekonomis dengan fasilitas dasar',
        'kontak' => '081234567898'
    ],
    [
        'id' => 10,
        'nama' => 'Kenanga Indah',
        'lokasi' => 'Jl. Purwokerto Barat No. 123',
        'jenis' => 'putri',
        'harga' => 9500000,
        'jarak' => 5000,
        'fasilitas' => ['wifi', 'ac', 'dapur', 'laundry', 'parkir'],
        'gambar' => 'assets/images/kos10.jpg',
        'sisa_kamar' => 1,
        'deskripsi' => 'Kos premium dengan keamanan 24 jam',
        'kontak' => '081234567899'
    ]
];

// FUNGSI FILTER KOS yang DIPERBAIKI
function filterKos($kos, $keyword, $harga_filter, $fasilitas_filter, $jenis_filter, $jarak_filter) {
    $match = true;
    
    // DEBUG: Log untuk setiap kos yang diperiksa
    error_log("Memeriksa kos: " . $kos['nama']);
    
    // 1. Filter berdasarkan keyword (nama, lokasi, atau deskripsi)
    if (!empty($keyword)) {
        $keyword_lower = strtolower($keyword);
        $nama_lower = strtolower($kos['nama']);
        $lokasi_lower = strtolower($kos['lokasi']);
        $deskripsi_lower = strtolower($kos['deskripsi']);
        
        $found_in_nama = strpos($nama_lower, $keyword_lower) !== false;
        $found_in_lokasi = strpos($lokasi_lower, $keyword_lower) !== false;
        $found_in_deskripsi = strpos($deskripsi_lower, $keyword_lower) !== false;
        
        if (!$found_in_nama && !$found_in_lokasi && !$found_in_deskripsi) {
            error_log("  - Tidak cocok keyword: $keyword_lower");
            $match = false;
        } else {
            error_log("  - Cocok keyword: $keyword_lower");
        }
    }
    
    // 2. Filter berdasarkan harga maksimal - DIPERBAIKI
    if (!empty($harga_filter) && $match) {
        $harga_int = (int)$harga_filter;
        $kos_harga = (int)$kos['harga'];
        
        error_log("  - Filter harga: $harga_int, Harga kos: $kos_harga");
        
        if ($harga_int > 0) {
            switch($harga_int) {
                case 5000000:
                    if ($kos_harga > 5000000) {
                        $match = false;
                        error_log("  - Harga kos > 5 juta, tidak lolos");
                    }
                    break;
                case 10000000:
                    if ($kos_harga < 5000000 || $kos_harga > 10000000) {
                        $match = false;
                        error_log("  - Harga kos tidak dalam range 5-10 juta, tidak lolos");
                    }
                    break;
                case 15000000:
                    if ($kos_harga < 10000000 || $kos_harga > 15000000) {
                        $match = false;
                        error_log("  - Harga kos tidak dalam range 10-15 juta, tidak lolos");
                    }
                    break;
                case 20000000:
                    if ($kos_harga < 15000000) {
                        $match = false;
                        error_log("  - Harga kos < 15 juta, tidak lolos untuk filter >15 juta");
                    }
                    break;
            }
        }
    }
    
    // 3. Filter berdasarkan fasilitas
    if (!empty($fasilitas_filter) && $match) {
        // Cek apakah fasilitas yang dicari ada dalam array fasilitas kos
        if (!in_array($fasilitas_filter, $kos['fasilitas'])) {
            error_log("  - Fasilitas $fasilitas_filter tidak ditemukan");
            $match = false;
        } else {
            error_log("  - Fasilitas $fasilitas_filter ditemukan");
        }
    }
    
    // 4. Filter berdasarkan jenis kos
    if (!empty($jenis_filter) && $match) {
        if ($kos['jenis'] !== $jenis_filter) {
            error_log("  - Jenis tidak cocok: " . $kos['jenis'] . " vs $jenis_filter");
            $match = false;
        } else {
            error_log("  - Jenis cocok: $jenis_filter");
        }
    }
    
    // 5. Filter berdasarkan jarak maksimal dari kampus
    if (!empty($jarak_filter) && $match) {
        $jarak_int = (int)$jarak_filter;
        $kos_jarak = (int)$kos['jarak'];
        
        error_log("  - Filter jarak: $jarak_int, Jarak kos: $kos_jarak");
        
        if ($jarak_int > 0) {
            switch($jarak_int) {
                case 500:
                    if ($kos_jarak > 500) {
                        $match = false;
                        error_log("  - Jarak kos > 500m, tidak lolos");
                    }
                    break;
                case 1000:
                    if ($kos_jarak < 500 || $kos_jarak > 1000) {
                        $match = false;
                        error_log("  - Jarak kos tidak dalam range 500m-1km, tidak lolos");
                    }
                    break;
                case 5000:
                    if ($kos_jarak < 1000 || $kos_jarak > 5000) {
                        $match = false;
                        error_log("  - Jarak kos tidak dalam range 1-5km, tidak lolos");
                    }
                    break;
            }
        }
    }
    
    error_log("  - Hasil filter: " . ($match ? "LOLOS" : "TIDAK LOLOS"));
    return $match;
}

// Terapkan filter ke semua kos
$hasil_filter = [];
foreach ($semua_kos as $kos) {
    if (filterKos($kos, $keyword, $harga_filter, $fasilitas_filter, $jenis_filter, $jarak_filter)) {
        // Format jarak untuk display
        if ($kos['jarak'] < 1000) {
            $kos['jarak_display'] = $kos['jarak'] . 'm';
        } else {
            $kos['jarak_display'] = number_format($kos['jarak']/1000, 1) . 'km';
        }
        $hasil_filter[] = $kos;
    }
}

$total_kos = count($hasil_filter);

// DEBUG: Tampilkan total hasil
error_log("Total kos ditemukan: " . $total_kos);

// Fungsi untuk mendapatkan label fasilitas
function getFasilitasLabel($fasilitas) {
    $labels = [
        'wifi' => 'WiFi',
        'ac' => 'AC',
        'km_dalam' => 'Kamar Mandi Dalam',
        'parkir' => 'Parkir',
        'dapur' => 'Dapur',
        'laundry' => 'Laundry',
        'kulkas' => 'Kulkas',
        'tv' => 'TV',
        'taman' => 'Taman',
        'gym' => 'Gym',
        'security' => 'Security 24 Jam'
    ];
    
    return $labels[$fasilitas] ?? ucfirst($fasilitas);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Kos - KosYuk</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard_pencarikos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== MAIN LAYOUT ========== */
        .main-content {
            padding-top: 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* ========== BREADCRUMB ========== */
        .breadcrumb {
            margin-bottom: 30px;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .breadcrumb-links {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }
        
        .breadcrumb-links a {
            color: #0A2C4F;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .breadcrumb-links a:hover {
            color: #083162;
        }
        
        .breadcrumb-links i {
            font-size: 12px;
        }
        
        /* ========== PAGE HEADER ========== */
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .page-title {
            font-size: 28px;
            color: #0A2C4F;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 25px;
        }
        
        /* ========== ACTIVE FILTERS ========== */
        .active-filters-container {
            background: #f8f9ff;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #0A2C4F;
            margin-top: 20px;
        }
        
        .filters-title {
            font-size: 16px;
            font-weight: 600;
            color: #0A2C4F;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filters-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e3f2fd;
            color: #0A2C4F;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .filter-tag .remove {
            color: #666;
            cursor: pointer;
            margin-left: 5px;
            font-size: 12px;
        }
        
        /* ========== SEARCH RESULTS ========== */
        .results-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .results-count {
            font-size: 18px;
            color: #0A2C4F;
            font-weight: 600;
        }
        
        .results-count span {
            color: #4CAF50;
            background: #e8f5e9;
            padding: 3px 10px;
            border-radius: 15px;
            margin-left: 8px;
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sort-options select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: white;
            font-size: 14px;
        }
        
        /* ========== KOS GRID ========== */
        .kos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .kos-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .kos-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .kos-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .kos-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .kos-card:hover .kos-image img {
            transform: scale(1.05);
        }
        
        .kos-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(5px);
        }
        
        .kos-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .kos-header {
            margin-bottom: 15px;
        }
        
        .kos-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e8f4ff;
            color: #0A2C4F;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .kos-name {
            font-size: 18px;
            color: #0A2C4F;
            margin: 0 0 5px 0;
            font-weight: 700;
        }
        
        .kos-location {
            color: #666;
            font-size: 14px;
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kos-distance {
            background: #f0f8ff;
            color: #0A2C4F;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-top: 5px;
        }
        
        .kos-facilities {
            margin: 15px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .facility-tag {
            background: #f5f5f5;
            color: #555;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .kos-footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .kos-price {
            color: #d32f2f;
            font-weight: 700;
            font-size: 18px;
        }
        
        .kos-price span {
            font-size: 12px;
            color: #888;
            font-weight: normal;
        }
        
        .detail-btn {
            background: #0A2C4F;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .detail-btn:hover {
            background: #083162;
        }
        
        /* ========== NO RESULTS ========== */
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-results-icon {
            font-size: 70px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #666;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .no-results p {
            color: #888;
            max-width: 500px;
            margin: 0 auto 25px;
            line-height: 1.6;
        }
        
        /* ========== DEBUG INFO ========== */
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            font-size: 13px;
            color: #856404;
        }
        
        .debug-info h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #856404;
        }
        
        /* ========== ACTION BUTTONS ========== */
        .action-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .btn-outline {
            background: white;
            color: #0A2C4F;
            border: 2px solid #0A2C4F;
        }
        
        .btn-outline:hover {
            background: #0A2C4F;
            color: white;
        }
        
        .btn-solid {
            background: #0A2C4F;
            color: white;
            border: 2px solid #0A2C4F;
        }
        
        .btn-solid:hover {
            background: #083162;
            border-color: #083162;
        }
        
        /* ========== RESPONSIVE ========== */
        @media (max-width: 1200px) {
            .kos-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding-top: 60px;
            }
            
            .container {
                padding: 15px;
            }
            
            .kos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-section {
                flex-direction: column;
                gap: 20px;
                align-items: stretch;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
        
        /* ========== ANIMATIONS ========== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .kos-card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>

<!-- ========= ADMIN PREVIEW BANNER ========= -->
<?php if ($is_admin_preview): ?>
<div class="admin-banner">
    <div class="admin-banner-content">
        <i class="fas fa-eye"></i>
        <strong>Admin Preview Mode</strong> - Anda sedang melihat hasil pencarian sebagai pencari kos.
        <a href="admin_dashboard.php" class="admin-back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Admin
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ========= INCLUDE HEADER ========= -->
<?php include 'includes/header_dashboard.php'; ?>

<div class="main-content">
    <div class="container">
        
        <!-- ========= BREADCRUMB ========= -->
        <div class="breadcrumb">
            <div class="breadcrumb-links">
                <a href="dashboard_pencarikos.php"><i class="fas fa-home"></i> Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <span>Hasil Pencarian</span>
            </div>
        </div>
        
        <!-- ========= PAGE HEADER ========= -->
        <div class="page-header">
            <h1 class="page-title">🔍 Hasil Pencarian Kos</h1>
            <p class="page-subtitle">Temukan kos impian Anda di sekitar Fakultas Teknik UNSOED</p>
            
            <!-- ========= DEBUG INFO UNTUK TESTING ========= -->
            <div class="debug-info">
                <h4>Debug Informasi Pencarian:</h4>
                <p><strong>Parameter yang diterima:</strong></p>
                <ul>
                    <li>Keyword: "<?php echo htmlspecialchars($keyword); ?>"</li>
                    <li>Harga Filter: "<?php echo $harga_filter; ?>"</li>
                    <li>Fasilitas Filter: "<?php echo $fasilitas_filter; ?>"</li>
                    <li>Jenis Filter: "<?php echo $jenis_filter; ?>"</li>
                    <li>Jarak Filter: "<?php echo $jarak_filter; ?>"</li>
                </ul>
                <p><strong>Total Data Kos:</strong> <?php echo count($semua_kos); ?> kos</p>
                <p><strong>Hasil Filter:</strong> <?php echo $total_kos; ?> kos ditemukan</p>
                <?php if ($total_kos > 0): ?>
                <p><strong>Kos yang ditemukan:</strong> 
                    <?php 
                    $nama_kos = [];
                    foreach ($hasil_filter as $kos) {
                        $nama_kos[] = $kos['nama'];
                    }
                    echo implode(', ', $nama_kos);
                    ?>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- ========= ACTIVE FILTERS ========= -->
            <?php if ($keyword || $harga_filter || $fasilitas_filter || $jenis_filter || $jarak_filter): ?>
            <div class="active-filters-container">
                <div class="filters-title">
                    <i class="fas fa-filter"></i>
                    Filter Aktif:
                </div>
                <div class="filters-grid">
                    <?php if ($keyword): ?>
                    <div class="filter-tag">
                        <i class="fas fa-search"></i>
                        <?php echo htmlspecialchars($keyword); ?>
                        <span class="remove" onclick="removeFilter('keyword')">&times;</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($harga_filter): ?>
                    <div class="filter-tag">
                        <i class="fas fa-tag"></i>
                        <?php 
                        switch($harga_filter) {
                            case '5000000': echo 'Harga < 5 juta'; break;
                            case '10000000': echo 'Harga 5-10 juta'; break;
                            case '15000000': echo 'Harga 10-15 juta'; break;
                            case '20000000': echo 'Harga > 15 juta'; break;
                            default: echo 'Filter Harga';
                        }
                        ?>
                        <span class="remove" onclick="removeFilter('harga')">&times;</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($fasilitas_filter): ?>
                    <div class="filter-tag">
                        <i class="fas fa-wifi"></i>
                        <?php echo getFasilitasLabel($fasilitas_filter); ?>
                        <span class="remove" onclick="removeFilter('fasilitas')">&times;</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($jenis_filter): ?>
                    <div class="filter-tag">
                        <i class="fas fa-<?php echo $jenis_filter == 'putra' ? 'male' : 'female'; ?>"></i>
                        Kos <?php echo ucfirst($jenis_filter); ?>
                        <span class="remove" onclick="removeFilter('jenis')">&times;</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($jarak_filter): ?>
                    <div class="filter-tag">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php 
                        switch($jarak_filter) {
                            case '500': echo 'Jarak 0-500m'; break;
                            case '1000': echo 'Jarak 500m-1km'; break;
                            case '5000': echo 'Jarak 1-5km'; break;
                            default: echo 'Filter Jarak';
                        }
                        ?>
                        <span class="remove" onclick="removeFilter('jarak')">&times;</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ========= SEARCH RESULTS ========= -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    Hasil Pencarian: 
                    <span id="resultsCount"><?php echo $total_kos; ?></span> Kos ditemukan
                </div>
                
                <div class="sort-options">
                    <label for="sortBy">Urutkan:</label>
                    <select id="sortBy" onchange="sortResults(this.value)">
                        <option value="jarak">Jarak Terdekat</option>
                        <option value="harga_asc">Harga Termurah</option>
                        <option value="harga_desc">Harga Termahal</option>
                        <option value="nama">Nama A-Z</option>
                    </select>
                </div>
            </div>
            
            <!-- ========= KOS GRID ========= -->
            <div class="kos-grid" id="kosGrid">
                <?php if ($total_kos > 0): ?>
                    <?php foreach ($hasil_filter as $index => $kos): 
                        $harga_format = number_format($kos['harga'], 0, ',', '.');
                        $jenis_icon = $kos['jenis'] == 'putra' ? 'male' : 'female';
                        $fasilitas_display = array_slice($kos['fasilitas'], 0, 3); // Tampilkan maksimal 3 fasilitas
                    ?>
                    <div class="kos-card" data-id="<?php echo $kos['id']; ?>" 
                         data-jarak="<?php echo $kos['jarak']; ?>"
                         data-harga="<?php echo $kos['harga']; ?>"
                         data-nama="<?php echo $kos['nama']; ?>">
                        <div class="kos-image">
                            <img src="<?php echo $kos['gambar']; ?>" 
                                 alt="Kos <?php echo $kos['nama']; ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=400&h=250&fit=crop'">
                            <div class="kos-badge">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $kos['jarak_display']; ?>
                            </div>
                        </div>
                        
                        <div class="kos-content">
                            <div class="kos-header">
                                <div class="kos-type">
                                    <i class="fas fa-<?php echo $jenis_icon; ?>"></i>
                                    Kos <?php echo ucfirst($kos['jenis']); ?>
                                </div>
                                <h3 class="kos-name">Kos <?php echo $kos['nama']; ?></h3>
                                <div class="kos-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $kos['lokasi']; ?>
                                </div>
                                <div class="kos-distance">
                                    <i class="fas fa-walking"></i> <?php echo $kos['jarak_display']; ?> dari kampus
                                </div>
                            </div>
                            
                            <div class="kos-facilities">
                                <?php foreach ($fasilitas_display as $fasilitas): ?>
                                <div class="facility-tag">
                                    <i class="fas fa-check"></i>
                                    <?php echo getFasilitasLabel($fasilitas); ?>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($kos['fasilitas']) > 3): ?>
                                <div class="facility-tag">
                                    +<?php echo count($kos['fasilitas']) - 3; ?> lainnya
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="kos-footer">
                                <div class="kos-price">
                                    Rp <?php echo $harga_format; ?>
                                    <span>/tahun</span>
                                </div>
                                <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="detail-btn">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- ========= NO RESULTS ========= -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Maaf, tidak ada kos yang sesuai</h3>
                        <p>Kami tidak menemukan kos yang sesuai dengan kriteria pencarian Anda. Coba gunakan filter yang berbeda atau periksa semua kos yang tersedia.</p>
                        <div style="margin-top: 30px;">
                            <a href="dashboard_pencarikos.php" class="btn btn-outline" style="margin-right: 10px;">
                                <i class="fas fa-redo"></i> Reset Filter
                            </a>
                            <a href="semua_kos.php" class="btn btn-solid">
                                <i class="fas fa-list"></i> Lihat Semua Kos
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- ========= ACTION BUTTONS ========= -->
            <?php if ($total_kos > 0): ?>
            <div class="action-section">
                <div class="action-buttons">
                    <a href="dashboard_pencarikos.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                    <a href="semua_kos.php" class="btn btn-solid">
                        <i class="fas fa-list"></i> Lihat Semua Kos
                    </a>
                </div>
                
                <div style="color: #666; font-size: 14px;">
                    Menampilkan <?php echo $total_kos; ?> dari <?php echo count($semua_kos); ?> kos
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========= INCLUDE FOOTER ========= -->
<?php include 'includes/footer.php'; ?>

<script>
// Fungsi untuk menghapus filter
function removeFilter(filterName) {
    const url = new URL(window.location.href);
    url.searchParams.delete(filterName);
    window.location.href = url.toString();
}

// Fungsi untuk mengurutkan hasil
function sortResults(sortBy) {
    const kosCards = Array.from(document.querySelectorAll('.kos-card'));
    const container = document.getElementById('kosGrid');
    
    // Sort berdasarkan kriteria
    kosCards.sort((a, b) => {
        switch(sortBy) {
            case 'jarak':
                return parseInt(a.dataset.jarak) - parseInt(b.dataset.jarak);
            case 'harga_asc':
                return parseInt(a.dataset.harga) - parseInt(b.dataset.harga);
            case 'harga_desc':
                return parseInt(b.dataset.harga) - parseInt(a.dataset.harga);
            case 'nama':
                return a.dataset.nama.localeCompare(b.dataset.nama);
            default:
                return 0;
        }
    });
    
    // Hapus semua kartu dari container
    while (container.firstChild) {
        container.removeChild(container.firstChild);
    }
    
    // Tambahkan kembali kartu yang sudah diurutkan
    kosCards.forEach(card => {
        container.appendChild(card);
    });
}

// Animasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kos-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
    });
});

// Fungsi untuk share pencarian
function shareSearch() {
    if (navigator.share) {
        navigator.share({
            title: 'Hasil Pencarian Kos - KosYuk',
            text: 'Saya menemukan <?php echo $total_kos; ?> kos yang bagus di KosYuk!',
            url: window.location.href
        });
    } else {
        // Fallback untuk browser lama
        navigator.clipboard.writeText(window.location.href);
        alert('Link pencarian telah disalin ke clipboard!');
    }
}
</script>
</body>
</html>