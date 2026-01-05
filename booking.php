<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek role - hanya pencari kos yang bisa booking
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pencari_kos') {
    header("Location: login.php");
    exit;
}

// Ambil ID kos dari URL
$id_kos = isset($_GET['id']) ? intval($_GET['id']) : 0;

// INCLUDE DATA KOS YANG KONSISTEN
include 'data_kos.php';

// Gunakan fungsi get_kos_by_id untuk mengambil data kos
$kos = get_kos_by_id($id_kos);

// Cek apakah kos ada
if ($kos === null) {
    header("Location: semua_kos.php");
    exit;
}

// Data user dari session - PERBAIKAN: TAMBAHKAN TANDA PANAH =>
$user_data = [
    'nama' => $_SESSION['user_name'] ?? 'Budi Santoso',
    'email' => $_SESSION['user_email'] ?? 'budi.santoso@example.com', // TAMBAH => DI SINI
    'no_handphone' => '081234567890'
];

// Handle form submission
$booking_success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $jenis_pembayaran = $_POST['jenis_pembayaran'] ?? '';
    $durasi_sewa = $_POST['durasi_sewa'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    // Validasi
    if (empty($jenis_pembayaran)) {
        $errors[] = "Pilih jenis pembayaran";
    }
    if (empty($durasi_sewa)) {
        $errors[] = "Pilih durasi sewa";
    }
    if (empty($tanggal_masuk)) {
        $errors[] = "Pilih tanggal masuk";
    }
    
    // Jika tidak ada error, proses booking
    if (empty($errors)) {
        // Simpan data booking ke session atau database
        $booking_data = [
            'id_kos' => $id_kos,
            'nama_kos' => $kos['nama'],
            'alamat_kos' => $kos['alamat'],
            'harga_kos' => $kos['harga'],
            'user_nama' => $user_data['nama'],
            'user_email' => $user_data['email'],
            'jenis_pembayaran' => $jenis_pembayaran,
            'durasi_sewa' => $durasi_sewa,
            'tanggal_masuk' => $tanggal_masuk,
            'catatan' => $catatan,
            'tanggal_booking' => date('Y-m-d H:i:s'),
            'kode_booking' => 'BOOK-' . strtoupper(substr(md5(time()), 0, 8)),
            'status' => 'pending' // pending, success, canceled
        ];
        
        // Simpan ke session (simulasi)
        $_SESSION['booking_data'] = $booking_data;
        $booking_success = true;
        
        // Reset form setelah sukses
        $_POST = [];
    }
}

// Hitung biaya berdasarkan durasi
function hitungBiaya($harga_tahunan, $durasi_sewa, $jenis_pembayaran) {
    $harga_per_bulan = $harga_tahunan / 12;
    
    switch($durasi_sewa) {
        case '6bulan':
            $total = $harga_per_bulan * 6;
            break;
        case '1tahun':
            $total = $harga_tahunan;
            break;
        case '2tahun':
            $total = $harga_tahunan * 2;
            // Diskon 5% untuk 2 tahun
            $total = $total * 0.95;
            break;
        default:
            $total = $harga_tahunan;
    }
    
    // Hitung DP jika pilih DP
    if ($jenis_pembayaran == 'dp') {
        $dp_amount = 1000000; // DP 1 juta
        $sisa = $total - $dp_amount;
        return [
            'dp' => $dp_amount,
            'sisa' => $sisa,
            'total' => $total
        ];
    } else {
        return [
            'dp' => 0,
            'sisa' => 0,
            'total' => $total
        ];
    }
}

