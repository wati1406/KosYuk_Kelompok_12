<?php
session_start();

// Cek login dan role pemilik kos
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'pemilik_kos') {
    header("Location: login.php");
    exit;
}

// Debug: Tampilkan semua session untuk troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan folder uploads ada
$upload_dir = 'uploads/kos_photos/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ambil parameter dari URL
$kos_id = $_GET['id'] ?? '';
$kos_index = isset($_GET['index']) ? intval($_GET['index']) : -1;

echo "<!-- DEBUG: kos_id = $kos_id, kos_index = $kos_index -->\n";

// Cari data kos berdasarkan ID atau index
$kos_data = null;
$kos_source = null;
$kos_actual_index = null;

// Debug: Tampilkan semua kos yang ada
echo "<!-- DEBUG: Session Data -->\n";
echo "<!-- Pending Owners: " . (isset($_SESSION['pending_owners']) ? count($_SESSION['pending_owners']) : 0) . " -->\n";
echo "<!-- Verified Owners: " . (isset($_SESSION['verified_owners']) ? count($_SESSION['verified_owners']) : 0) . " -->\n";

// Cari dengan prioritas: 1. ID, 2. Index
if (!empty($kos_id)) {
    // Cari berdasarkan ID
    if (isset($_SESSION['verified_owners'])) {
        foreach ($_SESSION['verified_owners'] as $index => $kos) {
            if (isset($kos['id']) && $kos['id'] === $kos_id && $kos['email'] === $_SESSION['user_email']) {
                $kos_data = $kos;
                $kos_source = 'verified_owners';
                $kos_actual_index = $index;
                break;
            }
        }
    }
    
    if (!$kos_data && isset($_SESSION['pending_owners'])) {
        foreach ($_SESSION['pending_owners'] as $index => $kos) {
            if (isset($kos['id']) && $kos['id'] === $kos_id && $kos['email'] === $_SESSION['user_email']) {
                $kos_data = $kos;
                $kos_source = 'pending_owners';
                $kos_actual_index = $index;
                break;
            }
        }
    }
}

// Jika tidak ditemukan berdasarkan ID, coba berdasarkan index
if (!$kos_data && $kos_index >= 0) {
    // Gabungkan semua kos untuk mencari berdasarkan index
    $all_kos = array_merge(
        isset($_SESSION['pending_owners']) ? $_SESSION['pending_owners'] : [],
        isset($_SESSION['verified_owners']) ? $_SESSION['verified_owners'] : []
    );
    
    if (isset($all_kos[$kos_index]) && 
        isset($all_kos[$kos_index]['email']) && 
        $all_kos[$kos_index]['email'] === $_SESSION['user_email']) {
        
        $kos_data = $all_kos[$kos_index];
        
        // Tentukan sumbernya
        if (isset($kos_data['status']) && $kos_data['status'] === 'verified') {
            $kos_source = 'verified_owners';
            // Cari index sebenarnya di verified_owners
            if (isset($_SESSION['verified_owners'])) {
                foreach ($_SESSION['verified_owners'] as $index => $kos) {
                    if (isset($kos['id']) && isset($kos_data['id']) && $kos['id'] === $kos_data['id']) {
                        $kos_actual_index = $index;
                        break;
                    }
                }
            }
        } else {
            $kos_source = 'pending_owners';
            // Cari index sebenarnya di pending_owners
            if (isset($_SESSION['pending_owners'])) {
                foreach ($_SESSION['pending_owners'] as $index => $kos) {
                    if (isset($kos['id']) && isset($kos_data['id']) && $kos['id'] === $kos_data['id']) {
                        $kos_actual_index = $index;
                        break;
                    }
                }
            }
        }
    }
}

// Jika tidak ditemukan, redirect ke dashboard
if (!$kos_data) {
    echo "<!-- DEBUG: Kos not found, redirecting -->\n";
    header("Location: dashboard_pemilik.php");
    exit;
}

// Pastikan kita punya index yang valid
if ($kos_actual_index === null) {
    // Jika tidak ada index, gunakan 0 sebagai default
    $kos_actual_index = 0;
}

