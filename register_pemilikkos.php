<?php
session_start();
$errors = [];
$form_data = []; // Inisialisasi variabel form_data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $kosName = trim($_POST['kosName']);
    $address = trim($_POST['address']);
    $type = $_POST['type'];
    $rooms = intval($_POST['rooms']);
    $description = trim($_POST['description']);

    // Validasi password
    if ($password !== $confirmPassword) {
        $errors[] = 'Password tidak cocok!';
    }

    // Jika tidak ada error, proses registrasi
    if (empty($errors)) {
        // Buat data pemilik kos
        $ownerData = [
            'id' => uniqid('owner_', true),
            'nama' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'pemilik_kos',
            'kos_name' => $kosName,
            'kos_address' => $address,
            'kos_type' => $type,
            'rooms' => $rooms,
            'description' => $description,
            'status' => 'pending',
            'registration_date' => date('Y-m-d H:i:s')
        ];
        
        // Inisialisasi array jika belum ada
        if (!isset($_SESSION['pending_owners'])) {
            $_SESSION['pending_owners'] = [];
        }
        
        // Tambahkan ke array
        $_SESSION['pending_owners'][] = $ownerData;
        
        // Set flag notifikasi
        $_SESSION['show_verification_notif'] = true;
        $_SESSION['registration_success'] = true;
        
        // Reset form data
        unset($_SESSION['form_data']);
        
        // Redirect untuk menghindari resubmit
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // Jika ada error, simpan data form untuk prefilling
        $_SESSION['form_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'kosName' => $kosName,
            'address' => $address,
            'type' => $type,
            'rooms' => $rooms,
            'description' => $description
        ];
    }
}

