<!-- File: footer_dashboard.php -->
<link rel="stylesheet" href="assets/css/footer.css">
<footer class="footer" id="footer">
    <div class="footer-container">
        <!-- KIRI: Logo & Alamat -->
        <div class="footer-section logo-section">
            <img src="assets/images/logo.png" class="footer-logo" alt="Logo KosYuk">
            <p class="footer-address">
                Jl. Raya Mayjen Sungkono No.KM 5, Dusun 2, Blater, Kec. Kalimanah, Kab. Purbalingga, Jawa Tengah 53371<br>
                Indonesia
            </p>
            <div class="contact-info">
                <p><i class="fas fa-phone"></i> +62 812-3456-7890</p>
                <p><i class="fas fa-envelope"></i> info@kosyuk.co.id</p>
            </div>
        </div>

        <!-- TENGAH: Tautan Cepat -->
        <div class="footer-section">
            <h3>Tautan Cepat</h3>
            <ul class="compact-list">
                <li><a href="dashboard_pencarikos.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="dashboard_pencarikos.php#tentang-kami"><i class="fas fa-info-circle"></i> Tentang Kami</a></li>
                <li><a href="dashboard_pencarikos.php#panduan"><i class="fas fa-book"></i> Panduan</a></li>
                <li><a href="#footer"><i class="fas fa-address-book"></i> Kontak</a></li>
            </ul>
        </div>

        <!-- KANAN: Layanan dan Social Media SEJAJAR -->
        <div class="footer-section combined-section">
            <div class="compact-container">
                <!-- Layanan -->
                <div class="compact-column">
                    <h3>Layanan</h3>
                    <ul class="compact-list">
                        <li><a href="semua_kos.php"><i class="fas fa-search"></i> Cari Kos</a></li>
                        <li><a href="dashboard_pencarikos.php#panduan"><i class="fas fa-question-circle"></i> Bantuan</a></li>
                        <li><a href="dashboard_pencarikos.php#panduan"><i class="fas fa-comments"></i> FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Social Media - DI SEBELAH KANAN (SEJAJAR) -->
                <div class="compact-column social-column">
                    <h3>Ikuti Kami</h3>
                    <div class="compact-social-icons">
                        <a href="#" class="social-icon instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-icon tiktok" title="TikTok"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-icon youtube" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Font Awesome untuk icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- JavaScript untuk Smooth Scroll -->
<script>
// Fungsi untuk smooth scroll ke footer
function scrollToFooter() {
    const footer = document.getElementById('footer');
    if (footer) {
        // Scroll ke footer dengan smooth effect
        footer.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Tambahkan efek highlight
        footer.classList.add('highlight');
        
        // Hapus highlight setelah 1.5 detik
        setTimeout(() => {
            footer.classList.remove('highlight');
        }, 1500);
    }
}

// Deteksi jika URL mengandung #footer
document.addEventListener('DOMContentLoaded', function() {
    // Cek jika ada hash #footer di URL
    if (window.location.hash === '#footer') {
        // Tunggu sedikit agar footer sudah di-load
        setTimeout(scrollToFooter, 300);
    }
    
    // Tangani klik pada link dengan href="#footer"
    document.querySelectorAll('a[href="#footer"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            scrollToFooter();
            
            // Update URL tanpa reload page
            window.history.pushState(null, null, '#footer');
        });
    });
});
</script>