// Hitung biaya berdasarkan input saat ini
$current_jenis_pembayaran = $_POST['jenis_pembayaran'] ?? 'lunas';
$current_durasi_sewa = $_POST['durasi_sewa'] ?? '1tahun';
$biaya = hitungBiaya($kos['harga'], $current_durasi_sewa, $current_jenis_pembayaran);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Kos - KosYuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0A2C4F;
            --secondary-color: #1A5F9E;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --light-blue: #e8f4ff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.5;
            font-size: 0.9rem;
        }
        
        .container-fluid {
            padding: 0;
        }
        
        /* Header - lebih kecil */
        .booking-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 12px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
        }
        
        .back-btn:hover {
            color: var(--warning-color);
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Main Content - lebih compact */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Card Styles - lebih kecil */
        .booking-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 18px;
            margin-bottom: 18px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .booking-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .card-title i {
            color: var(--secondary-color);
            background: var(--light-blue);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        /* Kos Info - lebih kecil */
        .kos-info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .kos-name {
            color: var(--primary-color);
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kos-name i {
            color: var(--secondary-color);
            font-size: 1rem;
        }
        
        .kos-price {
            color: var(--success-color);
            font-size: 0.95rem;
            font-weight: 700;
            background: rgba(40, 167, 69, 0.1);
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 8px;
        }
        
        /* Kos Details - lebih kecil */
        .kos-detail {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            color: #555;
            font-size: 0.85rem;
        }
        
        .kos-detail i {
            color: var(--secondary-color);
            width: 16px;
            margin-right: 8px;
            font-size: 0.8rem;
        }
        
        /* Payment Options - lebih kecil */
        .payment-option {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        
        .payment-option:hover {
            border-color: var(--secondary-color);
            background-color: #f8f9fa;
        }
        
        .payment-option.selected {
            border-color: var(--primary-color);
            background-color: var(--light-blue);
        }
        
        .payment-label {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .payment-label i {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .payment-badge {
            background-color: var(--success-color);
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .payment-description {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0;
            padding-left: 25px;
            line-height: 1.4;
        }
        
        /* Duration Options - lebih kecil */
        .duration-option {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            height: 100%;
            background: white;
        }
        
        .duration-option:hover {
            border-color: var(--secondary-color);
        }
        
        .duration-option.selected {
            border-color: var(--primary-color);
            background-color: var(--light-blue);
        }
        
        .duration-label {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .duration-price {
            color: var(--success-color);
            font-weight: 800;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .duration-discount {
            background-color: var(--warning-color);
            color: var(--primary-color);
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-top: 8px;
            display: inline-block;
        }
        
        /* Payment Methods - lebih kecil */
        .payment-method {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            position: relative;
        }
        
        .payment-method:hover {
            border-color: var(--secondary-color);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: var(--light-blue);
        }
        
        .bank-logo {
            width: 35px;
            height: 35px;
            object-fit: contain;
            margin-right: 12px;
            border-radius: 6px;
            background: white;
            padding: 4px;
            transition: transform 0.3s;
        }
        
        .payment-method:hover .bank-logo {
            transform: scale(1.1);
        }
        
        .bank-name {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        
        .bank-info {
            display: none;
            font-size: 0.8rem;
            color: #666;
            margin-top: 8px;
        }
        
        .bank-info.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Cost Summary - lebih kecil */
        .cost-summary {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 18px;
            margin-top: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .cost-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.85rem;
        }
        
        .cost-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--primary-color);
        }
        
        .cost-label {
            color: #555;
            font-weight: 600;
        }
        
        .cost-value {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .dp-section {
            background-color: rgba(255, 193, 7, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        /* Form Elements - lebih kecil */
        .form-control, .form-select {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(10, 44, 79, 0.1);
        }
        
        label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        
        /* Buttons - lebih kecil */
        .btn-booking {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s;
        }
        
        .btn-booking:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(10, 44, 79, 0.2);
        }
        
        /* Alert - lebih kecil */
        .alert-custom {
            border-radius: 8px;
            padding: 15px 20px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid;
            max-width: 1400px;
            margin: 0 auto 20px;
            font-size: 0.85rem;
        }
        
        /* Success Modal - lebih kecil */
        .success-content {
            background: white;
            border-radius: 10px;
            padding: 25px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        
        .success-icon {
            width: 70px;
            height: 70px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
        
        /* Info Box - lebih kecil */
        .info-box {
            background: linear-gradient(135deg, #e8f4ff 0%, #d1e7ff 100%);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #b8daff;
            font-size: 0.85rem;
        }
        
        .info-box i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Status Badge - lebih kecil */
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        /* Booking Summary Card - lebih kecil */
        .booking-summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 18px;
            border: 1px solid #e0e0e0;
        }
        
        .booking-success-card {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #28a745;
            margin-top: 20px;
        }
        
        /* Grid Layout - 2 KOLOM dengan jarak lebih kecil */
        .booking-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }
        
        @media (min-width: 992px) {
            .booking-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        /* Column Layout */
        .column-left {
            grid-column: 1;
        }
        
        .column-right {
            grid-column: 2;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 991px) {
            .column-left,
            .column-right,
            .full-width {
                grid-column: 1;
            }
        }
        
        /* Section Spacing - lebih kecil */
        .section-spacing {
            margin: 20px 0;
        }
        
        /* Catatan Section */
        .catatan-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .catatan-section label {
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        
        .catatan-section textarea {
            font-size: 0.85rem;
            min-height: 80px;
        }
        
        /* Upload Section Styles */
        .upload-section {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .upload-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .upload-header i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .upload-header h6 {
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .upload-form {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px dashed #ddd;
        }
        
        .upload-preview {
            margin-top: 10px;
            text-align: center;
        }
        
        .upload-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        /* Small text for additional info */
        .text-muted.small {
            font-size: 0.75rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 10px;
            }
            
            .booking-card {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .card-title {
                font-size: 1rem;
            }
            
            .btn-booking {
                padding: 12px 18px;
                font-size: 0.9rem;
            }
            
            .form-control, .form-select {
                padding: 8px 10px;
            }
            
            .duration-option {
                padding: 12px;
            }
            
            .payment-option {
                padding: 12px;
            }
        }
        
        /* Text Colors */
        .text-primary-custom {
            color: var(--primary-color) !important;
            font-size: 0.85rem;
        }
        
        .text-success-custom {
            color: var(--success-color) !important;
        }
        
        /* Remove footer */
        footer {
            display: none;
        }
        
        /* Success Modal adjustments */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .success-modal.active {
            display: flex;
        }
        
        /* Payment details */
        .bank-details {
            display: none;
            margin-top: 8px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        
        .bank-details.show {
            display: block;
        }
        
        .bank-details p {
            margin: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="booking-header">
        <div class="container-fluid">
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="detail_kos.php?id=<?php echo $id_kos; ?>" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Kembali ke Detail Kos
                    </a>
                    <div class="text-center">
                        <h1 class="mb-1" style="font-size: 1.5rem;">Booking Kos</h1>
                        <p class="mb-0 opacity-75" style="font-size: 0.85rem;">Lengkapi data untuk melanjutkan booking</p>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <div>
                                <div class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user_data['nama']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="main-content">
            <!-- Alert Errors -->
            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Perhatian!</h6>
                        <ul class="mb-0" style="font-size: 0.8rem;">
                            <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Success Alert -->
            <?php if($booking_success): ?>
            <div class="alert alert-success alert-custom" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-lg me-2"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Booking Berhasil!</h6>
                        <p class="mb-0" style="font-size: 0.85rem;">Data booking telah disimpan. Lihat detail di bawah.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="bookingForm">
                <div class="booking-grid">
                    <!-- Kolom Kiri: 4 kontainer -->
                    <div class="column-left">
                        <!-- Card 1: Data Kos -->
                        <div class="booking-card">
                            <h3 class="card-title">
                                <i class="fas fa-home"></i> Data Kos
                            </h3>
                            
                            <div class="kos-info-box">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="kos-name mb-2">
                                            <i class="fas fa-building"></i>
                                            Kos <?php echo htmlspecialchars($kos['nama']); ?>
                                        </h4>
                                        
                                        <div class="kos-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($kos['alamat']); ?></span>
                                        </div>
                                        
                                        <div class="kos-detail">
                                            <i class="fas fa-<?php echo $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female'; ?>"></i>
                                            <span>Kos <?php echo ucfirst($kos['jenis_kelamin']); ?></span>
                                        </div>
                                        
                                        <div class="kos-detail">
                                            <i class="fas fa-user-tie"></i>
                                            <span>Pemilik: <?php echo htmlspecialchars($kos['pemilik_nama']); ?></span>
                                        </div>
                                        
                                        <div class="kos-detail">
                                            <i class="fas fa-door-open"></i>
                                            <span>Sisa Kamar: <?php echo $kos['sisa_kamar']; ?> kamar tersedia</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-md-end">
                                            <div class="mb-2">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Harga per Tahun</small>
                                                <div class="kos-price" style="font-size: 0.9rem;">
                                                    Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Harga Pilihan Anda</small>
                                                <div class="kos-price" id="kos-price-adjusted" style="font-size: 0.9rem;">
                                                    Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Durasi Sewa -->
                        <div class="booking-card">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt"></i> Durasi Sewa
                            </h3>
                            
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="duration-option <?php echo ($current_durasi_sewa == '6bulan') ? 'selected' : ''; ?>" 
                                         onclick="selectDuration('6bulan')">
                                        <div class="duration-label">
                                            <i class="fas fa-clock me-1"></i>6 Bulan
                                        </div>
                                        <p class="duration-price" id="price-6bulan" style="font-size: 0.9rem;">
                                            Rp <?php echo number_format($kos['harga'] / 2, 0, ',', '.'); ?>
                                        </p>
                                        <p class="text-muted small" style="font-size: 0.75rem;">(Rp <?php echo number_format($kos['harga'] / 6, 0, ',', '.'); ?>/bulan)</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="duration-option <?php echo ($current_durasi_sewa == '1tahun') ? 'selected' : ''; ?>" 
                                         onclick="selectDuration('1tahun')">
                                        <div class="duration-label">
                                            <i class="fas fa-calendar me-1"></i>1 Tahun
                                        </div>
                                        <p class="duration-price" id="price-1tahun" style="font-size: 0.9rem;">
                                            Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="text-muted small" style="font-size: 0.75rem;">(Rp <?php echo number_format($kos['harga'] / 12, 0, ',', '.'); ?>/bulan)</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="duration-option <?php echo ($current_durasi_sewa == '2tahun') ? 'selected' : ''; ?>" 
                                         onclick="selectDuration('2tahun')">
                                        <div class="duration-label">
                                            <i class="fas fa-calendar-check me-1"></i>2 Tahun
                                        </div>
                                        <p class="duration-price" id="price-2tahun" style="font-size: 0.9rem;">
                                            Rp <?php echo number_format($kos['harga'] * 2 * 0.95, 0, ',', '.'); ?>
                                        </p>
                                        <div class="duration-discount">
                                            <i class="fas fa-tag me-1"></i> Hemat 5%
                                        </div>
                                        <p class="text-muted small mt-1" style="font-size: 0.75rem;">(Rp <?php echo number_format(($kos['harga'] * 2 * 0.95) / 24, 0, ',', '.'); ?>/bulan)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Input hidden untuk durasi sewa -->
                            <input type="hidden" name="durasi_sewa" id="durasi_sewa" value="<?php echo $current_durasi_sewa; ?>">
                        </div>

                        <!-- Card 3: Jenis Pembayaran -->
                        <div class="booking-card">
                            <h3 class="card-title">
                                <i class="fas fa-credit-card"></i> Jenis Pembayaran
                            </h3>
                            
                            <div class="mb-3">
                                <!-- Option: Lunas -->
                                <div class="payment-option <?php echo ($current_jenis_pembayaran == 'lunas') ? 'selected' : ''; ?>" 
                                     onclick="selectPaymentOption('lunas')">
                                    <div class="payment-option-header">
                                        <div class="payment-label">
                                            <i class="fas fa-money-bill-wave me-1"></i>Bayar Lunas
                                        </div>
                                        <span class="badge bg-success" style="font-size: 0.7rem;">Rekomendasi</span>
                                    </div>
                                    <p class="payment-description">
                                        Bayar penuh saat booking untuk proses yang lebih cepat dan aman.
                                    </p>
                                </div>
                                
                                <!-- Option: DP -->
                                <div class="payment-option <?php echo ($current_jenis_pembayaran == 'dp') ? 'selected' : ''; ?>" 
                                     onclick="selectPaymentOption('dp')">
                                    <div class="payment-option-header">
                                        <div class="payment-label">
                                            <i class="fas fa-hand-holding-usd me-1"></i>Down Payment (DP)
                                        </div>
                                        <span class="payment-badge">DP Rp 1.000.000</span>
                                    </div>
                                    <p class="payment-description">
                                        Bayar DP 1 juta untuk memesan kos. Sisa pembayaran lunas saat check-in.
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Input hidden untuk jenis pembayaran -->
                            <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="<?php echo $current_jenis_pembayaran; ?>">
                        </div>

                        <!-- Card 4: Metode Pembayaran -->
                        <div class="booking-card">
                            <h3 class="card-title">
                                <i class="fas fa-university"></i> Metode Pembayaran
                            </h3>
                            
                            <p class="text-muted mb-2" style="font-size: 0.85rem;">Klik logo bank untuk melihat nomor rekening:</p>
                            
                            <div class="mb-3">
                                <!-- BRI -->
                                <div class="payment-method" onclick="toggleBankDetail('bri')" id="bank-bri">
                                    <div class="d-flex align-items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/1200px-BANK_BRI_logo.svg.png" 
                                             class="bank-logo" alt="BRI">
                                        <div class="flex-grow-1">
                                            <div class="bank-name">Bank BRI</div>
                                            <div class="bank-details" id="bri-details">
                                                <p class="mb-1"><strong>No. Rekening:</strong> 1234-5678-9012-3456</p>
                                                <p class="mb-0"><strong>Atas Nama:</strong> PT KosYuk Indonesia</p>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-chevron-down" id="bri-chevron"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- BNI -->
                                <div class="payment-method" onclick="toggleBankDetail('bni')" id="bank-bni">
                                    <div class="d-flex align-items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Negara_Indonesia_%28BNI%29_logo.svg/1200px-Bank_Negara_Indonesia_%28BNI%29_logo.svg.png" 
                                             class="bank-logo" alt="BNI">
                                        <div class="flex-grow-1">
                                            <div class="bank-name">Bank BNI</div>
                                            <div class="bank-details" id="bni-details">
                                                <p class="mb-1"><strong>No. Rekening:</strong> 0987-6543-2109-8765</p>
                                                <p class="mb-0"><strong>Atas Nama:</strong> PT KosYuk Indonesia</p>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-chevron-down" id="bni-chevron"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Mandiri -->
                                <div class="payment-method" onclick="toggleBankDetail('mandiri')" id="bank-mandiri">
                                    <div class="d-flex align-items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/1200px-Bank_Mandiri_logo_2016.svg.png" 
                                             class="bank-logo" alt="Mandiri">
                                        <div class="flex-grow-1">
                                            <div class="bank-name">Bank Mandiri</div>
                                            <div class="bank-details" id="mandiri-details">
                                                <p class="mb-1"><strong>No. Rekening:</strong> 5678-9012-3456-7890</p>
                                                <p class="mb-0"><strong>Atas Nama:</strong> PT KosYuk Indonesia</p>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-chevron-down" id="mandiri-chevron"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Informasi Penting:</h6>
                                <ul class="mb-0" style="font-size: 0.8rem;">
                                    <li>Pastikan nominal transfer sesuai dengan total yang harus dibayar</li>
                                    <li>Upload bukti pembayaran setelah melakukan transfer</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: 2 kontainer (Ringkasan Pembayaran + Ringkasan Booking) -->
                    <div class="column-right">
                        <!-- Card 5: Ringkasan Pembayaran -->
                        <div class="booking-card" id="totalBayarCard">
                            <h3 class="card-title">
                                <i class="fas fa-receipt"></i> Ringkasan Pembayaran
                            </h3>
                            
                            <div class="cost-summary">
                                <div class="cost-item">
                                    <span class="cost-label">
                                        <i class="fas fa-home me-1"></i>
                                        Harga Sewa 
                                        <span id="durasi-label" class="fw-bold">
                                            <?php 
                                            if($current_durasi_sewa == '6bulan') echo '6 Bulan';
                                            elseif($current_durasi_sewa == '1tahun') echo '1 Tahun';
                                            else echo '2 Tahun';
                                            ?>
                                        </span>
                                    </span>
                                    <span class="cost-value" id="harga_durasi">Rp <?php echo number_format($biaya['total'], 0, ',', '.'); ?></span>
                                </div>
                                
                                <?php if($current_durasi_sewa == '2tahun'): ?>
                                <div class="cost-item diskon-item">
                                    <span class="cost-label">
                                        <i class="fas fa-tag me-1 text-success"></i>Diskon 2 Tahun
                                    </span>
                                    <span class="cost-value text-success">-Rp <?php echo number_format($kos['harga'] * 2 * 0.05, 0, ',', '.'); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($current_jenis_pembayaran == 'dp'): ?>
                                <div class="dp-section">
                                    <div class="cost-item">
                                        <span class="cost-label">
                                            <i class="fas fa-hand-holding-usd me-1"></i>Down Payment (DP)
                                        </span>
                                        <span class="cost-value">Rp 1.000.000</span>
                                    </div>
                                    <div class="cost-item">
                                        <span class="cost-label">
                                            <i class="fas fa-calendar-check me-1"></i>Sisa Pembayaran
                                        </span>
                                        <span class="cost-value" id="sisa_pembayaran">Rp <?php echo number_format($biaya['sisa'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Sisa pembayaran dibayar saat check-in di kos
                                        </small>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="cost-total">
                                    <span>
                                        <i class="fas fa-calculator me-1"></i>
                                        Total <?php echo ($current_jenis_pembayaran == 'dp') ? 'DP' : 'Bayar'; ?>
                                    </span>
                                    <span id="total_bayar" style="font-size: 1rem;">
                                        <?php 
                                        if($current_jenis_pembayaran == 'dp') {
                                            echo 'Rp 1.000.000';
                                        } else {
                                            echo 'Rp ' . number_format($biaya['total'], 0, ',', '.');
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 6: Ringkasan Booking dengan Upload Bukti -->
                        <div class="booking-summary-card">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list"></i> Ringkasan Booking
                            </h3>
                            
                            <!-- Detail Kos -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary-custom mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-home me-1"></i>Detail Kos
                                </h6>
                                <p class="mb-1"><strong>Kos <?php echo htmlspecialchars($kos['nama']); ?></strong></p>
                                <p class="text-muted mb-2" style="font-size: 0.8rem;"><?php echo htmlspecialchars($kos['alamat']); ?></p>
                                <p class="small mb-1">
                                    <i class="fas fa-<?php echo $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female'; ?> text-primary me-1"></i>
                                    Kos <?php echo ucfirst($kos['jenis_kelamin']); ?>
                                </p>
                                <p class="small mb-1">
                                    <i class="fas fa-user-tie text-primary me-1"></i>
                                    Pemilik: <?php echo htmlspecialchars($kos['pemilik_nama']); ?>
                                </p>
                                <p class="small mb-1">
                                    <i class="fas fa-door-open text-primary me-1"></i>
                                    Sisa Kamar: <?php echo $kos['sisa_kamar']; ?> kamar
                                </p>
                            </div>
                            
                            <!-- Data Pemesan -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary-custom mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-user me-1"></i>Data Pemesan
                                </h6>
                                <div class="row mb-2">
                                    <div class="col-12 mb-1">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Nama</small>
                                        <span class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user_data['nama']); ?></span>
                                    </div>
                                    <div class="col-12 mb-1">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Email</small>
                                        <span class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user_data['email']); ?></span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">No. Handphone</small>
                                        <span class="fw-bold" style="font-size: 0.85rem;"><?php echo htmlspecialchars($user_data['no_handphone']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tanggal Masuk -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary-custom mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-calendar-day me-1"></i>Tanggal Masuk
                                </h6>
                                <label for="tanggal_masuk" class="form-label small mb-1" style="font-size: 0.75rem;">Pilih tanggal check-in *</label>
                                <input type="date" class="form-control form-control-sm" name="tanggal_masuk" id="tanggal_masuk" 
                                       value="<?php echo $_POST['tanggal_masuk'] ?? date('Y-m-d', strtotime('+3 days')); ?>" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       required style="font-size: 0.8rem;">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <i class="fas fa-info-circle me-1"></i>Minimal H-1 dari hari ini
                                </small>
                            </div>
                            
                            <!-- Catatan -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary-custom mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-sticky-note me-1"></i>Catatan Tambahan (Opsional)
                                </h6>
                                <textarea class="form-control" name="catatan" id="catatan" rows="3" 
                                          placeholder="Contoh: Saya ingin kamar di lantai 1, dekat kamar mandi, atau permintaan khusus lainnya..."
                                          style="font-size: 0.8rem;"><?php echo $_POST['catatan'] ?? ''; ?></textarea>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    Catatan akan disampaikan kepada pemilik kos
                                </small>
                            </div>
                            
                            <!-- Ringkasan Pembayaran -->
                            <div class="mt-4">
                                <h6 class="fw-bold text-primary-custom mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-credit-card me-1"></i>Ringkasan Pembayaran
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Durasi</small>
                                        <span class="fw-bold" id="summary-durasi" style="font-size: 0.85rem;">
                                            <?php 
                                            if($current_durasi_sewa == '6bulan') echo '6 Bulan';
                                            elseif($current_durasi_sewa == '1tahun') echo '1 Tahun';
                                            else echo '2 Tahun';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Jenis</small>
                                        <span class="fw-bold" id="summary-jenis" style="font-size: 0.85rem;">
                                            <?php echo ($current_jenis_pembayaran == 'dp') ? 'DP' : 'Lunas'; ?>
                                        </span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Total <?php echo ($current_jenis_pembayaran == 'dp') ? 'DP' : 'Bayar'; ?></small>
                                        <span class="fw-bold text-success-custom" id="summary-total" style="font-size: 1rem;">
                                            <?php 
                                            if($current_jenis_pembayaran == 'dp') {
                                                echo 'Rp 1.000.000';
                                            } else {
                                                echo 'Rp ' . number_format($biaya['total'], 0, ',', '.');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Bukti Pembayaran -->
                            <div class="upload-section">
                                <div class="upload-header">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <h6>Upload Bukti Pembayaran</h6>
                                </div>
                                
                                <div class="upload-form">
                                    <div class="mb-3">
                                        <label for="bukti_pembayaran" class="form-label small mb-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-paperclip me-1"></i>Upload File Bukti Transfer
                                        </label>
                                        <input type="file" class="form-control form-control-sm" id="bukti_pembayaran" 
                                               name="bukti_pembayaran" accept="image/*,.pdf,.doc,.docx" style="font-size: 0.8rem;">
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            Format: JPG, PNG, PDF, DOC (maks. 5MB)
                                        </small>
                                    </div>
                                    
                                    <div class="upload-preview" id="uploadPreview" style="display: none;">
                                        <p class="small mb-2">Pratinjau:</p>
                                        <img id="previewImage" src="" alt="Preview Bukti Pembayaran">
                                    </div>
                                    
                                    <div class="info-box mt-2">
                                        <i class="fas fa-info-circle"></i>
                                        <p class="mb-0" style="font-size: 0.75rem;">
                                            <strong>Catatan:</strong> Upload bukti transfer setelah pembayaran.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Width Section: Tombol Submit saja -->
                    <div class="full-width">
                        <!-- Button Submit -->
                        <div class="section-spacing">
                            <?php if(!$booking_success): ?>
                            <button type="submit" class="btn btn-booking hover-lift">
                                <i class="fas fa-paper-plane me-2"></i> Proses Booking Sekarang
                            </button>
                            <p class="text-center text-muted mt-2 small" style="font-size: 0.8rem;">
                                <i class="fas fa-lock me-1"></i>
                                Data Anda aman dan terlindungi
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Card: Booking Sukses (Full Width) -->
            <?php if($booking_success && isset($_SESSION['booking_data'])): 
                $booking = $_SESSION['booking_data']; ?>
            <div class="booking-success-card full-width">
                <h3 class="card-title">
                    <i class="fas fa-check-circle text-success"></i> Booking Sukses!
                </h3>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="status-badge status-pending mb-2" style="background: #fff3cd; color: #856404;">
                                <i class="fas fa-clock me-1"></i>Menunggu Pembayaran
                            </div>
                            <h4 class="text-primary" style="font-size: 1.2rem;"><?php echo $booking['kode_booking']; ?></h4>
                            <p class="text-muted small" style="font-size: 0.8rem;">Simpan kode booking ini</p>
                        </div>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal Booking</small>
                                <span class="fw-bold" style="font-size: 0.85rem;"><?php echo date('d M Y H:i', strtotime($booking['tanggal_booking'])); ?></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal Masuk</small>
                                <span class="fw-bold" style="font-size: 0.85rem;"><?php echo date('d M Y', strtotime($booking['tanggal_masuk'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Kos yang Dipesan</small>
                                <span class="fw-bold" style="font-size: 0.85rem;">Kos <?php echo htmlspecialchars($booking['nama_kos']); ?></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Total <?php echo ($booking['jenis_pembayaran'] == 'dp') ? 'DP' : 'Bayar'; ?></small>
                                <span class="fw-bold text-success-custom" style="font-size: 0.85rem;">
                                    <?php 
                                    if($booking['jenis_pembayaran'] == 'dp') {
                                        echo 'Rp 1.000.000';
                                    } else {
                                        echo 'Rp ' . number_format($booking['harga_kos'], 0, ',', '.');
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Langkah selanjutnya:</small>
                            <ol class="small ps-3" style="font-size: 0.8rem;">
                                <li>Lakukan pembayaran sesuai instruksi di atas</li>
                                <li>Upload bukti pembayaran di form di atas</li>
                                <li>Tunggu konfirmasi dari admin (1-2 hari kerja)</li>
                                <li>Check-in sesuai tanggal yang dipilih</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary hover-lift" onclick="printBooking()" style="padding: 10px; font-size: 0.85rem;">
                                <i class="fas fa-print me-1"></i> Cetak Invoice
                            </button>
                            <a href="profil_pencarikos.php?tab=booking-saya" class="btn btn-success hover-lift" style="padding: 10px; font-size: 0.85rem;">
                                <i class="fas fa-list me-1"></i> Lihat Booking Saya
                            </a>
                            <a href="semua_kos.php" class="btn btn-outline-primary hover-lift" style="padding: 10px; font-size: 0.85rem;">
                                <i class="fas fa-search me-1"></i> Cari Kos Lain
                            </a>
                            <a href="dashboard_pencarikos.php" class="btn btn-outline-secondary hover-lift" style="padding: 10px; font-size: 0.85rem;">
                                <i class="fas fa-home me-1"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Modal (akan muncul setelah submit) -->
    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="mb-2" style="font-size: 1.3rem;">Booking Berhasil!</h3>
            <p class="text-muted mb-3" style="font-size: 0.9rem;">
                Booking Anda telah berhasil diproses.
            </p>
            <div class="d-grid gap-2">
                <button class="btn btn-primary hover-lift" onclick="window.location.href='profil_pencarikos.php?tab=booking-saya'" style="padding: 10px; font-size: 0.9rem;">
                    <i class="fas fa-eye me-1"></i> Lihat Detail Booking
                </button>
                <button class="btn btn-outline-secondary hover-lift" onclick="closeSuccessModal()" style="padding: 10px; font-size: 0.9rem;">
                    <i class="fas fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Harga tahunan dari PHP
        const hargaTahunan = <?php echo $kos['harga']; ?>;
        
        // Pilih Jenis Pembayaran
        function selectPaymentOption(option) {
            // Update UI
            document.querySelectorAll('.payment-option').forEach(function(el) {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Update hidden input
            document.getElementById('jenis_pembayaran').value = option;
            
            // Update ringkasan
            document.getElementById('summary-jenis').textContent = option === 'dp' ? 'DP' : 'Lunas';
            
            // Update ringkasan biaya
            updateCostSummary();
        }
        
        // Pilih Durasi Sewa
        function selectDuration(duration) {
            // Update UI
            document.querySelectorAll('.duration-option').forEach(function(el) {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Update hidden input
            document.getElementById('durasi_sewa').value = duration;
            
            // Update harga kos yang disesuaikan
            updateKosPriceAdjusted(duration);
            
            // Update ringkasan durasi
            let durasiLabel = '';
            switch(duration) {
                case '6bulan': durasiLabel = '6 Bulan'; break;
                case '1tahun': durasiLabel = '1 Tahun'; break;
                case '2tahun': durasiLabel = '2 Tahun'; break;
            }
            document.getElementById('summary-durasi').textContent = durasiLabel;
            document.getElementById('durasi-label').textContent = durasiLabel;
            
            // Update ringkasan biaya
            updateCostSummary();
        }
        
        // Update harga kos yang disesuaikan
        function updateKosPriceAdjusted(duration) {
            let harga;
            let label = '';
            
            switch(duration) {
                case '6bulan':
                    harga = hargaTahunan / 2;
                    label = '6 Bulan';
                    break;
                case '1tahun':
                    harga = hargaTahunan;
                    label = '1 Tahun';
                    break;
                case '2tahun':
                    harga = hargaTahunan * 2 * 0.95;
                    label = '2 Tahun';
                    break;
                default:
                    harga = hargaTahunan;
                    label = '1 Tahun';
            }
            
            // Update harga yang disesuaikan
            document.getElementById('kos-price-adjusted').innerHTML = 
                'Rp ' + Math.round(harga).toLocaleString('id-ID');
        }
        
        // Toggle Bank Detail
        function toggleBankDetail(bank) {
            const bankDetails = document.getElementById(bank + '-details');
            const chevron = document.getElementById(bank + '-chevron');
            
            // Toggle show class
            bankDetails.classList.toggle('show');
            
            // Rotate chevron
            if (bankDetails.classList.contains('show')) {
                chevron.style.transform = 'rotate(180deg)';
            } else {
                chevron.style.transform = 'rotate(0deg)';
            }
        }
        
        // Update Ringkasan Biaya
        function updateCostSummary() {
            const jenisPembayaran = document.getElementById('jenis_pembayaran').value;
            const durasiSewa = document.getElementById('durasi_sewa').value;
            
            // Hitung berdasarkan durasi
            let harga;
            let diskon = 0;
            
            switch(durasiSewa) {
                case '6bulan':
                    harga = hargaTahunan / 2;
                    break;
                case '1tahun':
                    harga = hargaTahunan;
                    break;
                case '2tahun':
                    harga = hargaTahunan * 2;
                    diskon = hargaTahunan * 2 * 0.05; // Diskon 5%
                    harga = harga * 0.95;
                    break;
                default:
                    harga = hargaTahunan;
            }
            
            // Format angka ke Rupiah
            function formatRupiah(angka) {
                return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
            }
            
            // Update tampilan
            document.getElementById('harga_durasi').textContent = formatRupiah(harga);
            
            if (jenisPembayaran === 'dp') {
                const dpAmount = 1000000;
                const sisa = harga - dpAmount;
                
                // Tampilkan atau perbarui DP section
                let dpSection = document.querySelector('.dp-section');
                if (!dpSection) {
                    const costSummary = document.querySelector('.cost-summary');
                    const totalSection = document.querySelector('.cost-total');
                    
                    const newSection = document.createElement('div');
                    newSection.className = 'dp-section';
                    newSection.innerHTML = `
                        <div class="cost-item">
                            <span class="cost-label">
                                <i class="fas fa-hand-holding-usd me-1"></i>Down Payment (DP)
                            </span>
                            <span class="cost-value">Rp 1.000.000</span>
                        </div>
                        <div class="cost-item">
                            <span class="cost-label">
                                <i class="fas fa-calendar-check me-1"></i>Sisa Pembayaran
                            </span>
                            <span class="cost-value" id="sisa_pembayaran">${formatRupiah(sisa)}</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                Sisa pembayaran dibayar saat check-in di kos
                            </small>
                        </div>
                    `;
                    
                    costSummary.insertBefore(newSection, totalSection);
                } else {
                    // Update sisa pembayaran
                    dpSection.querySelector('#sisa_pembayaran').textContent = formatRupiah(sisa);
                }
                
                document.getElementById('total_bayar').textContent = 'Rp 1.000.000';
                document.querySelector('.cost-total span:first-child').innerHTML = 
                    '<i class="fas fa-calculator me-1"></i>Total DP';
                document.getElementById('summary-total').textContent = 'Rp 1.000.000';
            } else {
                // Hapus DP section jika ada
                const dpSection = document.querySelector('.dp-section');
                if (dpSection) {
                    dpSection.remove();
                }
                document.getElementById('total_bayar').textContent = formatRupiah(harga);
                document.querySelector('.cost-total span:first-child').innerHTML = 
                    '<i class="fas fa-calculator me-1"></i>Total Bayar';
                document.getElementById('summary-total').textContent = formatRupiah(harga);
            }
            
            // Update diskon jika ada
            if (durasiSewa === '2tahun') {
                // Tampilkan item diskon jika belum ada
                let diskonItem = document.querySelector('.diskon-item');
                if (!diskonItem) {
                    const costSummary = document.querySelector('.cost-summary');
                    const firstItem = costSummary.querySelector('.cost-item');
                    
                    const newItem = document.createElement('div');
                    newItem.className = 'cost-item diskon-item';
                    newItem.innerHTML = `
                        <span class="cost-label">
                            <i class="fas fa-tag me-1 text-success"></i>Diskon 2 Tahun
                        </span>
                        <span class="cost-value text-success">-${formatRupiah(diskon)}</span>
                    `;
                    
                    costSummary.insertBefore(newItem, firstItem.nextSibling);
                } else {
                    diskonItem.querySelector('.cost-value').textContent = '-' + formatRupiah(diskon);
                }
            } else {
                // Hapus item diskon jika ada
                const diskonItem = document.querySelector('.diskon-item');
                if (diskonItem) {
                    diskonItem.remove();
                }
            }
        }
        
        // Tanggal minimal H+1
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            
            const minDate = tomorrow.toISOString().split('T')[0];
            const tanggalMasuk = document.getElementById('tanggal_masuk');
            tanggalMasuk.min = minDate;
            
            // Set default tanggal H+3 jika belum diisi
            if (!tanggalMasuk.value) {
                const defaultDate = new Date(today);
                defaultDate.setDate(today.getDate() + 3);
                tanggalMasuk.value = defaultDate.toISOString().split('T')[0];
            }
            
            // Update harga yang disesuaikan dan ringkasan biaya saat halaman dimuat
            const currentDuration = document.getElementById('durasi_sewa').value;
            updateKosPriceAdjusted(currentDuration);
            updateCostSummary();
            
            // Preview upload file
            const buktiInput = document.getElementById('bukti_pembayaran');
            if (buktiInput) {
                buktiInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    const preview = document.getElementById('uploadPreview');
                    const previewImage = document.getElementById('previewImage');
                    
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            preview.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(file);
                    } else {
                        preview.style.display = 'none';
                    }
                });
            }
        });
        
        // Print booking
        function printBooking() {
            const bookingData = <?php echo json_encode($_SESSION['booking_data'] ?? []); ?>;
            if (Object.keys(bookingData).length > 0) {
                const printWindow = window.open('', '_blank');
                const formattedDate = new Date(bookingData.tanggal_booking).toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Invoice Booking - ${bookingData.kode_booking}</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                            body { 
                                font-family: 'Inter', sans-serif; 
                                padding: 20px; 
                                background: #f8f9fa;
                                color: #333;
                                font-size: 14px;
                            }
                            .invoice { 
                                max-width: 800px; 
                                margin: 0 auto; 
                                background: white;
                                border-radius: 10px;
                                padding: 25px;
                                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                            }
                            .header { 
                                text-align: center; 
                                margin-bottom: 25px;
                                padding-bottom: 15px;
                                border-bottom: 2px solid #0A2C4F;
                            }
                            .header h1 { 
                                color: #0A2C4F;
                                font-size: 22px;
                                margin-bottom: 8px;
                            }
                            .header h2 { 
                                color: #1A5F9E;
                                font-size: 16px;
                                font-weight: 600;
                            }
                            .info { 
                                margin-bottom: 20px; 
                                padding: 15px;
                                background: #f8f9fa;
                                border-radius: 6px;
                            }
                            table { 
                                width: 100%; 
                                border-collapse: collapse; 
                                margin: 20px 0;
                                border-radius: 6px;
                                overflow: hidden;
                                box-shadow: 0 1px 5px rgba(0,0,0,0.1);
                                font-size: 13px;
                            }
                            th, td { 
                                border: 1px solid #ddd; 
                                padding: 10px; 
                                text-align: left; 
                            }
                            th { 
                                background-color: #0A2C4F; 
                                color: white;
                                font-weight: 600;
                                font-size: 13px;
                            }
                            .total { 
                                font-size: 1.1em; 
                                font-weight: bold; 
                                background: #f8f9fa;
                            }
                            .footer { 
                                margin-top: 25px; 
                                text-align: center; 
                                color: #666;
                                font-size: 12px;
                                padding-top: 15px;
                                border-top: 1px solid #eee;
                            }
                            .status-badge {
                                display: inline-block;
                                padding: 5px 10px;
                                border-radius: 15px;
                                font-weight: 600;
                                margin-bottom: 15px;
                                background: #fff3cd;
                                color: #856404;
                                font-size: 12px;
                            }
                            .section-title {
                                font-size: 15px;
                                color: #0A2C4F;
                                margin: 15px 0 10px;
                                padding-bottom: 8px;
                                border-bottom: 1px solid #e9ecef;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="invoice">
                            <div class="header">
                                <h1>Invoice Booking Kos</h1>
                                <h2>Kode Booking: ${bookingData.kode_booking}</h2>
                                <div class="status-badge">Status: ${bookingData.status.toUpperCase()}</div>
                            </div>
                            
                            <div class="info">
                                <p><strong>Tanggal & Waktu:</strong> ${formattedDate}</p>
                                <p><strong>Nomor Invoice:</strong> INV-${bookingData.kode_booking}</p>
                            </div>
                            
                            <div class="info">
                                <h3 class="section-title">Data Pemesan</h3>
                                <p><strong>Nama:</strong> ${bookingData.user_nama}</p>
                                <p><strong>Email:</strong> ${bookingData.user_email}</p>
                                <p><strong>Tanggal Masuk:</strong> ${new Date(bookingData.tanggal_masuk).toLocaleDateString('id-ID', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                })}</p>
                            </div>
                            
                            <div class="info">
                                <h3 class="section-title">Detail Kos</h3>
                                <p><strong>Nama Kos:</strong> Kos ${bookingData.nama_kos}</p>
                                <p><strong>Alamat:</strong> ${bookingData.alamat_kos}</p>
                                <p><strong>Jenis Kos:</strong> ${bookingData.jenis_kelamin ? 'Kos ' + bookingData.jenis_kelamin.charAt(0).toUpperCase() + bookingData.jenis_kelamin.slice(1) : ''}</p>
                                <p><strong>Harga per Tahun:</strong> Rp ${Number(bookingData.harga_kos).toLocaleString('id-ID')}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Detail</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Durasi Sewa</td>
                                        <td>${bookingData.durasi_sewa === '6bulan' ? '6 Bulan' : bookingData.durasi_sewa === '1tahun' ? '1 Tahun' : '2 Tahun'}</td>
                                        <td>Rp ${Number(<?php echo $biaya['total']; ?>).toLocaleString('id-ID')}</td>
                                    </tr>
                                    ${bookingData.jenis_pembayaran === 'dp' ? `
                                    <tr>
                                        <td>Down Payment</td>
                                        <td>DP Awal</td>
                                        <td>Rp 1.000.000</td>
                                    </tr>
                                    <tr>
                                        <td>Sisa Pembayaran</td>
                                        <td>Dibayar saat check-in</td>
                                        <td>Rp ${Number(<?php echo $biaya['sisa']; ?>).toLocaleString('id-ID')}</td>
                                    </tr>
                                    ` : ''}
                                    ${bookingData.durasi_sewa === '2tahun' ? `
                                    <tr>
                                        <td>Diskon</td>
                                        <td>Diskon 2 Tahun (5%)</td>
                                        <td>-Rp ${Number(<?php echo $kos['harga'] * 2 * 0.05; ?>).toLocaleString('id-ID')}</td>
                                    </tr>
                                    ` : ''}
                                </tbody>
                                <tfoot>
                                    <tr class="total">
                                        <td colspan="2">Total ${bookingData.jenis_pembayaran === 'dp' ? 'DP' : 'Bayar'}</td>
                                        <td>Rp ${bookingData.jenis_pembayaran === 'dp' ? '1.000.000' : Number(<?php echo $biaya['total']; ?>).toLocaleString('id-ID')}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            ${bookingData.catatan ? `
                            <div class="info">
                                <h3 class="section-title">Catatan</h3>
                                <p>${bookingData.catatan}</p>
                            </div>
                            ` : ''}
                            
                            <div class="footer">
                                <p><strong>Terima kasih telah menggunakan KosYuk!</strong></p>
                                <p>Invoice ini dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                                <p style="margin-top: 15px; color: #666; font-size: 11px;">
                                    * Simpan invoice ini sebagai bukti transaksi<br>
                                    * Hubungi customer service jika ada pertanyaan
                                </p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            }
        }
        
        // Close success modal
        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('active');
        }
        
        // Show success modal after booking
        <?php if($booking_success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('successModal').classList.add('active');
            }, 500);
        });
        <?php endif; ?>
        
        // Form submission dengan loading
        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Tampilkan loading
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            submitBtn.disabled = true;
            
            // Simulasi loading (dalam implementasi nyata, ini tidak perlu)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    </script>
</body>
</html>