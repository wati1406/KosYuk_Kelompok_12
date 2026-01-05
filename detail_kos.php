<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
$id_kos = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Inisialisasi session untuk favorit jika belum ada
if (!isset($_SESSION['favorit_kos'])) {
    $_SESSION['favorit_kos'] = [];
}

// Handle favorit action
if (isset($_POST['toggle_favorit'])) {
    $kos_id = intval($_POST['kos_id']);
    
    // Cek apakah kos sudah ada di favorit
    $key = array_search($kos_id, $_SESSION['favorit_kos']);
    
    if ($key !== false) {
        // Hapus dari favorit
        unset($_SESSION['favorit_kos'][$key]);
        $_SESSION['favorit_kos'] = array_values($_SESSION['favorit_kos']);
        $favorit_message = "Kos dihapus dari favorit!";
    } else {
        // Tambahkan ke favorit
        $_SESSION['favorit_kos'][] = $kos_id;
        $favorit_message = "Kos ditambahkan ke favorit!";
    }
    
    // Redirect untuk menghindari resubmit
    header("Location: detail_kos.php?id=" . $id_kos);
    exit;
}

// INCLUDE DATA KOS YANG KONSISTEN
include 'data_kos.php';

// Gunakan fungsi get_kos_by_id untuk mengambil data kos
$kos = get_kos_by_id($id_kos);

// Cek apakah kos ada
if ($kos === null) {
    header("Location: semua_kos.php");
    exit;
}

// Mapping fasilitas dari kode ke label
$fasilitas_mapping = [
    'wifi' => 'WiFi Cepat',
    'ac' => 'AC',
    'km_dalam' => 'Kamar Mandi Dalam',
    'laundry' => 'Laundry',
    'parkir_luas' => 'Parkir Luas',
    'dapur' => 'Dapur Bersama',
    'keamanan_24_jam' => 'Keamanan 24 Jam',
    'air_panas' => 'Air Panas',
    'kulkas' => 'Kulkas'
];

// Ambil fasilitas dari data kos
$fasilitas = [];
if (isset($kos['fasilitas']) && is_array($kos['fasilitas'])) {
    foreach ($kos['fasilitas'] as $fas) {
        if (isset($fasilitas_mapping[$fas])) {
            $fasilitas[] = $fasilitas_mapping[$fas];
        } else {
            $fasilitas[] = ucwords(str_replace('_', ' ', $fas));
        }
    }
} else {
    // Default fasilitas jika tidak ada
    $fasilitas = ['WiFi Cepat', 'AC', 'Kamar Mandi Dalam', 'Laundry', 'Dapur Bersama', 'Parkir Luas', 'Keamanan 24 Jam'];
}

// Data gambar - 4 gambar
$gambar_kos = [
    'assets/images/kos' . (($id_kos % 4) + 1) . '.jpg',
    'assets/images/kos' . ((($id_kos + 1) % 4) + 1) . '.jpg',
    'assets/images/kos' . ((($id_kos + 2) % 4) + 1) . '.jpg',
    'assets/images/kos' . ((($id_kos + 3) % 4) + 1) . '.jpg'
];

// Kamar tersedia
$kamar_tersedia = $kos['sisa_kamar'];

// Cek apakah kos sudah disimpan di favorit
$is_saved = in_array($id_kos, $_SESSION['favorit_kos']);

// Data review dummy
$all_reviews = [];
$total_rating = 0;

