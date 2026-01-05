<?php
session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if($password !== $confirmPassword){
        $errors[] = 'Password tidak cocok!';
    }

    if(empty($errors)){
        // Simpan data user baru ke session untuk login
        $_SESSION['new_user'] = [
            'nama' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'pencari_kos'
        ];
        
        // SIMPAN KE ARRAY REGISTERED_SEEKERS UNTUK ADMIN
        if(!isset($_SESSION['registered_seekers'])) {
            $_SESSION['registered_seekers'] = [];
        }
        
        // Cek apakah user sudah terdaftar sebelumnya (berdasarkan email)
        $already_registered = false;
        foreach($_SESSION['registered_seekers'] as $seeker) {
            if($seeker['email'] === $email) {
                $already_registered = true;
                break;
            }
        }
        
        // Jika belum terdaftar, tambahkan ke array
        if(!$already_registered) {
            $_SESSION['registered_seekers'][] = [
                'nama' => $name,
                'email' => $email,
                'phone' => $phone,
                'register_date' => date('Y-m-d H:i:s')
            ];
        }
        
        // Set pesan sukses
        $success = 'Pendaftaran berhasil! Silakan login dengan data yang telah didaftarkan.';
        
        // SIMPAN PESAN SUKSES
        $_SESSION['registration_success'] = $success;
        
        // Redirect ke login.php
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pencari Kos - KosYuk</title>
    <link rel="stylesheet" href="assets/css/form-gradient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="form-page-container">
        <div class="form-wrapper">
            <div class="form-header">
                <img src="assets/images/logo.png" alt="Logo KosYuk" class="form-logo">
                <h1>Daftar Pencari Kos</h1>
            </div>
            
            <div class="form-body">
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
                    </div>
                <?php endif; ?>

                <form id="renterForm" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="contoh@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">No. Handphone</label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Konfirmasi Password</label>
                        <div class="password-field">
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Ulangi password" required>
                            <button type="button" class="toggle-password" data-target="confirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Daftar </button>
                </form>

                <div class="form-links">
                    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                    <p><a href="index.php">Kembali ke beranda</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
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
        document.getElementById('renterForm').addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-error';
                errorDiv.innerHTML = '<p>Password dan konfirmasi password tidak cocok!</p>';
                
                // Hapus error sebelumnya jika ada
                const oldError = document.querySelector('.alert-error:not(:first-of-type)');
                if (oldError) oldError.remove();
                
                // Tambahkan error baru
                const firstError = document.querySelector('.alert-error');
                if (firstError) {
                    firstError.parentNode.insertBefore(errorDiv, firstError.nextSibling);
                } else {
                    this.prepend(errorDiv);
                }
                
                // Scroll ke error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>
</html>