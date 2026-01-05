<?php
session_start();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index'] ?? null;
    $action = $_POST['action'] ?? '';
    
    // Reverse index karena pesan ditampilkan dengan array_reverse
    $total_messages = count($_SESSION['contact_messages']);
    $actual_index = $total_messages - $index - 1;
    
    if($actual_index >= 0 && isset($_SESSION['contact_messages'][$actual_index])) {
        
        if($action === 'mark_read') {
            // Tandai sebagai sudah dibaca
            $_SESSION['contact_messages'][$actual_index]['read'] = true;
            echo json_encode(['success' => true]);
            
        } elseif($action === 'delete') {
            // Hapus pesan
            array_splice($_SESSION['contact_messages'], $actual_index, 1);
            echo json_encode(['success' => true]);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Pesan tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>