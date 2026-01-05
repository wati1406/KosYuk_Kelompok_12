<?php
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    if (empty($name) || empty($password)) {
        $errors[] = 'Nama lengkap dan password wajib diisi!';
    } else {
        // ============ CEK ADMIN ============
        $admin_username = 'admin';
        $admin_password = 'admin';
        
        if (strtolower($name) === strtolower($admin_username) && $password === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin_username;
            $_SESSION['user_role'] = 'admin';
            $_SESSION['logged_in'] = true;
            $_SESSION['user_name'] = 'Administrator';
            
            // CLEAR SESSION VARIABLES LAIN
            unset($_SESSION['new_user']);
            unset($_SESSION['success_message']);
            unset($_SESSION['registration_success']);
            
            header("Location: admin_dashboard.php");
            exit;
        }
        
        // ============ CEK PENCARI KOS ============
        // Cek dari session new_user (baru registrasi)
        if (isset($_SESSION['new_user'])) {
            $new_user = $_SESSION['new_user'];
            
            if (strtolower($name) === strtolower($new_user['nama']) && $password === $new_user['password']) {
                $_SESSION['user_name'] = $new_user['nama'];
                $_SESSION['user_email'] = $new_user['email'] ?? '';
                $_SESSION['user_phone'] = $new_user['phone'] ?? '';
                $_SESSION['user_role'] = 'pencari_kos';
                $_SESSION['logged_in'] = true;
                
                // Tambahkan ke registered_seekers jika belum ada
                if(!isset($_SESSION['registered_seekers'])) {
                    $_SESSION['registered_seekers'] = [];
                }
                
                $already_exists = false;
                foreach($_SESSION['registered_seekers'] as $seeker) {
                    if(isset($seeker['email']) && $seeker['email'] === $new_user['email']) {
                        $already_exists = true;
                        break;
                    }
                }
                
                if(!$already_exists) {
                    $_SESSION['registered_seekers'][] = [
                        'nama' => $new_user['nama'],
                        'email' => $new_user['email'],
                        'phone' => $new_user['phone'],
                        'register_date' => date('Y-m-d H:i:s')
                    ];
                }
                
                // CLEAR SESSION
                unset($_SESSION['new_user']);
                unset($_SESSION['success_message']);
                unset($_SESSION['registration_success']);
                
                header("Location: dashboard_pencarikos.php");
                exit;
            }
        }
        
        // Cek dari registered_seekers (sudah terdaftar sebelumnya)
        if(isset($_SESSION['registered_seekers']) && is_array($_SESSION['registered_seekers'])) {
            foreach($_SESSION['registered_seekers'] as $seeker) {
                if(strtolower($name) === strtolower($seeker['nama']) && $password === 'password123') {
                    // Default password untuk testing
                    $_SESSION['user_name'] = $seeker['nama'];
                    $_SESSION['user_email'] = $seeker['email'] ?? '';
                    $_SESSION['user_phone'] = $seeker['phone'] ?? '';
                    $_SESSION['user_role'] = 'pencari_kos';
                    $_SESSION['logged_in'] = true;
                    
                    // CLEAR SESSION
                    unset($_SESSION['new_user']);
                    unset($_SESSION['success_message']);
                    unset($_SESSION['registration_success']);
                    
                    header("Location: dashboard_pencarikos.php");
                    exit;
                }
            }
        }
        
        // ============ CEK PEMILIK KOS ============
        $login_success = false;
        
        // CEK dari verified_owners array
        if (isset($_SESSION['verified_owners']) && is_array($_SESSION['verified_owners'])) {
            foreach ($_SESSION['verified_owners'] as $index => $owner) {
                if (strtolower($name) === strtolower($owner['nama']) && $password === $owner['password']) {
                    $_SESSION['user_name'] = $owner['nama'];
                    $_SESSION['user_email'] = $owner['email'] ?? '';
                    $_SESSION['user_phone'] = $owner['phone'] ?? '';
                    $_SESSION['user_role'] = 'pemilik_kos';
                    $_SESSION['logged_in'] = true;
                    $_SESSION['kos_data'] = [
                        'nama_kos' => $owner['kos_name'],
                        'alamat' => $owner['kos_address'],
                        'tipe' => $owner['kos_type']
                    ];
                    $_SESSION['owner_index'] = $index;
                    
                    // CLEAR SESSION
                    unset($_SESSION['new_user']);
                    unset($_SESSION['success_message']);
                    unset($_SESSION['registration_success']);
                    
                    header("Location: dashboard_pemilik.php");
                    exit;
                }
            }
        }
        
        // CEK dari pending_owners array
        if (isset($_SESSION['pending_owners']) && is_array($_SESSION['pending_owners'])) {
            foreach ($_SESSION['pending_owners'] as $owner) {
                if (strtolower($name) === strtolower($owner['nama']) && $password === $owner['password']) {
                    $errors[] = 'Akun Anda masih menunggu verifikasi admin!';
                    $login_success = true;
                    break;
                }
            }
        }
        
        // Jika tidak ada yang cocok
        if (empty($errors) && !$login_success) {
            $errors[] = 'Nama lengkap atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - KosYuk</title>
    <link rel="stylesheet" href="assets/css/form-gradient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tambahan style untuk link logout berwarna putih */
        .logout-link {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
        }
        
        .logout-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff !important;
            transform: translateY(-2px);
        }
        
        .logout-container {
            margin-top: 15px;
            text-align: center;
        }
        
        /* Hapus border/garis pada alert */
        .alert-error, .alert-success {
            border: none !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .alert-error {
            background-color: #ffeaea !important;
            color: #d32f2f !important;
        }
        
        .alert-success {
            background-color: #edf7ed !important;
            color: #2e7d32 !important;
        }
        
        /* Optional: Hapus garis pada form jika ada */
        .form-wrapper {
            border: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .form-input {
            border: 1px solid #ddd !important;
        }
        
        .form-input:focus {
            border-color: #0A2C4F !important;
            box-shadow: 0 0 0 3px rgba(10, 44, 79, 0.1) !important;
        }
    </style>
</head>
<body>
    <div class="form-page-container">
        <div class="form-wrapper">
            <div class="form-header">
                <img src="assets/images/logo.png" alt="Logo KosYuk" class="form-logo">
                <h1>Masuk ke KosYuk</h1>
            </div>
            
            <div class="form-body">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
                    </div>
                <?php endif; ?>

                <?php 
                // Tampilkan pesan sukses dari registrasi jika ada
                if(isset($_SESSION['registration_success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['registration_success'] . '</div>';
                    unset($_SESSION['registration_success']);
                }
                ?>

                <form id="loginForm" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Masuk</button>
                </form>

                <div class="form-links">
                    <p>Belum punya akun? <a href="role.php">Daftar di sini</a></p>
                    <p><a href="index.php">Kembali ke beranda</a></p>
                    
                    <!-- PERUBAHAN: Link logout dengan warna putih -->
                    <div class="logout-container">
                        <a href="logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Clear Session / Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
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
    </script>
</body>
</html>