// Ambil data form untuk prefilling (setelah POST atau dari session)
if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
} else {
    $form_data = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pemilik Kos - KosYuk</title>
    <link rel="stylesheet" href="assets/css/form-gradient.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for owner registration */
        .owner-wrapper {
            max-width: 750px;
        }
        
        .form-section-title {
            background: linear-gradient(135deg, #0A2C4F, #18a0fb);
            color: white;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(10, 44, 79, 0.2);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .owner-wrapper {
                max-width: 100%;
                margin: 0 15px;
            }
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .file-input {
            padding: 12px;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #18a0fb;
            background: #edf2f7;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 6px;
            padding-left: 5px;
        }
        
        select.form-input {
            cursor: pointer;
        }
        
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, 
                transparent, 
                #e2e8f0, 
                transparent);
            margin: 30px 0;
        }
        
        .success-notification {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="form-page-container">
        <div class="form-wrapper owner-wrapper">
            <div class="form-header">
                <img src="assets/images/logo.png" alt="Logo KosYuk" class="form-logo">
                <h1>Daftar Pemilik Kos</h1>
            </div>
            
            <div class="form-body">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
                    <div class="success-notification">
                        <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
                        Pendaftaran berhasil! Data Anda sedang dalam proses verifikasi admin.
                    </div>
                    <?php unset($_SESSION['registration_success']); ?>
                <?php endif; ?>

                <form id="ownerForm" method="POST" enctype="multipart/form-data">
                    <!-- Data Diri -->
                    <div class="form-section-title">Data Diri Pemilik</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" class="form-input" 
                                   value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="phone">No. Handphone</label>
                            <input type="tel" id="phone" name="phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="password-field">
                                <input type="password" id="password" name="password" class="form-input" required>
                                <button type="button" class="toggle-password" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="confirmPassword">Konfirmasi Password</label>
                            <div class="password-field">
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required>
                                <button type="button" class="toggle-password" data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="ktp">Upload KTP</label>
                            <input type="file" id="ktp" name="ktp" class="form-input file-input" accept=".jpg,.jpeg,.png" required>
                            <div class="helper-text">Format: JPG, JPEG, PNG (Maks. 5MB)</div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Data Kos -->
                    <div class="form-section-title">Data Kos</div>
                    
                    <div class="form-group">
                        <label class="form-label" for="kosName">Nama Kos</label>
                        <input type="text" id="kosName" name="kosName" class="form-input" 
                               value="<?php echo htmlspecialchars($form_data['kosName'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Alamat Lengkap</label>
                        <textarea id="address" name="address" class="form-input" placeholder="Masukkan alamat lengkap kos..." required><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="type">Tipe Kos</label>
                            <select id="type" name="type" class="form-input" required>
                                <option value="">Pilih Tipe</option>
                                <option value="Putra" <?php echo (isset($form_data['type']) && $form_data['type'] == 'Putra') ? 'selected' : ''; ?>>Putra</option>
                                <option value="Putri" <?php echo (isset($form_data['type']) && $form_data['type'] == 'Putri') ? 'selected' : ''; ?>>Putri</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="rooms">Jumlah Kamar</label>
                            <input type="number" id="rooms" name="rooms" class="form-input" min="1" placeholder="Contoh: 10" 
                                   value="<?php echo htmlspecialchars($form_data['rooms'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">Deskripsi Kos</label>
                        <textarea id="description" name="description" class="form-input" placeholder="Deskripsikan fasilitas dan keunggulan kos Anda..." required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="photos">Upload Foto Kos</label>
                        <input type="file" id="photos" name="photos[]" class="form-input file-input" accept=".jpg,.jpeg,.png" multiple required>
                        <div class="helper-text">Format: JPG, JPEG, PNG (Maks. 10MB per file, minimal 3 foto)</div>
                    </div>

                    <button type="submit" class="btn-primary">Daftar </button>
                </form>

                <div class="form-links">
                    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                    <p><a href="role.php">Kembali ke pilihan peran</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ============ NOTIFICATION SYSTEM ============
        <?php if(isset($_SESSION['show_verification_notif']) && $_SESSION['show_verification_notif']): ?>
        // Tampilkan notifikasi setelah halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.id = 'verificationNotification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #0A2C4F, #18a0fb);
                color: white;
                padding: 20px 25px;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 10000;
                max-width: 400px;
                animation: slideInRight 0.5s ease;
                border-left: 5px solid #ffc107;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 2rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 8px 0; font-size: 1.2rem; font-weight: 600;">Menunggu Verifikasi</h4>
                        <p style="margin: 0; font-size: 0.95rem; opacity: 0.9;">
                            Pendaftaran berhasil! Data Anda sedang dalam proses verifikasi admin.
                            Waktu verifikasi: 1-3 hari kerja.
                        </p>
                        <div style="margin-top: 12px; display: flex; gap: 10px;">
                            <button onclick="window.location.href='index.php'" 
                                    style="padding: 8px 16px; background: white; color: #0A2C4F; 
                                           border: none; border-radius: 6px; font-weight: 600; 
                                           cursor: pointer; font-size: 0.9rem;">
                                <i class="fas fa-home"></i> Beranda
                            </button>
                            <button onclick="closeNotification()" 
                                    style="padding: 8px 16px; background: rgba(255,255,255,0.2); 
                                           color: white; border: 1px solid rgba(255,255,255,0.3); 
                                           border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
                <div style="position: absolute; top: 10px; right: 10px;">
                    <button onclick="closeNotification()" 
                            style="background: none; border: none; color: white; 
                                   cursor: pointer; font-size: 1.2rem; padding: 5px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Tambahkan progress bar
            const progressBar = document.createElement('div');
            progressBar.style.cssText = `
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 4px;
                background: rgba(255,255,255,0.2);
                border-radius: 0 0 10px 10px;
                overflow: hidden;
            `;
            
            const progressFill = document.createElement('div');
            progressFill.style.cssText = `
                width: 100%;
                height: 100%;
                background: #ffc107;
                animation: progressCountdown 30s linear;
                transform-origin: left;
            `;
            
            progressBar.appendChild(progressFill);
            notification.appendChild(progressBar);
            
            // Hapus session flag
            <?php unset($_SESSION['show_verification_notif']); ?>
            
            // Auto close setelah 30 detik
            setTimeout(() => {
                closeNotification();
            }, 30000);
        });
        
        // Fungsi untuk menutup notifikasi
        function closeNotification() {
            const notification = document.getElementById('verificationNotification');
            if (notification) {
                notification.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }
        }
        
        // Tambahkan keyframes untuk animasi
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes progressCountdown {
                from {
                    transform: scaleX(1);
                }
                to {
                    transform: scaleX(0);
                }
            }
        `;
        document.head.appendChild(style);
        <?php endif; ?>
        
        // ============ FORM VALIDATION ============
        // Toggle password visibility dengan Font Awesome icon
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok!');
                return false;
            } else {
                confirmPassword.setCustomValidity('');
                return true;
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
        
        // Form validation dengan feedback
        document.getElementById('ownerForm').addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                showError('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            // Validasi file KTP
            const ktpFile = document.getElementById('ktp').files[0];
            if (ktpFile) {
                const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
                if (!allowedExtensions.exec(ktpFile.name)) {
                    e.preventDefault();
                    showError('Format file KTP harus JPG, JPEG, atau PNG!');
                    return false;
                }
                
                // Cek ukuran file (maks 5MB)
                if (ktpFile.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    showError('Ukuran file KTP maksimal 5MB!');
                    return false;
                }
            }
            
            // Validasi file foto kos
            const photoFiles = document.getElementById('photos').files;
            if (photoFiles.length < 3) {
                e.preventDefault();
                showError('Minimal upload 3 foto kos!');
                return false;
            }
            
            for (let i = 0; i < photoFiles.length; i++) {
                const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
                if (!allowedExtensions.exec(photoFiles[i].name)) {
                    e.preventDefault();
                    showError('Format file foto harus JPG, JPEG, atau PNG!');
                    return false;
                }
                
                // Cek ukuran file (maks 10MB)
                if (photoFiles[i].size > 10 * 1024 * 1024) {
                    e.preventDefault();
                    showError('Ukuran file foto maksimal 10MB per file!');
                    return false;
                }
            }
            
            // Tampilkan loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
            
            // Auto reset button setelah 5 detik (jika form gagal submit)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Fungsi untuk menampilkan error
        function showError(message) {
            // Cek apakah sudah ada notifikasi error
            let errorNotification = document.querySelector('.error-notification');
            
            if (!errorNotification) {
                errorNotification = document.createElement('div');
                errorNotification.className = 'error-notification';
                errorNotification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #dc3545;
                    color: white;
                    padding: 15px 25px;
                    border-radius: 8px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    z-index: 9999;
                    animation: slideDown 0.3s ease;
                    text-align: center;
                    max-width: 500px;
                    width: 90%;
                `;
                
                document.body.appendChild(errorNotification);
            }
            
            errorNotification.innerHTML = `
                <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>
                ${message}
                <button onclick="this.parentElement.remove()" 
                        style="background: none; border: none; color: white; 
                               margin-left: 15px; cursor: pointer; font-size: 1.1rem;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Auto remove setelah 5 detik
            setTimeout(() => {
                if (errorNotification && errorNotification.parentElement) {
                    errorNotification.remove();
                }
            }, 5000);
        }
        
        // Tambahkan keyframes untuk error animation
        const errorStyle = document.createElement('style');
        errorStyle.textContent = `
            @keyframes slideDown {
                from {
                    transform: translateX(-50%) translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(-50%) translateY(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(errorStyle);
        
        // Preview file name untuk upload
        document.getElementById('ktp').addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                this.nextElementSibling.textContent = `File: ${fileName}`;
            }
        });
        
        document.getElementById('photos').addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileCount = this.files.length;
                this.nextElementSibling.textContent = `${fileCount} file dipilih`;
            }
        });
        
        // Auto focus pada input pertama
        document.querySelector('input[name="name"]').focus();
    </script>
</body>
</html>