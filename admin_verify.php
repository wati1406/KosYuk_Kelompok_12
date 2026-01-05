<?php
session_start();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if($index !== null && isset($_SESSION['pending_owners'][$index])) {
        $owner = $_SESSION['pending_owners'][$index];
        
        if($action === 'verify') {
            // 1. Update status owner
            $owner['status'] = 'verified';
            $owner['verified_date'] = date('Y-m-d H:i:s');
            $owner['verified_by'] = $_SESSION['admin_username'];
            
            // 2. Pastikan array verified_owners ada
            if(!isset($_SESSION['verified_owners'])) {
                $_SESSION['verified_owners'] = [];
            }
            
            // 3. Tambahkan ke verified_owners
            $_SESSION['verified_owners'][] = $owner;
            
            // 4. Hapus dari pending_owners
            unset($_SESSION['pending_owners'][$index]);
            
            // 5. Re-index array pending_owners
            $_SESSION['pending_owners'] = array_values($_SESSION['pending_owners']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pemilik kos berhasil diverifikasi',
                'owner_name' => $owner['nama']
            ]);
            exit;
            
        } elseif($action === 'reject') {
            $reason = $_POST['reason'] ?? '';
            
            // Simpan ke rejected jika perlu
            if(!isset($_SESSION['rejected_owners'])) {
                $_SESSION['rejected_owners'] = [];
            }
            
            $owner['status'] = 'rejected';
            $owner['rejected_date'] = date('Y-m-d H:i:s');
            $owner['rejection_reason'] = $reason;
            $_SESSION['rejected_owners'][] = $owner;
            
            // Hapus dari pending
            unset($_SESSION['pending_owners'][$index]);
            $_SESSION['pending_owners'] = array_values($_SESSION['pending_owners']);
            
            echo json_encode(['success' => true, 'message' => 'Pemilik kos ditolak']);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>