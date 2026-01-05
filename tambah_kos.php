<?php
session_start();

// Cek apakah user sudah login sebagai pemilik kos
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'pemilik_kos') {
    header("Location: login.php");
    exit;
}

// Inisialisasi pesan
$success_message = '';
$error_message = '';

// Proses form tambah kos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi data
    $required_fields = ['kos_name', 'kos_address', 'kos_type', 'rooms', 'room_price', 'latitude', 'longitude'];
    $valid = true;
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $valid = false;
            $error_message = "Harap lengkapi semua field yang wajib diisi.";
            break;
        }
    }
    
    if ($valid) {
        // Format harga - simpan sebagai harga TAHUNAN
        $raw_price = $_POST['room_price'];
        $clean_price = str_replace(['.', ','], '', $raw_price);
        $yearly_price = intval($clean_price);
        
        // Validasi koordinat
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $valid = false;
            $error_message = "Koordinat peta tidak valid.";
        }
        
        // Cek jika koordinat masih default UNSOED
        if ($latitude == -7.31694 && $longitude == 109.36340) {
            $valid = false;
            $error_message = "Harap tentukan lokasi kos Anda di peta (bukan lokasi default UNSOED).";
        }
        
        if ($valid) {
            // Siapkan data kos baru
            $new_kos = [
                'email' => $_SESSION['user_email'],
                'nama_pemilik' => $_SESSION['user_name'],
                'kos_name' => htmlspecialchars($_POST['kos_name']),
                'kos_address' => htmlspecialchars($_POST['kos_address']),
                'kos_type' => $_POST['kos_type'],
                'rooms' => intval($_POST['rooms']),
                'room_price' => $yearly_price,
                'kos_description' => htmlspecialchars($_POST['kos_description'] ?? ''),
                'facilities' => isset($_POST['facilities']) ? $_POST['facilities'] : [],
                'rules' => htmlspecialchars($_POST['rules'] ?? ''),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'pending',
                'registration_date' => date('Y-m-d H:i:s'),
                'photos' => []
            ];
            
            // Handle upload foto - SAMA DENGAN DETAIL KOS
            $upload_dir = 'uploads/kos_photos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // PERBAIKAN: Gunakan 'new_photos[]' seperti di detail_kos_pemilik.php
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
                
                $new_kos['photos'] = $uploaded_photos;
            }
            
            // Tambahkan ke session pending_owners
            if (!isset($_SESSION['pending_owners'])) {
                $_SESSION['pending_owners'] = [];
            }
            
            // Generate ID untuk kos
            $kos_id = 'KOS_' . uniqid();
            $new_kos['id'] = $kos_id;
            
            // SIMPAN: Tambahkan ke array pending_owners
            $_SESSION['pending_owners'][] = $new_kos;
            
            // Tambahkan notifikasi
            if (!isset($_SESSION['owner_notifications'])) {
                $_SESSION['owner_notifications'] = [];
            }
            
            $notification_id = count($_SESSION['owner_notifications']) + 1;
            $_SESSION['owner_notifications'][] = [
                'id' => $notification_id,
                'title' => 'Kos Baru Ditambahkan',
                'message' => 'Kos "' . $new_kos['kos_name'] . '" telah berhasil ditambahkan dan menunggu verifikasi admin.',
                'date' => date('Y-m-d H:i:s'),
                'read' => false,
                'type' => 'info'
            ];
            
            $success_message = "Kos berhasil ditambahkan! Menunggu verifikasi admin.";
            
            // Redirect ke dashboard setelah 2 detik
            header("refresh:2;url=dashboard_pemilik.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kos Baru - KosYuk</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #0A2C4F 0%, #1a4a7a 100%);
            padding: 20px 30px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            flex: 1;
        }

        .container {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .kos-form-container {
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

        .kos-form {
            padding: 30px;
        }

        .form-section {
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
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
            display: flex;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 1rem;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--success) 0%, #218838 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
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

        /* UPLOAD PHOTO STYLES - SAMA DENGAN DETAIL KOS */
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
                padding: 0 15px;
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .back-btn {
                align-self: flex-start;
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
            
            .upload-actions {
                flex-direction: column;
            }
            
            .btn-upload, .btn-remove {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .map-wrapper {
                height: 350px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-section-title {
                font-size: 1.2rem;
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
            
            .upload-preview {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="dashboard_pemilik.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <h1 class="page-title">Tambah Kos Baru</h1>
            <div style="width: 100px;"></div> <!-- Spacer untuk alignment -->
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <!-- Alert Messages -->
        <?php if(!empty($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><strong>Berhasil!</strong> <?php echo $success_message; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><strong>Error:</strong> <?php echo $error_message; ?></span>
        </div>
        <?php endif; ?>

        <!-- Form Tambah Kos -->
        <div class="kos-form-container">
            <form method="POST" action="" class="kos-form" enctype="multipart/form-data" id="kosForm">
                <!-- Section 1: Informasi Dasar Kos -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Dasar Kos
                    </h3>
                    
                    <div class="form-group">
                        <label for="kos_name" class="required">Nama Kos</label>
                        <input type="text" id="kos_name" name="kos_name" 
                               required
                               placeholder="Masukkan nama kos">
                    </div>

                    <div class="form-group">
                        <label for="kos_address" class="required">Alamat Lengkap</label>
                        <textarea id="kos_address" name="kos_address" required
                                  placeholder="Masukkan alamat lengkap kos (RT/RW, Kelurahan, Kecamatan, Kota)"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kos_type" class="required">Tipe Kos</label>
                            <select id="kos_type" name="kos_type" required>
                                <option value="">Pilih tipe kos</option>
                                <option value="Putra">Putra</option>
                                <option value="Putri">Putri</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rooms" class="required">Jumlah Kamar Tersedia</label>
                            <input type="number" id="rooms" name="rooms" min="1" max="100" value="1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- Harga sewa per TAHUN -->
                        <div class="form-group">
                            <label for="room_price" class="required">Harga Sewa per Tahun</label>
                            <div class="price-input">
                                <input type="text" id="room_price" name="room_price" required>
                            </div>
                            <div class="info-note">
                                <i class="fas fa-calculator"></i>
                                <span>Masukkan harga sewa per tahun.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="kos_description">Deskripsi Kos</label>
                            <textarea id="kos_description" name="kos_description"
                                      placeholder="Deskripsikan fasilitas, keunggulan, dan kondisi kos"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Fasilitas -->
                <div class="form-section">
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
                        ?>
                        <label class="facility-item">
                            <input type="checkbox" name="facilities[]" value="<?php echo htmlspecialchars($facility); ?>">
                            <span><?php echo htmlspecialchars($facility); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section 3: Peraturan -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-clipboard-list"></i>
                        Peraturan Kos
                    </h3>
                    
                    <div class="form-group">
                        <label for="rules">Aturan dan Ketentuan</label>
                        <textarea id="rules" name="rules"
                                  placeholder="Masukkan aturan kos, seperti waktu berkunjung, larangan, dll."></textarea>
                    </div>
                </div>

                <!-- Section 4: Lokasi dengan Google Maps -->
                <div class="form-section">
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
                                   value="-7.31694" required
                                   placeholder="-7.31694">
                        </div>
                        
                        <div class="form-group">
                            <label for="longitude" class="required">Longitude</label>
                            <input type="text" id="longitude" name="longitude" 
                                   value="109.36340" required
                                   placeholder="109.36340">
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
                                <input type="text" id="displayLat" value="-7.31694" readonly> 
                                <span>,</span>
                                <input type="text" id="displayLng" value="109.36340" readonly>
                            </div>
                        </div>
                        
                        <div class="map-wrapper">
                            <iframe 
                                id="mapEmbed"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.997875156683!2d109.36340357586562!3d-7.316944292774282!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655c5b9c5e5e5d%3A0x8f8b8f8b8f8b8f8b!2sFakultas%20Teknik%20Universitas%20Jenderal%20Soedirman%20(Kampus%20Blater)!5e0!3m2!1sid!2sid!4v1734000000000!5m2!1sid!2sid"
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

                <!-- Section 5: Foto Kos (SAMA DENGAN DETAIL KOS) -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-images"></i>
                        Foto Kos
                    </h3>
                    
                    <!-- Form untuk upload foto baru -->
                    <div class="upload-photo-section">
                        <h4>Unggah Foto Kos</h4>
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
                            <span>Minimal 1 foto, maksimal 10 foto</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions">
                    <a href="dashboard_pemilik.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Kos Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
        
        // JavaScript untuk Upload Foto (SAMA DENGAN DETAIL KOS)
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
        document.getElementById('kosForm').addEventListener('submit', function(e) {
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
            
            // Validasi foto (minimal 1 foto)
            const hasPhotos = uploadPreview.children.length > 0 || (photoUpload.files && photoUpload.files.length > 0);
            if (!hasPhotos) {
                isValid = false;
                errorMessage.push('• Minimal unggah 1 foto kos');
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
                if (!confirm('Apakah Anda yakin ingin menyimpan data kos baru?')) {
                    e.preventDefault();
                } else {
                    // Tampilkan loading
                    const submitBtn = document.querySelector('.btn-primary');
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
        });
        
        // Focus ke field pertama
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('kos_name').focus();
            
            // Format harga default
            if (priceInput) {
                priceInput.value = '1.500.000';
            }
            
            // Inisialisasi display koordinat
            document.getElementById('displayLat').value = document.getElementById('latitude').value;
            document.getElementById('displayLng').value = document.getElementById('longitude').value;
        });
    </script>
</body>
</html>