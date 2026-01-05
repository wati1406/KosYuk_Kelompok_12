<?php
session_start();

/* =========================
   PROTEK DASHBOARD PENCARI
========================= */
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pencari_kos') {
    header("Location: login.php");
    exit;
}

/* OPTIONAL: admin preview */
$is_admin_preview = isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true;

// INCLUDE DATA KOS YANG KONSISTEN
include 'isi_kos.php';

// AMBIL DATA UNTUK REKOMENDASI KOS (ID 1-4)
$rekomendasi_kos = get_kos_by_range(1, 4);

// AMBIL DATA UNTUK DAFTAR KOS TERBARU (ID 5-13)
$terbaru_kos = get_kos_by_range(5, 13);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pencari Kos - KosYuk</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard_pencarikos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Preview Banner */
        .admin-banner {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            padding: 10px 20px;
            text-align: center;
            position: sticky;
            top: 80px;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-banner-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .admin-banner strong {
            font-weight: 600;
        }
        
        .admin-back-btn {
            background: #0A2C4F;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .admin-back-btn:hover {
            background: #083162;
            transform: translateY(-2px);
        }
        
        /* Section Highlight untuk Anchor Links */
        .section-highlight {
            animation: highlightSection 2s ease;
        }
        
        @keyframes highlightSection {
            0% { background-color: transparent; }
            50% { background-color: rgba(255, 193, 7, 0.1); }
            100% { background-color: transparent; }
        }
        
        /* Smooth scroll offset untuk header fixed */
        :target {
            padding-top: 80px;
            margin-top: -80px;
            display: block;
        }
    </style>
</head>
<body>

<!-- Admin Preview Banner -->
<?php if ($is_admin_preview): ?>
<div class="admin-banner">
    <div class="admin-banner-content">
        <i class="fas fa-eye"></i>
        <strong>Admin Preview Mode</strong> - Anda sedang melihat dashboard sebagai pencari kos.
        <a href="admin_dashboard.php" class="admin-back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Admin
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ========= INCLUDE HEADER DARI FOLDER INCLUDES ========= -->
<?php include 'includes/header_dashboard.php'; ?>

<!-- ===== HERO SECTION ===== -->
<div class="hero-section">
    <div class="hero-slider">
        <div class="slide active" style="background-image: url('assets/images/background2.jpg');">
            <div class="hero-text">
                <h1>Cari Kos Idamanmu</h1>
                <p>Temukan kos terbaik, dekat kampus, harga terjangkau</p>
            </div>
        </div>
        <div class="slide" style="background-image: url('assets/images/background3.jpg');">
            <div class="hero-text">
                <h1>Cari Kos Idamanmu</h1>
                <p>Temukan kos terbaik, dekat kampus, harga terjangkau</p>
            </div>
        </div>
        <div class="slide" style="background-image: url('assets/images/background4.jpg');">
            <div class="hero-text">
                <h1>Cari Kos Idamanmu</h1>
                <p>Temukan kos terbaik, dekat kampus, harga terjangkau</p>
            </div>
        </div>
    </div>
</div>

<!-- ===== FORM PENCARIAN (1 BARIS) ===== -->
<!-- <section class="search-section">
    <div class="search-container">
        <form action="hasil_pencarian.php" method="GET" class="search-form">
            <input type="text" name="keyword" placeholder="Cari kos berdasarkan nama / lokasi">
            <select name="harga">
                <option value="">Filter Harga per Tahun</option>
                <option value="5000000">Dibawah 5 juta</option>
                <option value="10000000">5 - 10 juta</option>
                <option value="15000000">10 - 15 juta</option>
                <option value="20000000">Di atas 15 juta</option>
            </select>
            <select name="fasilitas">
                <option value="">Fasilitas</option>
                <option value="wifi">WiFi</option>
                <option value="ac">AC</option>
                <option value="km_dalam">Kamar Mandi Dalam</option>
                <option value="laundry">Laundry</option>
                <option value="dapur">Dapur Bersama</option>
            </select>
            <select name="jenis">
                <option value="">Jenis Kos</option>
                <option value="putra">Putra</option>
                <option value="putri">Putri</option>
            </select>
            <select name="jarak">
                <option value="">Jarak ke Kampus</option>
                <option value="500">0 - 500 meter</option>
                <option value="1000">500m - 1 km</option>
                <option value="5000">1 km - 5 km</option>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Cari Kos</button>
        </form>
    </div>
</section> -->

<!-- ===== KOS FAVORIT ===== -->
<section class="favorit-section" id="favorit">
    <h2>Rekomendasi Kos</h2>
    <div class="favorit-box">
        <?php foreach($rekomendasi_kos as $kos): 
            $jenis_icon = $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female';
        ?>
        <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="favorit-card-link">
            <div class="favorit-card">
                <div class="card-header">
                    <img src="assets/images/kos<?php echo ($kos['id'] % 4) + 1; ?>.jpg" 
                         alt="Kos <?php echo $kos['nama']; ?>" 
                         onerror="this.src='https://via.placeholder.com/280x200/0A2C4F/FFFFFF?text=KosYuk'">
                </div>
                <div class="card-body">
                    <!-- Jenis Kos -->
                    <div class="jenis-kos-info">
                        <span class="jenis-text">
                            <i class="fas fa-<?php echo $jenis_icon; ?>"></i>
                            Kos <?php echo ucfirst($kos['jenis_kelamin']); ?>
                        </span>
                    </div>
                    
                    <!-- Nama Kos -->
                    <h3>Kos <?php echo $kos['nama']; ?></h3>
                    
                    <!-- Info Lokasi -->
                    <p class="lokasi">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php echo $kos['alamat']; ?>
                    </p>
                    
                    <!-- Info Kamar Tersedia -->
                    <p class="tipe">
                        <i class="fas fa-bed"></i> 
                        Sisa <?php echo $kos['sisa_kamar']; ?> Kamar
                    </p>
                    
                    <!-- Info Harga -->
                    <p class="harga">
                        <i class="fas fa-tag"></i> 
                        Rp <?php echo number_format($kos['harga'], 0, ',', '.'); ?> / tahun
                    </p>
                </div>
                <div class="card-footer">
                    <button class="detail-btn">Lihat Detail</button>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== DAFTAR KOS TERBARU ===== -->
<section class="daftar-section">
    <h2>Daftar Kos Terbaru</h2>
    <div class="daftar-box">
        <?php foreach($terbaru_kos as $kos): 
            $jenis_icon = $kos['jenis_kelamin'] == 'putra' ? 'male' : 'female';
        ?>
        <a href="detail_kos.php?id=<?php echo $kos['id']; ?>" class="daftar-card" data-id="<?php echo $kos['id']; ?>">
            <img src="assets/images/kos<?php echo ($kos['id'] % 4) + 1; ?>.jpg" 
                 alt="Kos <?php echo $kos['nama']; ?>"
                 onerror="this.src='https://via.placeholder.com/100%x200/0A2C4F/FFFFFF?text=KosYuk'">
            <div class="daftar-info">
                <div class="jenis-kos-info">
                    <span class="jenis-text">
                        <i class="fas fa-<?php echo $jenis_icon; ?>"></i>
                        Kos <?php echo ucfirst($kos['jenis_kelamin']); ?>
                    </span>
                </div>
                <h3>Kos <?php echo $kos['nama']; ?></h3>
                <p class="tipe"><i class="fas fa-bed"></i> Sisa <?php echo $kos['sisa_kamar']; ?> Kamar</p>
                <p class="lokasi-daftar"><i class="fas fa-map-marker-alt"></i> <?php echo $kos['alamat']; ?></p>
                <div class="card-footer">
                    <p class="harga-daftar"><i class="fas fa-tag"></i> Rp<?php echo number_format($kos['harga'], 0, ',', '.'); ?>/tahun</p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <button class="lihat-lebih-btn" onclick="window.location.href='semua_kos.php'">
        Lihat Lebih Banyak Kos
    </button>
</section>

<!-- ========== PANDUAN MENGGUNAKAN KOSYUK ========== -->
<section class="info-section" id="panduan">
    <div class="container-3d">
        <h2 class="section-title">Panduan Menggunakan KosYuk</h2>
        <p class="section-subtitle">Ikuti 4 langkah mudah untuk menemukan kos impian Anda</p>
        
        <div class="panduan-container">
            <h3 class="container-title">Langkah-Langkah</h3>
            <p class="container-subtitle">Mulai dari pencarian hingga menempati kos impian Anda</p>
            
            <div class="panduan-row">
                <div class="panduan-card">
                    <span class="panduan-number">01</span>
                    <h4>Cari Kos</h4>
                    <p>Gunakan kolom pencarian atau filter untuk menemukan kos sesuai kebutuhan Anda. Filter berdasarkan lokasi, harga, dan fasilitas.</p>
                </div>
                
                <div class="panduan-card">
                    <span class="panduan-number">02</span>
                    <h4>Lihat Detail Kos</h4>
                    <p>Cek fasilitas, foto, harga, lokasi, dan ulasan sebelum memilih. Semua informasi lengkap tersedia di halaman detail kos.</p>
                </div>
                
                <div class="panduan-card">
                    <span class="panduan-number">03</span>
                    <h4>Booking Kamar</h4>
                    <p>Pilih kamar yang tersedia lalu lakukan pemesanan dengan mengisi data diri. Proses booking mudah dan cepat.</p>
                </div>
                
                <div class="panduan-card">
                    <span class="panduan-number">04</span>
                    <h4>Pembayaran</h4>
                    <p>Lakukan pembayaran sesuai instruksi (transfer atau pembayaran langsung). Sistem pembayaran yang aman dan terpercaya.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== TIPS MEMILIH KOS ========== -->
<section class="info-section">
    <div class="container-3d">
        <h2 class="section-title">Tips Memilih Kos yang Tepat</h2>
        <p class="section-subtitle">Pertimbangan penting sebelum memutuskan tempat tinggal</p>
        
        <div class="tips-container">
            <h3 class="container-title">Tips & Trik</h3>
            <p class="container-subtitle">Hal-hal yang perlu diperhatikan sebelum memilih kos</p>
            
            <div class="tips-row">
                <div class="tip-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4>Lokasi Strategis</h4>
                    <p>Pilih kos yang dekat dengan kampus, minimarket, dan transportasi umum</p>
                </div>
                
                <div class="tip-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Keamanan</h4>
                    <p>Pastikan lingkungan aman, ada penjaga kos, dan sistem keamanan memadai</p>
                </div>
                
                <div class="tip-card">
                    <i class="fas fa-wifi"></i>
                    <h4>Fasilitas Lengkap</h4>
                    <p>Cek ketersediaan WiFi, listrik, air, kamar mandi, dan dapur</p>
                </div>
                
                <div class="tip-card">
                    <i class="fas fa-file-contract"></i>
                    <h4>Peraturan Jelas</h4>
                    <p>Pahami semua peraturan kos sebelum menyetujui kontrak</p>
                </div>
                
                <div class="tip-card">
                    <i class="fas fa-users"></i>
                    <h4>Penghuni Lain</h4>
                    <p>Kenali penghuni lain untuk memastikan lingkungan yang nyaman</p>
                </div>
                
                <div class="tip-card">
                    <i class="fas fa-money-check-alt"></i>
                    <h4>Biaya Tambahan</h4>
                    <p>Tanyakan tentang biaya listrik, air, dan biaya lainnya</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== TENTANG KAMI ========== -->
<section class="info-section" id="tentang-kami">
    <div class="container-3d">
        <h2 class="section-title">Tentang KosYuk</h2>
        <p class="section-subtitle">Platform pencarian kos terbaik untuk mahasiswa UNSOED</p>
        
        <div class="about-container">
            <div class="about-intro">
                <h3>Tentang KosYuk</h3>
                <p>KosYuk adalah platform pencarian kos yang dibuat khusus untuk mahasiswa Fakultas Teknik UNSOED dan sekitarnya. Kami lahir dari pengalaman pribadi yang sering kesulitan mencari kos murah, nyaman, dan dekat kampus.</p>
                <p>Setiap kos yang ada di KosYuk sudah kami verifikasi langsung, foto asli, harga transparan, fasilitas jelas, dan ulasan dari penghuni sebelumnya.</p>
            </div>
            
            <div class="vision-box">
                <h3>Visi Kami</h3>
                <p>Menjadi satu-satunya aplikasi yang kamu butuhkan untuk cari kos di Purbalingga. Tinggal klik, langsung ketemu kos impianmu!</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== KEUNGGULAN KAMI ========== -->
<section class="info-section">
    <div class="container-3d">
        <h2 class="section-title">Keunggulan Kami</h2>
        <p class="section-subtitle">Mengapa memilih KosYuk untuk mencari tempat tinggal?</p>
        
        <div class="keunggulan-container">
            <h3 class="container-title">Kelebihan Kami</h3>
            <p class="container-subtitle">Hal-hal yang membuat KosYuk berbeda dari platform lainnya</p>
            
            <div class="keunggulan-row">
                <div class="keunggulan-card">
                    <i class="fas fa-check-circle"></i>
                    <h4>100% Diverifikasi</h4>
                    <p>Setiap kos diverifikasi langsung oleh tim kami untuk memastikan keaslian informasi</p>
                </div>
                
                <div class="keunggulan-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4>Lokasi Strategis</h4>
                    <p>Hanya menampilkan kos dalam radius 5 km dari kampus UNSOED</p>
                </div>
                
                <div class="keunggulan-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Harga Transparan</h4>
                    <p>Tidak ada biaya tambahan atau markup tersembunyi</p>
                </div>
                
                <div class="keunggulan-card">
                    <i class="fas fa-comments"></i>
                    <h4>Ulasan Jujur</h4>
                    <p>Review langsung dari mahasiswa yang pernah tinggal di kos tersebut</p>
                </div>
                
                <div class="keunggulan-card">
                    <i class="fas fa-headset"></i>
                    <h4>Support 24/7</h4>
                    <p>Tim kami siap membantu kapan saja melalui chat langsung</p>
                </div>
                
                <div class="keunggulan-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Aman Terpercaya</h4>
                    <p>Semua pemilik kos sudah melalui proses verifikasi identitas</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript untuk Smooth Scroll dan Highlight -->
<script>
// Fungsi untuk scroll ke section dengan ID tertentu
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        // Hitung offset untuk header fixed (80px)
        const headerOffset = 80;
        const sectionPosition = section.getBoundingClientRect().top;
        const offsetPosition = sectionPosition + window.pageYOffset - headerOffset;
        
        // Smooth scroll ke section
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
        
        // Tambahkan highlight effect
        section.classList.add('section-highlight');
        
        // Hapus highlight setelah 2 detik
        setTimeout(() => {
            section.classList.remove('section-highlight');
        }, 2000);
        
        return true;
    }
    return false;
}