// Set default values untuk data yang mungkin tidak ada
$default_data = [
    'id' => 'KOS_' . uniqid(),
    'email' => $_SESSION['user_email'],
    'nama_pemilik' => $_SESSION['user_name'],
    'kos_name' => 'Nama Kos',
    'kos_address' => 'Alamat Kos',
    'kos_type' => 'Putra',
    'rooms' => 1,
    'room_price' => 1500000,
    'kos_description' => '',
    'facilities' => [],
    'rules' => '',
    'latitude' => '-7.31694',
    'longitude' => '109.36340',
    'status' => 'pending',
    'photos' => [],
    'registration_date' => date('Y-m-d H:i:s')
];

// Merge dengan data default
$kos_data = array_merge($default_data, $kos_data);

echo "<!-- DEBUG: Found kos: " . htmlspecialchars($kos_data['kos_name']) . " -->\n";
echo "<!-- DEBUG: Source: $kos_source, Index: $kos_actual_index -->\n";

// Inisialisasi pesan
$success_message = '';

// Proses update jika ada form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!-- DEBUG: Processing POST request -->\n";
    
    // Validasi data
    $required_fields = ['kos_name', 'kos_address', 'kos_type', 'rooms', 'room_price', 'latitude', 'longitude'];
    $valid = true;
    $error_messages = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $valid = false;
            $error_messages[] = "Field $field harus diisi";
        }
    }
    
    if (!$valid) {
        $success_message = "Error: " . implode(", ", $error_messages);
    } else {
        // Ambil data dari form
        $raw_price = $_POST['room_price'];
        $clean_price = str_replace(['.', ','], '', $raw_price);
        $yearly_price = intval($clean_price);
        
        // Validasi koordinat
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $valid = false;
            $success_message = "Error: Koordinat peta tidak valid.";
        }
        
        // Cek jika koordinat masih default UNSOED
        if ($latitude == -7.31694 && $longitude == 109.36340) {
            $valid = false;
            $success_message = "Error: Harap tentukan lokasi kos Anda (bukan lokasi default UNSOED).";
        }
        
        if ($valid) {
            // Siapkan data kos yang diupdate
            $updated_kos = [
                'id' => $kos_data['id'], // PENTING: Simpan ID yang sama!
                'email' => $_SESSION['user_email'],
                'nama_pemilik' => $_SESSION['user_name'],
                'kos_name' => htmlspecialchars($_POST['kos_name']),
                'kos_address' => htmlspecialchars($_POST['kos_address']),
                'kos_type' => $_POST['kos_type'],
                'rooms' => intval($_POST['rooms']),
                'room_price' => $yearly_price,
                'kos_description' => htmlspecialchars($_POST['kos_description']),
                'facilities' => isset($_POST['facilities']) ? $_POST['facilities'] : [],
                'rules' => htmlspecialchars($_POST['rules']),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $kos_data['status'], // Pertahankan status asli
                'photos' => $kos_data['photos'], // Pertahankan foto lama dulu
                'registration_date' => $kos_data['registration_date'] // Pertahankan tanggal registrasi
            ];
            
            // Handle upload foto baru - SAMA DENGAN TAMBAH KOS
            if (isset($_FILES['new_photos']) && !empty($_FILES['new_photos']['name'][0])) {
                $uploaded_photos = [];
                
                foreach ($_FILES['new_photos']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['new_photos']['error'][$key] === UPLOAD_ERR_OK) {
                        $original_name = $_FILES['new_photos']['name'][$key];
                        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $file_name = time() . '_' . uniqid() . '.' . $file_ext;
                            $file_path = $upload_dir . $file_name;
                            
                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $uploaded_photos[] = $file_path;
                            }
                        }
                    }
                }
                
                // Gabungkan foto baru dengan foto lama
                if (!empty($uploaded_photos)) {
                    $updated_kos['photos'] = array_merge($kos_data['photos'], $uploaded_photos);
                }
            }
            
            echo "<!-- DEBUG: Updating kos at source: $kos_source, index: $kos_actual_index -->\n";
            
            // PENTING: Update ke session dengan index yang benar
            if ($kos_source === 'verified_owners') {
                if (!isset($_SESSION['verified_owners'])) {
                    $_SESSION['verified_owners'] = [];
                }
                $_SESSION['verified_owners'][$kos_actual_index] = $updated_kos;
                echo "<!-- DEBUG: Updated in verified_owners -->\n";
            } elseif ($kos_source === 'pending_owners') {
                if (!isset($_SESSION['pending_owners'])) {
                    $_SESSION['pending_owners'] = [];
                }
                $_SESSION['pending_owners'][$kos_actual_index] = $updated_kos;
                echo "<!-- DEBUG: Updated in pending_owners -->\n";
            }
            
            // Update data untuk ditampilkan
            $kos_data = $updated_kos;
            
            // Tambahkan notifikasi
            if (!isset($_SESSION['owner_notifications'])) {
                $_SESSION['owner_notifications'] = [];
            }
            
            $notification_id = count($_SESSION['owner_notifications']) + 1;
            $_SESSION['owner_notifications'][] = [
                'id' => $notification_id,
                'title' => 'Data Kos Diperbarui',
                'message' => 'Data kos "' . $updated_kos['kos_name'] . '" telah berhasil diperbarui.',
                'date' => date('Y-m-d H:i:s'),
                'read' => false,
                'type' => 'info'
            ];
            
            $success_message = "Data kos berhasil diperbarui!";
            echo "<!-- DEBUG: Update successful -->\n";
        }
    }
}

