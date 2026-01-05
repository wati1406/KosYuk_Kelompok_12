<?php
// CEK APAKAH SESSION SUDAH DIMULAI SEBELUM MEMULAI SESSION BARU
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit;
}

// Jika admin dalam preview mode, ubah tampilan
$is_admin_preview = isset($_SESSION['admin_preview_mode']) && $_SESSION['admin_preview_mode'] === true;

// Ambil nama user dari session (contoh)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KosYuk</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard_pencarikos.css">
    <style>
        /* Dropdown khusus untuk header dashboard */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropbtn {
            background-color: transparent;
            color: #0A2C4F;
            padding: 10px 14px; /* Diperkecil */
            font-size: 15px; /* Diperkecil */
            border: none;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px; /* Diperkecil */
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 180px; /* Diperkecil */
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 10px 14px; /* Diperkecil */
            text-decoration: none;
            display: block;
            font-weight: normal;
            font-size: 14px; /* Diperkecil */
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown:hover .dropbtn {
            color: #07203a;
        }
        
        .dropdown-content a:last-child {
            border-top: 1px solid #e0e0e0;
            color: #d32f2f;
        }
        
        .dropdown-content a:last-child:hover {
            background-color: #ffebee;
        }
        
        /* Welcome message - DIPERKECIL */
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 8px; /* Diperkecil */
            color: #0A2C4F;
            font-weight: 600;
            font-size: 14px; /* Diperkecil */
        }
        
        /* Admin preview indicator - DIPERKECIL */
        .admin-preview-badge {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 2px 6px; /* Diperkecil */
            border-radius: 10px; /* Diperkecil */
            font-size: 0.65rem; /* Diperkecil */
            margin-left: 5px;
            font-weight: bold;
        }
        
        /* Admin preview menu item */
        .admin-preview-menu {
            background: rgba(255, 193, 7, 0.1);
            border-left: 3px solid #ffc107;
        }
        
        .admin-preview-menu:hover {
            background: rgba(255, 193, 7, 0.2);
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px; /* Diperkecil dari 80px */
        }
        
        .logo-top {
            height: 45px; /* Diperkecil dari 50px */
            cursor: pointer;
        }
        
        .navbar {
            display: flex;
            align-items: center;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 15px; /* DIPERKECIL DARI 25px ke 15px */
            align-items: center;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li {
            position: relative;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #0A2C4F;
            font-weight: 500;
            padding: 6px 10px; /* Diperkecil dari 8px 12px */
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 14px; /* Diperkecil dari default */
            white-space: nowrap; /* Mencegah teks wrap */
            cursor: pointer;
        }
        
        .nav-links a:hover {
            background-color: rgba(10, 44, 79, 0.1);
            color: #1A5F9E;
        }
        
        .nav-links .active {
            background-color: #0A2C4F;
            color: white;
        }
        
        /* Smooth scroll untuk seluruh halaman */
        html {
            scroll-behavior: smooth;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .header-container {
                flex-direction: column;
                height: auto;
                padding: 10px 15px;
            }
            
            .logo-top {
                margin-bottom: 10px;
                height: 40px;
            }
            
            .nav-links {
                gap: 12px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-links a {
                padding: 5px 8px;
                font-size: 13px;
            }
            
            .dropbtn {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .user-welcome {
                font-size: 13px;
                display: none; /* Sembunyikan di mobile */
            }
            
            .dropdown-content {
                right: auto;
                left: 0;
                min-width: 160px;
            }
            
            .dropdown-content a {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            /* Nonaktifkan smooth scroll di mobile untuk performa */
            html {
                scroll-behavior: auto;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 8px 12px;
            }
            
            .nav-links {
                gap: 8px;
            }
            
            .nav-links a {
                padding: 4px 6px;
                font-size: 12px;
            }
            
            .logo-top {
                height: 35px;
            }
        }
        
        @media (max-width: 576px) {
            .nav-links {
                gap: 6px;
            }
            
            .nav-links a {
                padding: 3px 5px;
                font-size: 11px;
            }
            
            .dropbtn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
        
        /* Tambahan untuk menjaga proporsi */
        .nav-links li:not(.dropdown):not(.user-welcome) {
            flex-shrink: 0; /* Mencegah item mengecil berlebihan */
        }
        
        /* Wrapper untuk logo dan menu agar lebih kompak */
        .logo-nav-wrapper {
            display: flex;
            align-items: center;
            gap: 30px; /* Jarak antara logo dan menu */
            width: 100%;
            justify-content: space-between;
        }
        
        /* Highlight efek untuk bagian yang sedang di-scroll */
        :target {
            animation: highlight 2s ease;
        }
        
        @keyframes highlight {
            0% { background-color: rgba(10, 44, 79, 0.1); }
            100% { background-color: transparent; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-nav-wrapper">
                <!-- Logo dengan link ke dashboard -->
                <a href="dashboard_pencarikos.php" style="text-decoration: none; display: flex; align-items: center;">
                    <img src="assets/images/logo1.png" alt="Logo KosYuk" class="logo-top">
                </a>

                <!-- Navbar dengan dropdown Profil -->
                <nav class="navbar">
                    <ul class="nav-links">
                        <!-- Menu Beranda -->
                        <li><a href="#" id="menu-beranda" data-target="beranda">Beranda</a></li>
                        <!-- Menu lainnya -->
                        <li><a href="#" id="menu-tentang" data-target="tentang-kami">Tentang Kami</a></li>
                        <li><a href="#" id="menu-panduan" data-target="panduan">Panduan</a></li>
                        <li><a href="#" id="menu-kontak" data-target="kontak">Kontak</a></li>
                         <li><a href="semua_kos.php" ><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
     fill="none" viewBox="0 0 24 24" stroke="black" stroke-width="2">
  <circle cx="11" cy="11" r="8"/>
  <line x1="21" y1="21" x2="16.65" y2="16.65"/>
</svg>
cari kos</a></li>
                        
                        <!-- Dropdown Profil - sekarang lebih kompak -->
                        <li class="dropdown">
                            <a href="#" class="dropbtn">Profil ▾</a>
                            <div class="dropdown-content">
                                <a href="profil_pencarikos.php"> Pengaturan Profil</a>
                                <a href="dashboard_pencarikos.php#favorit"> Rekomendasi Kos</a>
                                
                                <?php if($is_admin_preview): ?>
                                    <a href="admin_dashboard.php" class="admin-preview-menu">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Admin
                                    </a>
                                <?php else: ?>
                                    <a href="logout.php" onclick="return confirm('Yakin ingin logout?')"> Logout</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- JavaScript untuk smooth scroll dengan offset untuk header fixed -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua menu link
            const menuLinks = document.querySelectorAll('.nav-links a[data-target]');
            const currentPage = window.location.pathname.split('/').pop();
            
            // Fungsi untuk scroll ke bagian tertentu
            function scrollToSection(sectionId) {
                console.log("Mencoba scroll ke: " + sectionId);
                
                // Jika target adalah 'beranda', scroll ke atas
                if (sectionId === 'beranda') {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    return true;
                }
                
                // Cari elemen target langsung dengan ID
                let targetElement = document.getElementById(sectionId);
                
                // Jika tidak ditemukan, coba cari dengan berbagai kemungkinan ID
                if (!targetElement) {
                    const possibleIds = {
                        'tentang-kami': ['tentang-kami', 'about', 'about-us', 'tentang'],
                        'panduan': ['panduan', 'guide', 'panduan-pengguna', 'user-guide'],
                        'kontak': ['kontak', 'contact', 'kontak-kami', 'contact-us', 'hubungi-kami']
                    };
                    
                    if (possibleIds[sectionId]) {
                        for (const id of possibleIds[sectionId]) {
                            targetElement = document.getElementById(id);
                            if (targetElement) {
                                console.log("Elemen ditemukan dengan ID alternatif: " + id);
                                break;
                            }
                        }
                    }
                }
                
                // Jika masih tidak ditemukan, cari dengan class atau data attribute
                if (!targetElement) {
                    targetElement = document.querySelector(`[data-section="${sectionId}"]`);
                }
                
                // Jika masih tidak ditemukan, cari dengan teks konten
                if (!targetElement) {
                    const sectionNames = {
                        'tentang-kami': ['tentang', 'about'],
                        'panduan': ['panduan', 'guide'],
                        'kontak': ['kontak', 'contact', 'hubungi']
                    };
                    
                    if (sectionNames[sectionId]) {
                        const elements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, section, div');
                        for (const el of elements) {
                            const text = el.textContent.toLowerCase();
                            for (const keyword of sectionNames[sectionId]) {
                                if (text.includes(keyword) && el.id) {
                                    targetElement = el;
                                    break;
                                }
                            }
                            if (targetElement) break;
                        }
                    }
                }
                
                if (!targetElement) {
                    console.warn("Elemen dengan ID '" + sectionId + "' tidak ditemukan di halaman");
                    
                    // Jika tidak ditemukan, scroll ke paling bawah untuk kontak
                    if (sectionId === 'kontak') {
                        window.scrollTo({
                            top: document.body.scrollHeight,
                            behavior: 'smooth'
                        });
                        return true;
                    }
                    
                    // Untuk lainnya, scroll ke atas
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    return false;
                }
                
                // Hitung offset untuk header fixed
                const headerHeight = document.querySelector('header').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                console.log("Scroll ke posisi: " + targetPosition + " (header height: " + headerHeight + ")");
                
                // Smooth scroll dengan offset
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL tanpa reload halaman
                if (history.pushState) {
                    history.pushState(null, null, '#' + sectionId);
                }
                
                return true;
            }
            
            // Fungsi untuk update active menu
            function updateActiveMenu(sectionId) {
                console.log("Update menu aktif: " + sectionId);
                menuLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-target') === sectionId) {
                        link.classList.add('active');
                    }
                });
            }
            
            // Tambahkan event listener untuk setiap menu
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('data-target');
                    console.log("Menu diklik: " + targetId);
                    
                    // Scroll ke bagian yang dituju
                    if (scrollToSection(targetId)) {
                        updateActiveMenu(targetId);
                    }
                });
            });
            
            // Fungsi untuk cek apakah elemen ada di viewport
            function isElementInViewport(el) {
                if (!el) return false;
                const rect = el.getBoundingClientRect();
                return (
                    rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.bottom >= 0
                );
            }
            
            // Fungsi untuk mencari semua elemen yang mungkin menjadi target scroll
            function getSectionElement(sectionId) {
                // Cari elemen utama dengan ID yang sesuai
                let element = document.getElementById(sectionId);
                
                // Jika tidak ditemukan, cari dengan ID alternatif
                if (!element) {
                    const alternativeIds = {
                        'tentang-kami': ['about', 'tentang', 'about-us'],
                        'panduan': ['guide', 'panduan-pengguna'],
                        'kontak': ['contact', 'kontak-kami', 'hubungi-kami']
                    };
                    
                    if (alternativeIds[sectionId]) {
                        for (const id of alternativeIds[sectionId]) {
                            element = document.getElementById(id);
                            if (element) break;
                        }
                    }
                }
                
                // Jika masih tidak ditemukan, cari dengan data attribute
                if (!element) {
                    element = document.querySelector(`[data-section="${sectionId}"]`);
                }
                
                return element;
            }
            
            // Update active menu saat scroll
            window.addEventListener('scroll', function() {
                // Jika di paling atas, aktifkan menu Beranda
                if (window.scrollY <= 10) {
                    menuLinks.forEach(l => l.classList.remove('active'));
                    const berandaLink = document.getElementById('menu-beranda');
                    if (berandaLink) berandaLink.classList.add('active');
                    return;
                }
                
                // Cari semua section yang mungkin
                const sections = [
                    { id: 'tentang-kami', element: getSectionElement('tentang-kami') },
                    { id: 'panduan', element: getSectionElement('panduan') },
                    { id: 'kontak', element: getSectionElement('kontak') }
                ].filter(section => section.element);
                
                if (sections.length === 0) {
                    console.log("Tidak ada section yang ditemukan");
                    return;
                }
                
                let activeSection = null;
                let minDistance = Infinity;
                
                // Cari section yang paling dekat dengan atas viewport
                sections.forEach(section => {
                    const rect = section.element.getBoundingClientRect();
                    const distance = Math.abs(rect.top);
                    
                    // Jika section terlihat di viewport atau paling dekat
                    if (isElementInViewport(section.element) || distance < minDistance) {
                        if (distance < minDistance) {
                            minDistance = distance;
                            activeSection = section.id;
                        }
                    }
                });
                
                // Update active menu berdasarkan section yang ditemukan
                if (activeSection) {
                    menuLinks.forEach(link => {
                        link.classList.remove('active');
                        const target = link.getAttribute('data-target');
                        
                        // Cocokkan dengan section yang aktif
                        if (target === activeSection) {
                            link.classList.add('active');
                        }
                    });
                }
            });
            
            // Cek hash URL saat halaman dimuat
            window.addEventListener('load', function() {
                console.log("Halaman dimuat, hash: " + window.location.hash);
                
                // Tunggu sebentar untuk memastikan semua konten dimuat
                setTimeout(() => {
                    if (window.location.hash) {
                        const hash = window.location.hash.substring(1); // Hapus # dari hash
                        console.log("Hash dari URL: " + hash);
                        
                        if (hash) {
                            // Scroll ke bagian yang dimaksud
                            scrollToSection(hash);
                            
                            // Update active menu berdasarkan hash
                            updateActiveMenu(hash);
                        }
                    } else {
                        // Jika tidak ada hash, cek apakah kita di atas
                        if (window.scrollY <= 10) {
                            updateActiveMenu('beranda');
                        }
                    }
                }, 100);
            });
            
            // Tangani perubahan hash URL
            window.addEventListener('hashchange', function() {
                const hash = window.location.hash.substring(1);
                if (hash) {
                    setTimeout(() => {
                        scrollToSection(hash);
                        updateActiveMenu(hash);
                    }, 100);
                }
            });
            
            // Inisialisasi: jika di atas, aktifkan Beranda
            if (window.scrollY <= 10) {
                updateActiveMenu('beranda');
            }
        });
    </script>
</body>
</html>