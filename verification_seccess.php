<?php
session_start();

// Cek apakah ada session verified_owner
if(!isset($_SESSION['verified_owner'])) {
    header("Location: index.php");
    exit;
}

$verified_owner = $_SESSION['verified_owner'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Berhasil - KosYuk</title>
    <link rel="stylesheet" href="assets/css/form-gradient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 20px;
        }
        
        .success-card h1 {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 2rem;
        }
        
        .success-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .owner-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .info-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-primary {
            padding: 12px 30px;
            background: linear-gradient(135deg, #0A2C4F, #18a0fb);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24, 160, 251, 0.3);
        }
        
        .btn-secondary {
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Verifikasi Berhasil!</h1>
            <p>Akun pemilik kos Anda telah berhasil diverifikasi oleh admin. Sekarang Anda dapat login dan mulai mengelola kos Anda.</p>
            
            <div class="owner-info">
                <div class="info-item">
                    <div class="info-label">Nama Pemilik</div>
                    <div class="info-value"><?php echo htmlspecialchars($verified_owner['nama']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nama Kos</div>
                    <div class="info-value"><?php echo htmlspecialchars($verified_owner['kos_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Verifikasi</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($verified_owner['verified_date'])); ?></div>
                </div>
            </div>
            
            <div class="btn-group">
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Sekarang
                </a>
                <a href="index.php" class="btn-secondary">
                    <i class="fas fa-home"></i> Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Hapus session setelah ditampilkan
unset($_SESSION['verified_owner']);
?>