// Fungsi untuk cek fasilitas
function is_facility_checked($facilities, $facility_name) {
    if (empty($facilities)) return false;
    if (is_array($facilities)) {
        return in_array($facility_name, $facilities);
    }
    return strpos($facilities, $facility_name) !== false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kos - KosYuk</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, #0A2C4F 0%, #1a4a7a 100%);
            padding: 25px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
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

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .kos-status {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border-color: rgba(255, 193, 7, 0.3);
        }

        .status-verified {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border-color: rgba(40, 167, 69, 0.3);
        }

        .kos-form {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
            from {
                opacity: 0;
                transform: translateY(20px);
            }
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section-title {
            color: var(--primary);
            font-size: 1.4rem;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--primary-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: var(--primary-light);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }

        .form-group label.required::after {
            content: " *";
            color: var(--danger);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-light);
            background: white;
            box-shadow: 0 0 0 3px rgba(24, 160, 251, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .facility-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
            cursor: pointer;
        }

        .facility-item:hover {
            background: #e9ecef;
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .facility-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .facility-item span {
            font-size: 1rem;
            color: var(--dark);
        }

        /* MAP STYLES */
        .map-container {
            margin-top: 20px;
        }

        .map-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .btn-map-control {
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-map-control:hover {
            background: #082a4d;
            transform: translateY(-2px);
        }
        
        .map-coordinates {
            font-size: 0.9rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .map-coordinates input {
            width: 140px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            text-align: center;
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .map-instructions {
            margin-top: 15px;
            padding: 12px 15px;
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            border-radius: 4px;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .map-instructions i {
            color: #2196f3;
            margin-right: 8px;
        }

        .map-wrapper {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            height: 500px;
        }

        #mapEmbed {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* CSS untuk Photo Slider */
        .photo-slider-container {
            margin-bottom: 30px;
        }

        .photo-slider {
            position: relative;
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            background: #f8f9fa;
        }

        .slide-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide-item.active {
            opacity: 1;
        }

        .slide-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .slide-number {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }

        .slider-nav:hover {
            background: rgba(0,0,0,0.7);
        }

        .slider-nav.prev {
            left: 15px;
        }

        .slider-nav.next {
            right: 15px;
        }

        /* Thumbnails */
        .photo-thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-light) #f1f1f1;
        }

        .photo-thumbnails::-webkit-scrollbar {
            height: 8px;
        }

        .photo-thumbnails::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .photo-thumbnails::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        .thumbnail-item {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.3s;
            flex-shrink: 0;
            border: 2px solid transparent;
        }

        .thumbnail-item:hover {
            opacity: 0.8;
        }

        .thumbnail-item.active {
            opacity: 1;
            border-color: var(--primary-light);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-photo {
            text-align: center;
            padding: 40px;
            color: var(--gray);
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
        }

        .no-photo i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
            display: block;
        }

        .btn-save {
            padding: 16px 40px;
            background: linear-gradient(135deg, var(--success) 0%, #218838 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-save:active {
            transform: translateY(-1px);
        }

        .alert {
            padding: 20px;
            border-radius: 10px;
            margin: 0 30px 30px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .alert-success i {
            color: #155724;
            font-size: 1.3rem;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .alert-error i {
            color: #721c24;
            font-size: 1.3rem;
        }

        .readonly-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary);
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .readonly-info h4 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .readonly-info h4 i {
            color: var(--primary-light);
        }

        .readonly-info p {
            margin-bottom: 10px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .readonly-info strong {
            color: var(--dark);
            min-width: 150px;
            display: inline-block;
        }

        .info-note {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .required::after {
            content: " *";
            color: var(--danger);
        }

        .price-input {
            position: relative;
        }

        .price-input::before {
            content: "Rp";
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-weight: 600;
        }

        .price-input input {
            padding-left: 45px !important;
        }

        /* UPLOAD AREA - SAMA DENGAN TAMBAH KOS */
        .upload-photo-section {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s;
        }

        .upload-photo-section:hover {
            border-color: var(--primary-light);
        }

        .upload-photo-section h4 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .upload-area {
            text-align: center;
            padding: 25px;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e9ecef;
        }

        .upload-area:hover {
            border-color: var(--primary-light);
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .upload-area.dragover {
            border-color: var(--success);
            background: rgba(40, 167, 69, 0.1);
        }

        .upload-area i {
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 10px;
            display: block;
        }

        .upload-area p {
            font-size: 0.9rem;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .upload-area span {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .upload-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            padding: 8px;
        }

        .upload-preview-item {
            position: relative;
            border-radius: 6px;
            overflow: hidden;
            height: 120px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .upload-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-photo {
            position: absolute;
            top: 3px;
            right: 3px;
            background: var(--danger);
            color: white;
            border: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 0.8rem;
        }

        .remove-photo:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .upload-actions {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .btn-upload {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--success) 0%, #218838 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-remove {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--danger) 0%, #c82333 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-remove:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                border-radius: 10px;
            }
            
            .header {
                padding: 20px;
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
                order: -1;
                width: 100%;
            }
            
            .back-btn {
                align-self: flex-start;
            }
            
            .kos-status {
                align-self: flex-end;
            }
            
            .kos-form {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .facilities-grid {
                grid-template-columns: 1fr;
            }
            
            .photo-slider {
                height: 300px;
            }
            
            .photo-thumbnails {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .thumbnail-item {
                width: 60px;
                height: 60px;
            }
            
            .upload-preview {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
            .btn-save {
                width: 100%;
                justify-content: center;
            }
            
            .upload-actions {
                flex-direction: column;
            }
            
            .btn-upload, .btn-remove {
                width: 100%;
                justify-content: center;
            }
            
            .map-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .map-coordinates {
                width: 100%;
                justify-content: space-between;
            }
            
            .map-coordinates input {
                width: calc(50% - 5px);
            }
            
            .map-wrapper {
                height: 400px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.3rem;
            }
            
            .form-section-title {
                font-size: 1.2rem;
            }
            
            .readonly-info {
                padding: 15px;
            }
            
            .readonly-info strong {
                min-width: 120px;
            }
            
            .upload-area {
                padding: 15px;
            }
            
            .upload-area i {
                font-size: 1.5rem;
            }
            
            .upload-area p {
                font-size: 0.8rem;
            }
            
            .map-wrapper {
                height: 350px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Success/Error Message -->
        <?php if(!empty($success_message)): ?>
        <div class="alert <?php echo strpos($success_message, 'Error:') === 0 ? 'alert-error' : 'alert-success'; ?>">
            <i class="fas <?php echo strpos($success_message, 'Error:') === 0 ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
            <span><?php echo $success_message; ?></span>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="header">
            <a href="dashboard_pemilik.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h1 class="page-title">Detail Kos: <?php echo htmlspecialchars($kos_data['kos_name']); ?></h1>
            <span class="kos-status <?php echo $kos_data['status'] === 'verified' ? 'status-verified' : 'status-pending'; ?>">
                <i class="fas <?php echo $kos_data['status'] === 'verified' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                <?php echo $kos_data['status'] === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi'; ?>
            </span>
        </div>

        <!-- Form Detail Kos -->
        <form method="POST" action="" class="kos-form" enctype="multipart/form-data" id="kosForm">
            <!-- Section 1: Informasi Dasar Kos -->
            <div class="form-section" style="animation-delay: 0.1s">
                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i>
                    Informasi Dasar Kos
                </h3>
                
                <div class="form-group">
                    <label for="kos_name" class="required">Nama Kos</label>
                    <input type="text" id="kos_name" name="kos_name" 
                           value="<?php echo htmlspecialchars($kos_data['kos_name']); ?>" 
                           required
                           placeholder="Masukkan nama kos">
                </div>

                <div class="form-group">
                    <label for="kos_address" class="required">Alamat Lengkap</label>
                    <textarea id="kos_address" name="kos_address" required
                              placeholder="Masukkan alamat lengkap kos"><?php echo htmlspecialchars($kos_data['kos_address']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kos_type" class="required">Tipe Kos</label>
                        <select id="kos_type" name="kos_type" required>
                            <option value="">Pilih tipe kos</option>
                            <option value="Putra" <?php echo $kos_data['kos_type'] === 'Putra' ? 'selected' : ''; ?>>Putra</option>
                            <option value="Putri" <?php echo $kos_data['kos_type'] === 'Putri' ? 'selected' : ''; ?>>Putri</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rooms" class="required">Jumlah Kamar Tersedia</label>
                        <input type="number" id="rooms" name="rooms" min="1" max="100"
                               value="<?php echo $kos_data['rooms']; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Harga sewa per TAHUN -->
                    <div class="form-group">
                        <label for="room_price" class="required">Harga Sewa per Tahun</label>
                        <div class="price-input">
                            <input type="text" id="room_price" name="room_price" 
                                   value="<?php echo number_format($kos_data['room_price'], 0, ',', '.'); ?>" required>
                        </div>
                        <div class="info-note">
                            <i class="fas fa-calculator"></i>
                            <span>Harga sewa per tahun.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kos_description">Deskripsi Kos</label>
                        <textarea id="kos_description" name="kos_description"
                                  placeholder="Deskripsikan fasilitas, keunggulan, dan kondisi kos"><?php echo htmlspecialchars($kos_data['kos_description']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Fasilitas -->
            <div class="form-section" style="animation-delay: 0.2s">
                <h3 class="form-section-title">
                    <i class="fas fa-th-list"></i>
                    Fasilitas Kos
                </h3>
                
                <div class="facilities-grid">
                    <?php
                    $all_facilities = [
                        'Kamar Mandi Dalam', 'AC', 'Kipas Angin', 'Lemari', 'Kasur', 
                        'Meja Belajar', 'WiFi', 'Dapur Bersama', 'Ruang Tamu', 
                        'Laundry', 'Parkir Motor', 'Parkir Mobil', 'CCTV', 
                        'Security 24 Jam', 'Air Panas'
                    ];
                    
                    foreach ($all_facilities as $facility):
                        $checked = is_facility_checked($kos_data['facilities'], $facility) ? 'checked' : '';
                    ?>
                    <label class="facility-item">
                        <input type="checkbox" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>" <?php echo $checked; ?>>
                        <span><?php echo htmlspecialchars($facility); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 3: Peraturan -->
            <div class="form-section" style="animation-delay: 0.3s">
                <h3 class="form-section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Peraturan Kos
                </h3>
                
                <div class="form-group">
                    <label for="rules">Aturan dan Ketentuan</label>
                    <textarea id="rules" name="rules"
                              placeholder="Masukkan aturan kos, seperti waktu berkunjung, larangan, dll."><?php echo htmlspecialchars($kos_data['rules']); ?></textarea>
                </div>
            </div>

            <!-- Section 4: Lokasi dengan Google Maps -->
            <div class="form-section" style="animation-delay: 0.4s">
                <h3 class="form-section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Lokasi Kos
                </h3>
                
                <div class="map-instructions">
                    <i class="fas fa-info-circle"></i>
                    <strong>Panduan menentukan lokasi:</strong> 
                    Pilih lokasi di peta di bawah, lalu masukkan koordinat manual di form.
                </div>
                
                <!-- Input koordinat -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude" class="required">Latitude</label>
                        <input type="text" id="latitude" name="latitude" 
                               value="<?php echo $kos_data['latitude']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude" class="required">Longitude</label>
                        <input type="text" id="longitude" name="longitude" 
                               value="<?php echo $kos_data['longitude']; ?>" required>
                    </div>
                </div>
                
                <!-- Google Maps Embed -->
                <div class="map-container">
                    <div class="map-controls">
                        <div class="map-control-buttons">
                            <button type="button" class="btn-map-control" id="btnResetMap">
                                <i class="fas fa-sync-alt"></i> Reset ke UNSOED
                            </button>
                            <button type="button" class="btn-map-control" id="btnCopyCoords">
                                <i class="fas fa-copy"></i> Salin Koordinat
                            </button>
                        </div>
                        
                        <div class="map-coordinates">
                            <span>Koordinat: </span>
                            <input type="text" id="displayLat" value="<?php echo $kos_data['latitude']; ?>" readonly> 
                            <span>,</span>
                            <input type="text" id="displayLng" value="<?php echo $kos_data['longitude']; ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="map-wrapper">
                        <?php
                        // Bangun URL embed dengan lokasi kos
                        $embed_url = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.997875156683!2d109.36340357586562!3d-7.316944292774282!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655c5b9c5e5e5d%3A0x8f8b8f8b8f8b8f8b!2sFakultas%20Teknik%20Universitas%20Jenderal%20Soedirman%20(Kampus%20Blater)!5e0!3m2!1sid!2sid!4v1734000000000!5m2!1sid!2sid";
                        
                        // Jika ada koordinat spesifik, gunakan view dengan marker
                        if (!empty($kos_data['latitude']) && !empty($kos_data['longitude'])) {
                            $lat = $kos_data['latitude'];
                            $lng = $kos_data['longitude'];
                            $embed_url = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.997875156683!2d{$lng}!3d{$lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655c5b9c5e5e5d%3A0x8f8b8f8b8f8b8f8b!2sFakultas%20Teknik%20Universitas%20Jenderal%20Soedirman%20(Kampus%20Blater)!5e0!3m2!1sid!2sid!4v1734000000000!5m2!1sid!2sid&q={$lat},{$lng}";
                        }
                        ?>
                        <iframe 
                            id="mapEmbed"
                            src="<?php echo $embed_url; ?>"
                            width="100%"
                            height="100%"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="info-note">
                        <i class="fas fa-hand-point-up"></i>
                        <span>1. Klik kanan di lokasi peta yang diinginkan</span>
                    </div>
                    <div class="info-note">
                        <i class="fas fa-hand-point-up"></i>
                        <span>2. Pilih "Apa di sini?" untuk mendapatkan koordinat</span>
                    </div>
                    <div class="info-note">
                        <i class="fas fa-hand-point-up"></i>
                        <span>3. Salin koordinat ke form di atas</span>
                    </div>
                </div>
            </div>

            <!-- Section 5: Foto Kos dengan Slider -->
            <div class="form-section" style="animation-delay: 0.5s">
                <h3 class="form-section-title">
                    <i class="fas fa-images"></i>
                    Foto Kos
                </h3>
                
                <!-- Slider untuk foto -->
                <div class="photo-slider-container">
                    <?php if (!empty($kos_data['photos']) && is_array($kos_data['photos'])): ?>
                        <div class="photo-slider" id="photoSlider">
                            <?php foreach ($kos_data['photos'] as $index => $photo): ?>
                                <div class="slide-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Foto Kos <?php echo $index + 1; ?>">
                                    <div class="slide-number"><?php echo $index + 1; ?> / <?php echo count($kos_data['photos']); ?></div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Navigation buttons -->
                            <?php if (count($kos_data['photos']) > 1): ?>
                                <button class="slider-nav prev" type="button" onclick="changeSlide(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="slider-nav next" type="button" onclick="changeSlide(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Thumbnails -->
                        <?php if (count($kos_data['photos']) > 1): ?>
                            <div class="photo-thumbnails">
                                <?php foreach ($kos_data['photos'] as $index => $photo): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)">
                                        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-photo">
                            <i class="fas fa-image"></i>
                            <h3>Belum ada foto yang diunggah</h3>
                            <p>Silakan unggah foto kos Anda menggunakan form di bawah</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Form untuk upload foto baru (SAMA DENGAN TAMBAH KOS) -->
                <div class="upload-photo-section">
                    <h4>Tambah Foto Baru</h4>
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Seret dan lepas foto di sini atau klik untuk memilih</p>
                        <span>Format: JPG, PNG | Maks: 5MB per foto</span>
                        <input type="file" id="photoUpload" name="new_photos[]" multiple accept="image/*" style="display: none;">
                    </div>
                    
                    <!-- Preview foto yang akan diupload -->
                    <div class="upload-preview" id="uploadPreview"></div>
                    
                    <!-- Tombol aksi -->
                    <div class="upload-actions">
                        <button type="button" class="btn-upload" onclick="document.getElementById('photoUpload').click()">
                            <i class="fas fa-plus"></i> Pilih Foto
                        </button>
                        <button type="button" class="btn-remove" onclick="removeAllPhotos()">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </div>
                    
                    <div class="info-note">
                        <i class="fas fa-info-circle"></i>
                        <span>Anda dapat mengunggah maksimal 10 foto tambahan</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript untuk Photo Slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide-item');
        const thumbnails = document.querySelectorAll('.thumbnail-item');

        function showSlide(n) {
            if (slides.length === 0) return;
            
            // Reset semua slide
            slides.forEach(slide => slide.classList.remove('active'));
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            
            // Hitung slide baru
            currentSlide = (n + slides.length) % slides.length;
            
            // Tampilkan slide aktif
            slides[currentSlide].classList.add('active');
            if (thumbnails.length > 0) {
                thumbnails[currentSlide].classList.add('active');
            }
        }

        function changeSlide(n) {
            showSlide(currentSlide + n);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        // Auto slide jika ada lebih dari 1 foto
        if (slides.length > 1) {
            setInterval(() => {
                changeSlide(1);
            }, 5000);
        }

        // Fungsi untuk reset ke lokasi UNSOED
        document.getElementById('btnResetMap').addEventListener('click', function() {
            document.getElementById('latitude').value = '-7.31694';
            document.getElementById('longitude').value = '109.36340';
            document.getElementById('displayLat').value = '-7.31694';
            document.getElementById('displayLng').value = '109.36340';
            
            // Reset iframe ke lokasi default
            const mapEmbed = document.getElementById('mapEmbed');
            mapEmbed.src = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.997875156683!2d109.36340357586562!3d-7.316944292774282!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655c5b9c5e5e5d%3A0x8f8b8f8b8f8b8f8b!2sFakultas%20Teknik%20Universitas%20Jenderal%20Soedirman%20(Kampus%20Blater)!5e0!3m2!1sid!2sid!4v1734000000000!5m2!1sid!2sid";
            
            alert("Koordinat telah direset ke lokasi Fakultas Teknik UNSOED.");
        });
        
        // Fungsi untuk menyalin koordinat
        document.getElementById('btnCopyCoords').addEventListener('click', function() {
            const lat = document.getElementById('displayLat').value;
            const lng = document.getElementById('displayLng').value;
            const coords = `${lat}, ${lng}`;
            
            navigator.clipboard.writeText(coords)
                .then(() => {
                    alert(`Koordinat ${coords} berhasil disalin ke clipboard!`);
                })
                .catch(err => {
                    console.error('Gagal menyalin: ', err);
                    // Fallback untuk browser lama
                    const textArea = document.createElement('textarea');
                    textArea.value = coords;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert(`Koordinat ${coords} berhasil disalin ke clipboard!`);
                });
        });
        
        // Update display saat input berubah
        document.getElementById('latitude').addEventListener('input', function() {
            document.getElementById('displayLat').value = this.value;
        });
        
        document.getElementById('longitude').addEventListener('input', function() {
            document.getElementById('displayLng').value = this.value;
        });
        
        // Format harga saat input
        const priceInput = document.getElementById('room_price');
        
        if (priceInput) {
            priceInput.addEventListener('input', function(e) {
                // Hapus semua karakter kecuali angka
                let value = this.value.replace(/[^\d]/g, '');
                
                // Format dengan titik sebagai pemisah ribuan
                if (value) {
                    value = parseInt(value, 10).toLocaleString('id-ID');
                }
                
                this.value = value;
            });
        }
        
        // JavaScript untuk Upload Foto (SAMA DENGAN TAMBAH KOS)
        const uploadArea = document.getElementById('uploadArea');
        const photoUpload = document.getElementById('photoUpload');
        const uploadPreview = document.getElementById('uploadPreview');
        
        if (uploadArea && photoUpload && uploadPreview) {
            // Drag and drop functionality
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                handleFiles(files);
            });
            
            // Handle file selection
            photoUpload.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });
            
            function handleFiles(files) {
                const maxFiles = 10;
                const existingFiles = uploadPreview.children.length;
                
                if (existingFiles + files.length > maxFiles) {
                    alert(`Maksimal ${maxFiles} foto. Anda sudah memiliki ${existingFiles} foto.`);
                    return;
                }
                
                Array.from(files).forEach(file => {
                    // Cek ukuran file (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} terlalu besar. Maksimal 5MB.`);
                        return;
                    }
                    
                    // Cek tipe file
                    if (!file.type.startsWith('image/')) {
                        alert(`File ${file.name} bukan gambar.`);
                        return;
                    }
                    
                    // Buat preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'upload-preview-item';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-photo';
                        removeBtn.type = 'button';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.onclick = () => {
                            previewItem.remove();
                            // Update input file
                            const dataTransfer = new DataTransfer();
                            const fileInput = document.getElementById('photoUpload');
                            Array.from(fileInput.files).forEach((f, index) => {
                                if (f !== file) {
                                    dataTransfer.items.add(f);
                                }
                            });
                            fileInput.files = dataTransfer.files;
                        };
                        
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        uploadPreview.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });
            }
            
            function removeAllPhotos() {
                if (confirm('Hapus semua foto yang dipilih?')) {
                    uploadPreview.innerHTML = '';
                    photoUpload.value = '';
                }
            }
            
            // Expose function to global scope
            window.removeAllPhotos = removeAllPhotos;
        }
        
        // Validasi koordinat
        function validateCoordinates() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            
            if (isNaN(lat) || isNaN(lng)) {
                alert('Koordinat harus berupa angka!');
                return false;
            }
            
            if (lat < -90 || lat > 90) {
                alert('Latitude harus antara -90 dan 90!');
                return false;
            }
            
            if (lng < -180 || lng > 180) {
                alert('Longitude harus antara -180 dan 180!');
                return false;
            }
            
            // Cek jika koordinat masih default UNSOED
            if (lat === -7.31694 && lng === 109.36340) {
                const confirmChange = confirm('Koordinat masih menggunakan lokasi default UNSOED.\nApakah Anda yakin lokasi kos Anda sama dengan UNSOED?');
                return confirmChange;
            }
            
            return true;
        }
        
        // Validasi form sebelum submit
        const kosForm = document.getElementById('kosForm');
        if (kosForm) {
            kosForm.addEventListener('submit', function(e) {
                let isValid = true;
                let errorMessage = [];
                
                // Validasi nama kos
                const kosName = document.getElementById('kos_name').value.trim();
                if (!kosName) {
                    isValid = false;
                    errorMessage.push('• Nama kos harus diisi');
                }
                
                // Validasi alamat
                const kosAddress = document.getElementById('kos_address').value.trim();
                if (!kosAddress) {
                    isValid = false;
                    errorMessage.push('• Alamat kos harus diisi');
                }
                
                // Validasi tipe kos
                const kosType = document.getElementById('kos_type').value;
                if (!kosType) {
                    isValid = false;
                    errorMessage.push('• Tipe kos harus dipilih');
                }
                
                // Validasi jumlah kamar
                const rooms = document.getElementById('rooms').value;
                if (!rooms || parseInt(rooms) < 1) {
                    isValid = false;
                    errorMessage.push('• Jumlah kamar harus minimal 1');
                }
                
                // Validasi harga
                const price = priceInput ? priceInput.value.replace(/[^\d]/g, '') : '';
                if (!price || parseInt(price) < 100000) {
                    isValid = false;
                    errorMessage.push('• Harga sewa minimal Rp 100.000 per tahun');
                }
                
                // Validasi koordinat
                if (!validateCoordinates()) {
                    isValid = false;
                    // Error message sudah ditampilkan di validateCoordinates()
                }
                
                if (!isValid && errorMessage.length > 0) {
                    e.preventDefault();
                    const errorList = errorMessage.join('\n');
                    alert('Harap perbaiki kesalahan berikut:\n\n' + errorList + '\n\nKlik OK untuk kembali ke form.');
                    
                    // Scroll ke error pertama
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else if (isValid) {
                    // Konfirmasi sebelum submit
                    if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                        e.preventDefault();
                    } else {
                        // Tampilkan loading
                        const submitBtn = document.querySelector('.btn-save');
                        if (submitBtn) {
                            const originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                            submitBtn.disabled = true;
                            
                            // Reset setelah 3 detik (untuk mencegah double submit)
                            setTimeout(() => {
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            }, 3000);
                        }
                    }
                }
            });
        }
        
        // Focus ke field pertama
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Document loaded");
            
            // Update display coordinates
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            const displayLat = document.getElementById('displayLat');
            const displayLng = document.getElementById('displayLng');
            
            if (displayLat) displayLat.value = lat;
            if (displayLng) displayLng.value = lng;
            
            // Format harga
            if (priceInput) {
                let value = priceInput.value.replace(/[^\d]/g, '');
                if (value) {
                    priceInput.value = parseInt(value, 10).toLocaleString('id-ID');
                }
            }
            
            // Initialize photo slider
            if (slides.length > 0) {
                showSlide(0);
            }
        });
    </script>
</body>
</html>