// Generate 10 review dengan data lengkap
for ($i = 1; $i <= 10; $i++) {
    $review_rating = rand(3, 5);
    $total_rating += $review_rating;
    
    $review = [
        'id' => $i,
        'user_name' => ['Rizki Pratama', 'Siti Aisyah', 'Andi Wijaya', 'Dewi Lestari', 'Bambang Sutrisno', 'Maya Indah', 'Fajar Nugroho', 'Linda Sari', 'Hendra Kurniawan', 'Rina Melati'][$i-1],
        'user_avatar' => 'assets/images/avatar' . (($i % 4) + 1) . '.png',
        'rating' => $review_rating,
        'date' => date('Y-m-d', strtotime('-' . rand(0, 60) . ' days')),
        'comment' => [
            'Kos yang sangat nyaman dan bersih. Pemiliknya ramah dan fasilitas lengkap. Air panas berfungsi dengan baik dan WiFi stabil.',
            'Lokasi strategis dekat kampus. Harga sesuai dengan fasilitas yang diberikan. Lingkungan aman dan nyaman untuk belajar.',
            'Sudah 2 tahun tinggal disini. Sangat recommended untuk mahasiswa. Kamar luas dan terawat dengan baik.',
            'Bagus tapi WiFi kadang lemot. Mungkin perlu ditambah bandwidth. Selain itu semua fasilitas berfungsi dengan baik.',
            'Kamar luas dan bersih. Air panas berfungsi dengan baik. Penjaga kos sangat perhatian dan ramah.',
            'Pelayanan pemilik sangat baik. Lingkungan juga aman dan nyaman. Dekat dengan warung makan dan minimarket.',
            'Harga cukup terjangkau untuk fasilitas yang diberikan. Lokasi dekat dengan kampus dan tempat makan.',
            'Dekat dengan warung makan dan minimarket, sangat praktis. Kamar mandi dalam bersih dan air lancar.',
            'Ada masalah kebocoran tapi langsung diperbaiki oleh pemilik. Responsif terhadap keluhan penghuni.',
            'Rekomendasi untuk mahasiswa yang mencari kos nyaman dan terjangkau. Lingkungan tenang cocok untuk belajar.'
        ][$i-1],
        'is_pemilik' => false,
        'replies' => []
    ];
    
    // 30% kemungkinan ada balasan dari pemilik
    if (rand(1, 10) <= 3) {
        $review['replies'] = [
            [
                'id' => $i * 10,
                'user_name' => $kos['pemilik_nama'],
                'user_avatar' => 'assets/images/owner.png',
                'date' => date('Y-m-d', strtotime($review['date'] . ' +' . rand(1, 3) . ' days')),
                'comment' => [
                    'Terima kasih atas reviewnya! Senang bisa membuat Anda nyaman. Kami selalu berusaha memberikan pelayanan terbaik.',
                    'Terima kasih feedbacknya. WiFi sudah kami upgrade bulan depan untuk kenyamanan bersama.',
                    'Terima kasih Andi atas kepercayaannya selama ini! Kami senang bisa melayani Anda.',
                    'Siap, terima kasih masukannya. Akan kami perbaiki segera untuk kenyamanan bersama.',
                    'Alhamdulillah, semoga selalu nyaman tinggal disini. Kami terus berusaha memberikan yang terbaik.',
                    'Terima kasih Maya, kami selalu berusaha memberikan yang terbaik untuk penghuni kos.',
                    'Senang bisa melayani dengan baik. Semoga selalu betah tinggal di kos kami.',
                    'Iya, lokasi memang strategis untuk kebutuhan sehari-hari. Senang bisa membantu.',
                    'Maaf atas ketidaknyamanannya, semoga sudah lebih baik sekarang. Kami selalu terbuka untuk masukan.',
                    'Terima kasih rekomendasinya Rina! Kami berharap bisa terus memberikan pelayanan terbaik.'
                ][$i-1],
                'is_pemilik' => true
            ]
        ];
    }
    
    $all_reviews[] = $review;
}

// Hitung rating rata-rata
$average_rating = $total_rating / count($all_reviews);

// Update rating kos dengan rata-rata
$kos['rating'] = number_format($average_rating, 1);
$kos['total_review'] = count($all_reviews);

