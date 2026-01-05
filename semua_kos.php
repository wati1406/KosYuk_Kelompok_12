<?php
session_start();

// INCLUDE DATA KOS YANG KONSISTEN
include 'data_kos.php';

// Cek login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Jika admin dalam preview mode
$is_admin_preview = isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true;

// Cek role
if ($_SESSION['user_role'] !== 'pencari_kos' && !$is_admin_preview) {
    header("Location: login.php");
    exit;
}

function get_filtered_kos($filters = []) {
    $all_kos = get_all_kos();
    $filtered = [];
    
    foreach ($all_kos as $kos) {
        $match = true;
        
        // Filter berdasarkan keyword
        if (!empty($filters['keyword'])) {
            $keyword = strtolower($filters['keyword']);
            $nama = strtolower($kos['nama']);
            $alamat = strtolower($kos['alamat']);
            
            if (strpos($nama, $keyword) === false && strpos($alamat, $keyword) === false) {
                $match = false;
            }
        }
        
        // Filter berdasarkan harga
        if (!empty($filters['harga'])) {
            $harga_filter = (int)$filters['harga'];
            $harga = $kos['harga'];
            
            switch ($harga_filter) {
                case 5000000:
                    if ($harga >= 5000000) $match = false;
                    break;
                case 10000000:
                    if ($harga < 5000000 || $harga > 10000000) $match = false;
                    break;
                case 15000000:
                    if ($harga <= 10000000 || $harga > 15000000) $match = false;
                    break;
                case 20000000:
                    if ($harga <= 15000000) $match = false;
                    break;
            }
        }
        
         // Filter berdasarkan fasilitas (VERSI AMAN)
            if (!empty($filters['fasilitas'])) {
                if (!isset($kos['fasilitas']) || !is_array($kos['fasilitas'])) {
                    $match = false;
                } elseif (!in_array($filters['fasilitas'], $kos['fasilitas'])) {
                    $match = false;
                }
            }
            
        
        // Filter berdasarkan jenis
        if (!empty($filters['jenis'])) {
            if ($kos['jenis_kelamin'] != $filters['jenis']) {
                $match = false;
            }
        }
        
        // Filter berdasarkan jarak
        if (!empty($filters['jarak'])) {
            $jarak_filter = (int)$filters['jarak'];
            $jarak = $kos['jarak_ke_kampus'];
            
            switch ($jarak_filter) {
                case 500:
                    if ($jarak > 500) $match = false;
                    break;
                case 1000:
                    if ($jarak <= 500 || $jarak > 1000) $match = false;
                    break;
                case 5000:
                    if ($jarak <= 1000 || $jarak > 5000) $match = false;
                    break;
            }
        }
        
        if ($match) {
            $filtered[] = $kos;
        }
    }
    
    return $filtered;
}

// Proses Filter dan Pencarian
$filtered_kos = [];

if (!empty($_GET)) {
    // Gunakan fungsi filter dari data_kos.php
    $filtered_kos = get_filtered_kos([
        'keyword' => $_GET['keyword'] ?? '',
        'harga' => $_GET['harga'] ?? '',
        'fasilitas' => $_GET['fasilitas'] ?? '',
        'jenis' => $_GET['jenis'] ?? '',
        'jarak' => $_GET['jarak'] ?? ''
    ]);
} else {
    // Jika tidak ada filter, tampilkan semua kos
    $all_kos = get_all_kos();
    $filtered_kos = array_values($all_kos);
}

// Konfigurasi pagination
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$total_items = count($filtered_kos);
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

// Hitung offset untuk data
$offset = ($current_page - 1) * $items_per_page;
$kos_for_page = array_slice($filtered_kos, $offset, $items_per_page);