// Fungsi untuk handle anchor links
function handleAnchorLinks() {
    // Tangani klik pada semua anchor links
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href^="#"]');
        
        if (link) {
            const href = link.getAttribute('href');
            
            // Skip jika href kosong atau hanya "#"
            if (href === '#' || href === '') return;
            
            // Khusus untuk #footer, biarkan footer.php yang menangani
            if (href === '#footer') {
                // Cek jika footer sudah ada di halaman
                const footer = document.getElementById('footer');
                if (footer) {
                    e.preventDefault();
                    
                    // Scroll ke footer
                    footer.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Update URL
                    window.history.pushState(null, null, href);
                    
                    // Highlight footer
                    footer.classList.add('section-highlight');
                    setTimeout(() => {
                        footer.classList.remove('section-highlight');
                    }, 2000);
                }
                return;
            }
            
            // Untuk anchor links lainnya di halaman yang sama
            e.preventDefault();
            
            const sectionId = href.substring(1); // Hapus karakter #
            
            // Coba scroll ke section
            if (scrollToSection(sectionId)) {
                // Update URL tanpa reload
                window.history.pushState(null, null, href);
            }
        }
    });
    
    // Tangani ketika halaman di-load dengan hash di URL
    window.addEventListener('load', function() {
        const hash = window.location.hash;
        
        if (hash && hash !== '#') {
            const sectionId = hash.substring(1);
            
            // Tunggu sedikit untuk memastikan semua elemen sudah di-load
            setTimeout(() => {
                scrollToSection(sectionId);
            }, 500);
        }
    });
    
    // Tangani ketika user menggunakan tombol back/forward
    window.addEventListener('popstate', function() {
        const hash = window.location.hash;
        
        if (hash && hash !== '#') {
            const sectionId = hash.substring(1);
            scrollToSection(sectionId);
        }
    });
}

// Panggil fungsi ketika halaman siap
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi handle anchor links
    handleAnchorLinks();
    
    // Optional: Tambahkan event untuk Hero Slider jika ada
    const heroSlider = document.querySelector('.hero-slider');
    if (heroSlider) {
        let currentSlide = 0;
        const slides = heroSlider.querySelectorAll('.slide');
        const totalSlides = slides.length;
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % totalSlides;
            slides[currentSlide].classList.add('active');
        }
        
        // Auto slide setiap 5 detik
        setInterval(nextSlide, 5000);
    }
});
</script>

<!-- Include JS dan Footer -->
<script src="assets/js/dashboard_pencarikos.js"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>