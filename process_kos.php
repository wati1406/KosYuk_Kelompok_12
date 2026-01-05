<?php
// process_kos.php - Untuk handle semua operasi kos pemilik
session_start();

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'pemilik_kos') {
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'tambah':
        tambahKosBaru();
        break;
    case 'edit':
        editKos();
        break;
    case 'hapus':
        hapusKos();
        break;
    case 'get_detail':
        getDetailKos();
        break;
    case 'update_profile':
        updateProfile();
        break;
    case 'mark_all_notifications_read':
        markAllNotificationsRead();
        break;
    case 'mark_notification_read':
        markNotificationRead();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
}

function tambahKosBaru() {
    $data = $_POST;
    $user_email = $_SESSION['user_email'];
    
    // Validasi data
    $required_fields = ['kos_name', 'kos_address', 'kos_type', 'rooms', 'room_price', 'latitude', 'longitude'];
    foreach($required_fields as $field) {
        if(empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => 'Field ' . $field . ' harus diisi']);
            return;
        }
    }
    
    // Validasi koordinat
    $latitude = floatval($data['latitude']);
    $longitude = floatval($data['longitude']);
    
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        echo json_encode(['success' => false, 'message' => 'Koordinat tidak valid']);
        return;
    }
    
    // Cek jika koordinat masih default
    if ($latitude == -7.31694 && $longitude == 109.36340) {
        echo json_encode(['success' => false, 'message' => 'Harap tentukan lokasi kos di peta']);
        return;
    }
    
    // Format harga - simpan sebagai harga TAHUNAN
    $raw_price = $data['room_price'];
    $clean_price = str_replace(['.', ','], '', $raw_price);
    $yearly_price = intval($clean_price);
    
    // Siapkan data kos baru
    $new_kos = [
        'id' => 'KOS_' . uniqid(),
        'email' => $user_email,
        'nama_pemilik' => $_SESSION['user_name'],
        'kos_name' => htmlspecialchars($data['kos_name']),
        'kos_address' => htmlspecialchars($data['kos_address']),
        'kos_type' => $data['kos_type'],
        'rooms' => intval($data['rooms']),
        'room_price' => $yearly_price, // Harga TAHUNAN
        'kos_description' => htmlspecialchars($data['kos_description'] ?? ''),
        'facilities' => isset($data['facilities']) ? (is_array($data['facilities']) ? $data['facilities'] : [$data['facilities']]) : [],
        'rules' => htmlspecialchars($data['rules'] ?? ''),
        'latitude' => $latitude,
        'longitude' => $longitude,
        'status' => 'pending',
        'registration_date' => date('Y-m-d H:i:s'),
        'photos' => isset($data['photos']) ? $data['photos'] : []
    ];
    
    // Tambahkan ke session pending_owners
    if (!isset($_SESSION['pending_owners'])) {
        $_SESSION['pending_owners'] = [];
    }
    
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
        'type' => 'info',
        'kos_name' => $new_kos['kos_name']
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Kos berhasil ditambahkan! Menunggu verifikasi admin.',
        'kos_id' => $new_kos['id']
    ]);
}

function getDetailKos() {
    $kos_index = $_GET['kos_index'] ?? '';
    $kos_index = intval($kos_index);
    
    // Cari di semua kos
    $all_kos = array_merge($_SESSION['pending_owners'] ?? [], $_SESSION['verified_owners'] ?? []);
    
    if (isset($all_kos[$kos_index])) {
        $kos_data = $all_kos[$kos_index];
        $kos_data['index'] = $kos_index;
        echo json_encode([
            'success' => true,
            'data' => $kos_data
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kos tidak ditemukan']);
    }
}

function updateProfile() {
    $data = $_POST;
    
    // Update session data
    $_SESSION['user_phone'] = $data['phone'] ?? '';
    $_SESSION['user_ktp'] = $data['ktp'] ?? '';
    $_SESSION['user_address'] = $data['address'] ?? '';
    $_SESSION['user_bank_name'] = $data['bankName'] ?? '';
    $_SESSION['user_account_name'] = $data['accountName'] ?? '';
    $_SESSION['user_account_number'] = $data['accountNumber'] ?? '';
    
    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diperbarui!'
    ]);
}

function markAllNotificationsRead() {
    if (!isset($_SESSION['owner_notifications'])) {
        $_SESSION['owner_notifications'] = [];
    }
    
    // Tandai semua notifikasi sebagai dibaca
    foreach ($_SESSION['owner_notifications'] as &$notification) {
        $notification['read'] = true;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Semua notifikasi telah ditandai sebagai dibaca'
    ]);
}

function markNotificationRead() {
    $notification_id = $_GET['id'] ?? 0;
    $notification_id = intval($notification_id);
    
    if (!isset($_SESSION['owner_notifications'])) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada notifikasi']);
        return;
    }
    
    // Cari notifikasi berdasarkan ID
    foreach ($_SESSION['owner_notifications'] as &$notification) {
        if ($notification['id'] === $notification_id) {
            $notification['read'] = true;
            echo json_encode([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai dibaca'
            ]);
            return;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Notifikasi tidak ditemukan']);
}
?>