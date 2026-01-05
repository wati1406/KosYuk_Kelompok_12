<?php
session_start();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Pastikan array faq_data ada
    if(!isset($_SESSION['faq_data'])) {
        $_SESSION['faq_data'] = [];
    }
    
    if($action === 'delete') {
        // Handle delete FAQ
        $index = $_POST['index'] ?? null;
        
        if($index !== null && isset($_SESSION['faq_data'][$index])) {
            // Hapus dari array
            array_splice($_SESSION['faq_data'], $index, 1);
            
            echo json_encode(['success' => true, 'message' => 'FAQ berhasil dihapus']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'FAQ tidak ditemukan']);
            exit;
        }
        
    } else {
        // Handle save/update FAQ
        $index = $_POST['index'] ?? '';
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? '');
        
        // Validasi input
        if(empty($question) || empty($answer) || empty($category)) {
            echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
            exit;
        }
        
        $faqData = [
            'id' => uniqid(),
            'question' => $question,
            'answer' => $answer,
            'category' => $category,
            'created_date' => date('Y-m-d H:i:s')
        ];
        
        if($index === '') {
            // Tambah FAQ baru
            $_SESSION['faq_data'][] = $faqData;
            echo json_encode(['success' => true, 'message' => 'FAQ berhasil ditambahkan']);
        } else {
            // Update FAQ yang ada
            if(isset($_SESSION['faq_data'][$index])) {
                // Preserve original ID and creation date
                $faqData['id'] = $_SESSION['faq_data'][$index]['id'];
                $faqData['created_date'] = $_SESSION['faq_data'][$index]['created_date'];
                $faqData['modified_date'] = date('Y-m-d H:i:s');
                
                $_SESSION['faq_data'][$index] = $faqData;
                echo json_encode(['success' => true, 'message' => 'FAQ berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'FAQ tidak ditemukan']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>