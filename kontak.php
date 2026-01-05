<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - KosYuk</title>
    <link rel="stylesheet" href="assets/css/kontak.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Kontak Kami</h1>
        <p class="hero-subtitle">
            KosYuk hadir untuk membantu Anda menemukan kos yang sesuai kebutuhan. 
            Jika Anda memiliki pertanyaan, kendala, atau ingin bekerja sama sebagai pemilik kos, 
            silakan hubungi kami melalui form atau kontak yang tersedia.
        </p>
    </div>
</section>

<!-- Contact Icons & Social Media Horizontal -->
<div class="contact-social-container">
    <div class="contact-row">
        <!-- Hubungi Kami - Icon Saja dalam 1 baris -->
        <div class="contact-icons-single-row">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4>Alamat</h4>
                <p>Jl. Raya Mayjen Sungkono No.KM 5<br>Purbalingga</p>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h4>Email</h4>
                <p>info@kosyuk.com<br>Respon cepat</p>
            </div>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>Telepon/WA</h4>
                <p>+62 812-3456-7890</p>
            </div>
            
            <!-- Jam Operasional -->
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h4>Jam Operasional</h4>
                <p>Senin - Jumat<br>08.00 - 17.00 WIB</p>
            </div>
        </div>
        
        <!-- Social Media Horizontal -->
        <div class="social-horizontal">
            <h3>Ikuti Media Sosial Kami</h3>
            <div class="social-icons-horizontal">
                <a href="#" class="social-icon-h instagram" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-icon-h facebook" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-icon-h tiktok" title="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
                <a href="#" class="social-icon-h twitter" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-icon-h youtube" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER: Kirim Pesan & Maps -->
<div class="contact-wrapper">
    <div class="contact-content">
        <!-- Form Kontak -->
        <div class="form-section">
            <h3>Kirim Pesan</h3>
            <form id="contactForm" method="POST">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama Anda" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subjek</label>
                    <input type="text" id="subject" name="subject" placeholder="Tentang apa pesan Anda?" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Pesan</label>
                    <textarea id="message" name="message" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Kirim Pesan</button>
            </form>
            <div id="formMessage"></div>
        </div>

        <!-- Google Maps -->
        <div class="map-section">
            <h3>Lokasi Kami</h3>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.997875156683!2d109.36340357586562!3d-7.316944292774282!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655c5b9c5e5e5d%3A0x8f8b8f8b8f8b8f8b!2sFakultas%20Teknik%20Universitas%20Jenderal%20Soedirman%20(Kampus%20Blater)!5e0!3m2!1sid!2sid!4v1720000000000!5m2!1sid!2sid" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/contact.js"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>