// Fungsi untuk membuat URL dengan parameter
function buildUrl($params = []) {
    $query_params = $_GET;
    foreach ($params as $key => $value) {
        $query_params[$key] = $value;
    }
    return '?' . http_build_query($query_params);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Kos - KosYuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            color: #0A2C4F;
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
            font-weight: 700;
        }
        
        /* FILTER SECTION - DI TENGAH */
        .filter-section-wrapper {
            display: flex;
            justify-content: center;
            margin: 30px 0 40px 0;
            width: 100%;
        }
        
        .filter-container {
            background: linear-gradient(135deg, rgba(10, 44, 79, 0.95) 0%, rgba(26, 95, 158, 0.95) 100%);
            padding: 25px 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 1300px;
            box-shadow: 0 10px 30px rgba(10, 44, 79, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }
        
        .filter-form input,
        .filter-form select {
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid transparent;
            background: white;
            font-size: 14px;
            min-width: 180px;
            flex: 1;
            max-width: 220px;
            transition: all 0.3s;
        }
        
        .filter-form input:focus,
        .filter-form select:focus {
            border-color: #0A2C4F;
            box-shadow: 0 0 0 3px rgba(10, 44, 79, 0.2);
        }
        
        .filter-form button {
    padding: 12px 25px;
    background:  #0A2C4F; /* BIRU MUDA */
    border: none;
    color: white; /* TEKS PUTIH */
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.filter-form button:hover {
    background: #1A5F9E; /* BIRU LEBIH TUA */
    transform: translateY(-2px);
}
        
        /* DAFTAR SECTION */
        .daftar-section {
            padding: 20px 0;
            margin: 0 auto;
            max-width: 1200px;
        }
        
        .daftar-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .daftar-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
        }
        
        .daftar-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            border-color: #0A2C4F;
        }
        
        .daftar-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .daftar-info {
            padding: 20px;
        }
        
        .jenis-kos-info {
            margin-bottom: 10px;
        }
        
        .jenis-text {
            background: rgba(10, 44, 79, 0.1);
            color: #0A2C4F;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        
        .daftar-info h3 {
            font-size: 17px;
            color: #0A2C4F;
            margin-bottom: 10px;
            font-weight: 700;
            line-height: 1.3;
        }
        
        .tipe, .lokasi-daftar {
            color: #666;
            font-size: 13px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .card-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .harga-daftar {
            color: #0A2C4F;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        /* PAGINATION - DI TENGAH */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            margin-bottom: 30px;
        }
        
        .pagination-container {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
        }
        
        .pagination-links {
            display: flex;
            gap: 8px;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        
        .pagination-links a,
        .pagination-links span {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            min-width: 40px;
            text-align: center;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .pagination-links a {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }
        
        .pagination-links a:hover {
            background: #0A2C4F;
            color: white;
            border-color: #0A2C4F;
        }
        
        .pagination-links .current {
            background: #ffc107;
            color: #0A2C4F;
            font-weight: 600;
            border: 1px solid #ffc107;
        }
        
        .pagination-links .disabled {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            cursor: not-allowed;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 40px 0;
        }
        
        .no-results i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 22px;
        }
        
        .no-results p {
            color: #888;
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .no-results .btn {
            padding: 10px 25px;
            background: #0A2C4F;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .no-results .btn:hover {
            background: #083162;
            transform: translateY(-2px);
        }
        
        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .daftar-box {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .filter-container {
                max-width: 1100px;
            }
        }
        
        @media (max-width: 992px) {
            .filter-form {
                gap: 8px;
            }
            
            .filter-form input,
            .filter-form select {
                min-width: calc(50% - 8px);
                max-width: calc(50% - 8px);
            }
            
            .filter-form button {
                min-width: 100%;
                max-width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .daftar-box {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .container {
                padding: 15px;
            }
            
            .page-title {
                font-size: 26px;
            }
            
            .filter-container {
                padding: 20px;
            }
            
            .filter-form input,
            .filter-form select {
                min-width: 100%;
                max-width: 100%;
            }
            
            .pagination-container {
                padding: 10px 15px;
            }
            
            .pagination-links a,
            .pagination-links span {
                padding: 6px 10px;
                min-width: 35px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .pagination-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination-links a,
            .pagination-links span {
                padding: 5px 8px;
                min-width: 30px;
                font-size: 13px;
            }
        }

        .back-container {
            margin-bottom: 20px;
        }
 .back-btn {
            display: block;
            padding: 15px;
            background: #0A2C4F;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: left;
            gap: 10px;
            width: 100%;
            text-align: center;
            font-weight: 600;
        }




    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Semua Kos Tersedia</h1>

        <div class="back-container">
            <a href="dashboard_pencarikos.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
       
        <!-- FILTER SECTION - DI TENGAH -->
        <div class="filter-section-wrapper">
            <div class="filter-container">
                <form method="GET" class="filter-form">
                    <input type="text" name="keyword" placeholder="Cari kos berdasarkan nama / lokasi"
                           value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                    <select name="harga">
                        <option value="">Filter Harga per Tahun</option>
                        <option value="5000000" <?php echo (isset($_GET['harga']) && $_GET['harga'] == '5000000') ? 'selected' : ''; ?>>Dibawah 5 juta</option>
                        <option value="10000000" <?php echo (isset($_GET['harga']) && $_GET['harga'] == '10000000') ? 'selected' : ''; ?>>5 - 10 juta</option>
                        <option value="15000000" <?php echo (isset($_GET['harga']) && $_GET['harga'] == '15000000') ? 'selected' : ''; ?>>10 - 15 juta</option>
                        <option value="20000000" <?php echo (isset($_GET['harga']) && $_GET['harga'] == '20000000') ? 'selected' : ''; ?>>Di atas 15 juta</option>
                    </select>
                    <select name="fasilitas">
                        <option value="">Fasilitas</option>
                        <option value="wifi" <?php echo (isset($_GET['fasilitas']) && $_GET['fasilitas'] == 'wifi') ? 'selected' : ''; ?>>WiFi</option>
                        <option value="ac" <?php echo (isset($_GET['fasilitas']) && $_GET['fasilitas'] == 'ac') ? 'selected' : ''; ?>>AC</option>
                        <option value="km_dalam" <?php echo (isset($_GET['fasilitas']) && $_GET['fasilitas'] == 'km_dalam') ? 'selected' : ''; ?>>Kamar Mandi Dalam</option>
                        <option value="laundry" <?php echo (isset($_GET['fasilitas']) && $_GET['fasilitas'] == 'laundry') ? 'selected' : ''; ?>>Laundry</option>
                        <option value="dapur" <?php echo (isset($_GET['fasilitas']) && $_GET['fasilitas'] == 'dapur') ? 'selected' : ''; ?>>Dapur Bersama</option>
                    </select>
                    <select name="jenis">
                        <option value="">Jenis Kos</option>
                        <option value="putra" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'putra') ? 'selected' : ''; ?>>Putra</option>
                        <option value="putri" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'putri') ? 'selected' : ''; ?>>Putri</option>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> Cari Kos</button>
                </form>
            </div>
        </div>
       
        <!-- DAFTAR SECTION -->
        <section class="daftar-section">
            <?php if ($total_items > 0): ?>
                <div class="daftar-box">
                    <?php foreach ($kos_for_page as $kos): ?>
                    <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="daftar-card">
                        <img src="assets/images/kos<?php echo (($kos['id'] % 4) + 1); ?>.jpg"
                             alt="<?php echo htmlspecialchars($kos['nama']); ?>"
                             onerror="this.src='https://via.placeholder.com/400x300/0A2C4F/FFFFFF?text=KosYuk'">
                        <div class="daftar-info">
                            <div class="jenis-kos-info">
                                <span class="jenis-text">
                                    <i class="fas fa-<?php echo $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female'; ?>"></i>
                                    Kos <?php echo ucfirst($kos['jenis_kelamin']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($kos['nama']); ?></h3>
                            <p class="tipe"><i class="fas fa-bed"></i> Sisa <?php echo $kos['sisa_kamar']; ?> Kamar</p>
                            <p class="lokasi-daftar"><i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($kos['alamat']); ?>
                            </p>
                            <div class="card-footer">
                                <p class="harga-daftar">
                                    <i class="fas fa-tag"></i>
                                    Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?>/tahun
                                </p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Tidak ditemukan kos yang sesuai dengan kriteria Anda</h3>
                    <p>Silakan coba dengan filter yang berbeda atau hapus semua filter untuk melihat semua kos.</p>
                    <a href="semua_kos.php" class="btn">
                        <i class="fas fa-times"></i> Hapus Semua Filter
                    </a>
                </div>
            <?php endif; ?>
        </section>
       
        <!-- PAGINATION - DI TENGAH -->
        <?php if ($total_items > 0): ?>
        <div class="pagination-wrapper">
            <div class="pagination-container">
                <div class="pagination-links">
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo buildUrl(['page' => 1]); ?>" title="Halaman Pertama">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="<?php echo buildUrl(['page' => $current_page - 1]); ?>" title="Halaman Sebelumnya">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                        <span class="disabled"><i class="fas fa-angle-left"></i></span>
                    <?php endif; ?>
                   
                    <?php
                    // Tampilkan 5 halaman sekitar halaman saat ini
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                   
                    // Adjust jika tidak cukup halaman
                    if ($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }
                   
                    for ($p = $start_page; $p <= $end_page; $p++):
                    ?>
                        <?php if ($p == $current_page): ?>
                            <span class="current"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="<?php echo buildUrl(['page' => $p]); ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                   
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo buildUrl(['page' => $current_page + 1]); ?>" title="Halaman Berikutnya">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="<?php echo buildUrl(['page' => $total_pages]); ?>" title="Halaman Terakhir">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-right"></i></span>
                        <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
   
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('.filter-form');
        
        // Reset ke halaman 1 saat submit filter baru
        filterForm.addEventListener('submit', function(e) {
            const pageInput = document.createElement('input');
            pageInput.type = 'hidden';
            pageInput.name = 'page';
            pageInput.value = '1';
            this.appendChild(pageInput);
        });
        
        // Auto submit filter saat select berubah (opsional)
        document.querySelectorAll('.filter-form select').forEach(select => {
            select.addEventListener('change', function() {
                // Reset page ke 1 saat filter berubah
                const form = this.closest('form');
                const pageInput = form.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.value = '1';
                }
                form.submit();
            });
        });
        
        // Highlight filter yang aktif
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.toString()) {
            document.querySelectorAll('.filter-form input, .filter-form select').forEach(element => {
                if (urlParams.has(element.name) && urlParams.get(element.name) === element.value) {
                    element.style.borderColor = '#ffc107';
                    element.style.boxShadow = '0 0 0 3px rgba(255, 193, 7, 0.2)';
                }
            });
        }
    });
    </script>
</body>
</html>