// Urutkan review berdasarkan tanggal terbaru
usort($all_reviews, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Ambil 5 review terbaru untuk preview
$preview_reviews = array_slice($all_reviews, 0, 5);

// Handle submit review
$review_success = false;
$reply_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_review'])) {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        $user_name = trim($_POST['user_name']);
        
        if ($rating >= 1 && $rating <= 5 && !empty($comment) && !empty($user_name)) {
            // Tambahkan review baru
            $new_review = [
                'id' => count($all_reviews) + 1,
                'user_name' => htmlspecialchars($user_name),
                'user_avatar' => 'assets/images/avatar' . (rand(1, 4)) . '.png',
                'rating' => $rating,
                'date' => date('Y-m-d'),
                'comment' => htmlspecialchars($comment),
                'is_pemilik' => false,
                'replies' => []
            ];
            
            array_unshift($all_reviews, $new_review);
            $preview_reviews = array_slice($all_reviews, 0, 5);
            $review_success = "Review berhasil dikirim!";
            
            // Update rating rata-rata
            $total_rating = 0;
            foreach ($all_reviews as $review) {
                $total_rating += $review['rating'];
            }
            $kos['rating'] = number_format($total_rating / count($all_reviews), 1);
            $kos['total_review'] = count($all_reviews);
        }
    }
    
    // Handle reply to review
    if (isset($_POST['submit_reply'])) {
        $review_id = intval($_POST['review_id']);
        $reply_comment = trim($_POST['reply_comment']);
        
        if (!empty($reply_comment)) {
            foreach ($all_reviews as &$review) {
                if ($review['id'] == $review_id) {
                    $new_reply = [
                        'id' => count($review['replies']) + 1,
                        'user_name' => $_SESSION['user_name'] ?? 'Pemilik Kos',
                        'user_avatar' => 'assets/images/owner.png',
                        'date' => date('Y-m-d'),
                        'comment' => htmlspecialchars($reply_comment),
                        'is_pemilik' => true
                    ];
                    
                    $review['replies'][] = $new_reply;
                    $reply_success = "Balasan berhasil dikirim!";
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kos['nama']); ?> - Detail Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #0A2C4F;
            --secondary-color: #1A5F9E;
            --accent-color: #FF9800;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-gray: #f8f9fa;
            --dark-gray: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        .kos-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            color: var(--accent-color);
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Kos Images */
        .kos-main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding-bottom: 5px;
        }
        
        .thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        /* Kos Info */
        .kos-info-header {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .kos-name {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .kos-location {
            color: var(--dark-gray);
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kos-price-rating {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .kos-price {
            color: var(--primary-color);
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .kos-price small {
            display: block;
            color: var(--dark-gray);
            font-size: 0.9rem;
            font-weight: normal;
        }
        
        .kos-rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .rating-stars {
            color: var(--accent-color);
            font-size: 1.2rem;
        }
        
        .rating-value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .review-count {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        
        /* Fasilitas */
        .fasilitas-badge {
            background: var(--primary-color) !important;
            color: white !important;
            padding: 8px 15px !important;
            margin: 0 8px 8px 0 !important;
            border-radius: 20px !important;
            font-size: 0.9rem !important;
            border: none !important;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
            font-size: 1.3rem;
        }
        
        /* Buttons */
        .whatsapp-btn {
            background-color: #25D366;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .whatsapp-btn:hover {
            background-color: #128C7E;
            color: white;
            transform: translateY(-2px);
        }
        
        .booking-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .booking-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .booking-btn:disabled {
            background-color: var(--dark-gray);
            cursor: not-allowed;
        }
        
        .save-btn {
            background-color: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .save-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .save-btn.saved {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
        
        /* Map */
        #map {
            height: 300px;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        /* Review Section - SAMPING KANAN */
        .review-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .review-card {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid var(--light-gray);
        }
        
        .review-info h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .review-date {
            color: var(--dark-gray);
            font-size: 0.75rem;
            margin-top: 2px;
        }
        
        .review-rating {
            color: var(--accent-color);
            margin-left: auto;
            font-size: 0.8rem;
        }
        
        .review-comment {
            color: #555;
            line-height: 1.5;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }
        
        .reply-card {
            margin-left: 40px;
            background-color: white;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            border-left: 3px solid var(--primary-color);
            font-size: 0.8rem;
        }
        
        .reply-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .reply-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }
        
        .reply-info h6 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .reply-comment {
            color: #555;
            line-height: 1.4;
        }
        
        .add-review-form {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        
        .rating-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .rating-input label {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .star-rating {
            display: flex;
            gap: 3px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            font-size: 1.2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .star-rating input:checked ~ label,
        .star-rating input:hover ~ label,
        .star-rating label:hover ~ label {
            color: var(--accent-color);
        }
        
        .review-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }
        
        .review-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(10, 44, 79, 0.1);
        }
        
        .submit-review-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            width: 100%;
            justify-content: center;
        }
        
        .submit-review-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Share Section - SAMPING KANAN */
        .share-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .share-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .share-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .share-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.85rem;
            justify-content: center;
        }
        
        .share-facebook {
            background: #1877F2;
            color: white;
        }
        
        .share-facebook:hover {
            background: #166FE5;
            color: white;
            transform: translateY(-2px);
        }
        
        .share-twitter {
            background: #1DA1F2;
            color: white;
        }
        
        .share-twitter:hover {
            background: #1A91DA;
            color: white;
            transform: translateY(-2px);
        }
        
        .share-whatsapp {
            background: #25D366;
            color: white;
        }
        
        .share-whatsapp:hover {
            background: #128C7E;
            color: white;
            transform: translateY(-2px);
        }
        
        .share-instagram {
            background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
            color: white;
        }
        
        .share-instagram:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-2px);
        }
        
        .share-tiktok {
            background: #000000;
            color: white;
        }
        
        .share-tiktok:hover {
            background: #333333;
            color: white;
            transform: translateY(-2px);
        }
        
        .share-copy {
            background: var(--primary-color);
            color: white;
            grid-column: span 2;
        }
        
        .share-copy:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .kos-name {
                font-size: 1.5rem;
            }
            
            .kos-main-image {
                height: 300px;
            }
            
            .thumbnail {
                width: 80px;
                height: 80px;
            }
            
            .share-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .share-copy {
                grid-column: span 3;
            }
        }
        
        @media (max-width: 768px) {
            .kos-price-rating {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .share-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .share-copy {
                grid-column: span 2;
            }
            
            .reply-card {
                margin-left: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .kos-main-image {
                height: 250px;
            }
            
            .thumbnail {
                width: 60px;
                height: 60px;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .review-rating {
                margin-left: 0;
                margin-top: 5px;
            }
            
            .reply-card {
                margin-left: 10px;
            }
            
            .share-buttons {
                grid-template-columns: 1fr;
            }
            
            .share-copy {
                grid-column: span 1;
            }
        }
        
        /* Alert Styling */
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Form Elements */
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(10, 44, 79, 0.1);
        }
        
        /* Info Icons */
        .info-icon {
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }
        
        /* Scrollbar untuk review */
        .review-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .review-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .review-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .review-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .review-container::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="kos-header">
        <div class="main-container">
            <a href="semua_kos.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kos
            </a>
        </div>
    </header>

    <div class="main-container">
        <!-- Alerts -->
        <?php if(isset($favorit_message)): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $favorit_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if($review_success): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $review_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if($reply_success): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $reply_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if($kamar_tersedia == 0): ?>
        <div class="alert alert-warning alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Maaf!</strong> Saat ini tidak ada kamar yang tersedia.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php else: ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Tersedia!</strong> <?php echo $kamar_tersedia; ?> kamar kosong.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <!-- Gambar Utama -->
                <img src="<?php echo $gambar_kos[0]; ?>" 
                     class="kos-main-image" 
                     alt="Gambar Kos"
                     onerror="this.src='https://via.placeholder.com/800x400/0A2C4F/FFFFFF?text=KosYuk'">

                <!-- Thumbnail Gambar -->
                <div class="thumbnail-container">
                    <?php foreach($gambar_kos as $index => $gambar): ?>
                    <img src="<?php echo $gambar; ?>" 
                         class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                         onclick="changeImage(this, '<?php echo $gambar; ?>')"
                         alt="Thumbnail <?php echo $index + 1; ?>"
                         onerror="this.src='https://via.placeholder.com/100x100/0A2C4F/FFFFFF?text=Kos'">
                    <?php endforeach; ?>
                </div>

                <!-- Info Kos -->
                <div class="kos-info-header">
                    <h1 class="kos-name">Kos <?php echo htmlspecialchars($kos['nama']); ?></h1>
                    <p class="kos-location">
                        <i class="fas fa-map-marker-alt me-2"></i> 
                        <?php echo htmlspecialchars($kos['alamat']); ?>
                    </p>
                    
                    <div class="kos-price-rating">
                        <div class="kos-rating">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($kos['rating'])): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i == ceil($kos['rating']) && fmod($kos['rating'], 1) >= 0.5): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value ms-2"><?php echo $kos['rating']; ?></span>
                            <span class="review-count ms-1">(<?php echo $kos['total_review']; ?> ulasan)</span>
                        </div>
                        <div class="kos-price">
                            Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?>/tahun
                            <small class="d-block">
                                Rp <?php echo number_format($kos['harga'] / 12, 0, ',', '.'); ?>/bulan
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Fasilitas</h3>
                        <div class="d-flex flex-wrap">
                            <?php foreach($fasilitas as $fas): ?>
                            <span class="badge fasilitas-badge p-2 mb-2">
                                <i class="fas fa-check me-1"></i> <?php echo htmlspecialchars($fas); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Deskripsi Kos</h3>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($kos['deskripsi'])); ?></p>
                    </div>
                </div>

                <!-- Lokasi & Maps -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt me-2"></i> Lokasi & Peta
                        </h3>
                        
                        <div id="map"></div>
                        
                        <div class="location-info mt-3">
                            <h5><i class="fas fa-info-circle me-2"></i> Informasi Lokasi</h5>
                            <p class="mb-2"><strong>Alamat Lengkap:</strong> <?php echo htmlspecialchars($kos['alamat_lengkap']); ?></p>
                            <p class="mb-2"><strong>Akses:</strong> Dekat dengan kampus, pasar, dan pusat perbelanjaan</p>
                            <p class="mb-0"><strong>Transportasi:</strong> Akses mudah ke angkutan umum</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (Sidebar) -->
            <div class="col-lg-4">
                <!-- Info Detail -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Informasi Detail</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-home info-icon"></i> Tipe Kamar</span>
                                <strong><?php echo htmlspecialchars($kos['tipe']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-<?php echo $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female'; ?> info-icon"></i> Jenis Kos</span>
                                <strong>Kos <?php echo ucfirst(htmlspecialchars($kos['jenis_kelamin'])); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-expand-arrows-alt info-icon"></i> Ukuran Kamar</span>
                                <strong><?php echo htmlspecialchars($kos['ukuran']); ?> m²</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-user-friends info-icon"></i> Kapasitas</span>
                                <strong><?php echo htmlspecialchars($kos['kapasitas']); ?> orang/kamar</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-door-open info-icon"></i> Kamar Tersedia</span>
                                <strong><?php echo $kos['sisa_kamar']; ?> kamar</strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Kontak Pemilik -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Kontak Pemilik</h3>
                        <div class="mb-3">
                            <p class="mb-1"><strong>Nama:</strong></p>
                            <p class="mb-3"><?php echo htmlspecialchars($kos['pemilik_nama']); ?></p>
                            
                            <p class="mb-1"><strong>Telepon:</strong></p>
                            <p class="mb-3"><?php echo htmlspecialchars($kos['pemilik_telepon']); ?></p>
                        </div>
                        
                        <button class="btn w-100 whatsapp-btn mb-2" 
                                onclick="whatsappOwner('<?php echo htmlspecialchars($kos['pemilik_telepon']); ?>')">
                            <i class="fab fa-whatsapp me-2"></i> Hubungi via WhatsApp
                        </button>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if($kamar_tersedia > 0): ?>
                            <button class="btn booking-btn" 
                                    onclick="window.location.href='booking.php?id=<?php echo $id_kos; ?>'">
                                <i class="fas fa-calendar-check me-2"></i> Booking Sekarang
                            </button>
                            <?php else: ?>
                            <button class="btn booking-btn" disabled>
                                <i class="fas fa-calendar-times me-2"></i> Tidak Tersedia
                            </button>
                            <?php endif; ?>
                            
                            <!-- Tombol Favorit -->
                            <form method="POST" action="" style="display: inline; width: 100%;">
                                <input type="hidden" name="kos_id" value="<?php echo $id_kos; ?>">
                                <button type="submit" name="toggle_favorit" 
                                        class="btn save-btn w-100 <?php echo $is_saved ? 'saved' : ''; ?>">
                                    <i class="fas <?php echo $is_saved ? 'fa-heart' : 'fa-heart'; ?> me-2"></i>
                                    <?php echo $is_saved ? 'Disimpan' : 'Simpan ke Favorit'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Review Section - DIPINDAH KE SAMPING KANAN -->
                <div class="review-section">
                    <h3 class="card-title mb-3">
                        <i class="fas fa-star me-2"></i> Ulasan & Penilaian
                    </h3>

                    <!-- Rating Summary -->
                    <div class="rating-summary mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="rating-stars me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($kos['rating'])): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i == ceil($kos['rating']) && fmod($kos['rating'], 1) >= 0.5): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value"><?php echo $kos['rating']; ?></span>
                            <span class="review-count ms-2">(<?php echo $kos['total_review']; ?> ulasan)</span>
                        </div>
                    </div>

                    <!-- Recent Reviews with Scroll -->
                    <div class="review-container mb-3">
                        <?php if(count($preview_reviews) > 0): ?>
                            <?php foreach($preview_reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <img src="<?php echo $review['user_avatar']; ?>" 
                                         alt="<?php echo htmlspecialchars($review['user_name']); ?>" 
                                         class="review-avatar"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($review['user_name']); ?>&background=0A2C4F&color=fff'">
                                    <div class="review-info">
                                        <h5><?php echo htmlspecialchars($review['user_name']); ?></h5>
                                        <div class="review-date">
                                            <?php echo date('d M Y', strtotime($review['date'])); ?>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="review-comment">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </div>
                                
                                <?php if(count($review['replies']) > 0): ?>
                                    <?php foreach($review['replies'] as $reply): ?>
                                    <div class="reply-card">
                                        <div class="reply-header">
                                            <img src="<?php echo $reply['user_avatar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($reply['user_name']); ?>" 
                                                 class="reply-avatar"
                                                 onerror="this.src='assets/images/owner.png'">
                                            <div class="reply-info">
                                                <h6><?php echo htmlspecialchars($reply['user_name']); ?></h6>
                                                <div class="review-date">
                                                    <?php echo date('d M Y', strtotime($reply['date'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reply-comment">
                                            <?php echo nl2br(htmlspecialchars($reply['comment'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-comment-slash fa-2x mb-2 text-muted"></i>
                                <p class="text-muted mb-0">Belum ada ulasan</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Add Review Form -->
                    <div class="add-review-form">
                        <h5 class="mb-2" style="font-size: 0.9rem;">Tambah Ulasan</h5>
                        <form method="POST" action="" id="reviewForm">
                            <div class="rating-input">
                                <label>Rating:</label>
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5">
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="user_name" 
                                       value="<?php echo $_SESSION['user_name'] ?? ''; ?>"
                                       placeholder="Nama Anda" required>
                            </div>
                            
                            <div class="mb-2">
                                <textarea class="form-control review-textarea" name="comment" 
                                          placeholder="Bagikan pengalaman Anda..." required></textarea>
                            </div>
                            
                            <button type="submit" name="submit_review" class="submit-review-btn">
                                <i class="fas fa-paper-plane"></i> Kirim Ulasan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Share Section - DIPINDAH KE SAMPING KANAN -->
                <div class="share-section">
                    <h4 class="share-title">
                        <i class="fas fa-share-alt me-2"></i> Bagikan Kos Ini
                    </h4>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="share-btn share-facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode('Cek kos ini: ' . $kos['nama']); ?>" 
                           target="_blank" class="share-btn share-twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode('Cek kos ini: Kos ' . $kos['nama'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" class="share-btn share-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.instagram.com/" 
                           target="_blank" class="share-btn share-instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.tiktok.com/" 
                           target="_blank" class="share-btn share-tiktok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <button class="share-btn share-copy" onclick="copyLink()">
                            <i class="fas fa-copy"></i> Salin Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Fungsi ganti gambar utama
        function changeImage(element, src) {
            document.querySelectorAll('.thumbnail').forEach(function(thumb) {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
            document.querySelector('.kos-main-image').src = src;
        }
        
        // Fungsi WhatsApp
        function whatsappOwner(phone) {
            var kosName = "Kos <?php echo htmlspecialchars($kos['nama'], ENT_QUOTES); ?>";
            var message = "Halo, saya tertarik dengan kos " + kosName + ". Bisakah saya mendapatkan informasi lebih lanjut?";
            var url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);
            window.open(url, '_blank');
        }
        
        // Initialize Map
        function initMap() {
            const lat = <?php echo isset($kos['lat']) ? $kos['lat'] : -7.423; ?>;
            const lng = <?php echo isset($kos['lng']) ? $kos['lng'] : 109.236; ?>;
            const kosName = "Kos <?php echo htmlspecialchars($kos['nama'], ENT_QUOTES); ?>";
            
            const map = L.map('map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            const marker = L.marker([lat, lng]).addTo(map);
            
            marker.bindPopup(`
                <div style="text-align: center;">
                    <strong>${kosName}</strong><br>
                    <?php echo htmlspecialchars($kos['alamat']); ?>
                </div>
            `).openPopup();
        }
        
        // Copy link to clipboard
        function copyLink() {
            const link = window.location.href;
            navigator.clipboard.writeText(link).then(() => {
                alert('Link berhasil disalin!');
            }).catch(err => {
                console.error('Gagal menyalin link:', err);
            });
        }
        
        // Star Rating Interaction
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            initMap();
            
            // Star rating hover effect
            const stars = document.querySelectorAll('.star-rating label');
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('for').replace('star', '');
                    highlightStars(rating);
                });
                
                star.addEventListener('mouseout', function() {
                    const checkedStar = document.querySelector('.star-rating input:checked');
                    if (checkedStar) {
                        const rating = checkedStar.value;
                        highlightStars(rating);
                    } else {
                        resetStars();
                    }
                });
                
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('for').replace('star', '');
                    highlightStars(rating);
                });
            });
            
            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.innerHTML = '<i class="fas fa-star"></i>';
                        star.style.color = '#FF9800';
                    } else {
                        star.innerHTML = '<i class="far fa-star"></i>';
                        star.style.color = '#ddd';
                    }
                });
            }
            
            function resetStars() {
                stars.forEach(star => {
                    star.innerHTML = '<i class="far fa-star"></i>';
                    star.style.color = '#ddd';
                });
            }
            
            // Form validation
            const reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    const rating = document.querySelector('input[name="rating"]:checked');
                    const comment = document.querySelector('textarea[name="comment"]').value;
                    const userName = document.querySelector('input[name="user_name"]').value;
                    
                    if (!rating) {
                        e.preventDefault();
                        alert('Silakan berikan rating dengan mengklik bintang.');
                        return false;
                    }
                    
                    if (comment.trim().length < 10) {
                        e.preventDefault();
                        alert('Ulasan harus minimal 10 karakter.');
                        return false;
                    }
                    
                    if (userName.trim() === '') {
                        e.preventDefault();
                        alert('Silakan masukkan nama Anda.');
                        return false;
                    }
                    
                    // Show loading
                    const submitBtn = reviewForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
                    submitBtn.disabled = true;
                    
                    // Simulate processing
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 2000);
                    
                    return true;
                });
            }
            
            // Auto-scroll untuk review section
            const reviewContainer = document.querySelector('.review-container');
            if (reviewContainer) {
                reviewContainer.scrollTop = 0;
            }
            
            // Smooth scroll untuk anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>