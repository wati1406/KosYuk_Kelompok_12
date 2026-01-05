<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran - KosYuk</title>
    <link rel="stylesheet" href="assets/css/form-pattern.css">
    <style>
        /* Role Page Specific */
        .role-page {
            min-height: 100vh;
            padding: 80px 20px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .role-container {
            width: 100%;
            max-width: 900px;
            text-align: center;
        }
        
        .role-header {
            margin-bottom: 50px;
        }
        
        .role-title {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #0A2C4F, #18a0fb);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .role-subtitle {
            font-size: 1.1rem;
            color: #475569;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .role-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .role-card {
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 
                0 8px 32px rgba(10, 44, 79, 0.08),
                0 4px 8px rgba(0, 0, 0, 0.03),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Pola latar belakang card */
        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(45deg, transparent 95%, rgba(24, 160, 251, 0.03) 95%),
                linear-gradient(135deg, transparent 95%, rgba(10, 44, 79, 0.02) 95%);
            background-size: 20px 20px;
            z-index: 0;
            pointer-events: none;
        }
        
        .role-card > * {
            position: relative;
            z-index: 1;
        }
        
        .role-card:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 12px 40px rgba(10, 44, 79, 0.12),
                0 6px 12px rgba(0, 0, 0, 0.05);
        }
        
        .role-image {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #0A2C4F;
            box-shadow: 0 6px 16px rgba(10, 44, 79, 0.15);
            transition: all 0.3s ease;
        }
        
        .role-card:hover .role-image {
            transform: scale(1.05);
            border-color: #18a0fb;
            box-shadow: 0 8px 20px rgba(24, 160, 251, 0.2);
        }
        
        .role-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .role-card h2 {
            color: #0A2C4F;
            font-size: 1.7rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .role-card p {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .btn-role {
            display: inline-block;
            padding: 12px 35px;
            background: linear-gradient(135deg, #0A2C4F, #18a0fb);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(24, 160, 251, 0.2);
        }
        
        .btn-role:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(24, 160, 251, 0.3);
            background: linear-gradient(135deg, #1A4A7A, #4DC1FF);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 40px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #0A2C4F;
            background: rgba(10, 44, 79, 0.1);
        }
        
        .login-link {
            margin-top: 30px;
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: #18a0fb;
            text-decoration: none;
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            background: rgba(24, 160, 251, 0.1);
        }
        
        @media (max-width: 768px) {
            .role-cards {
                flex-direction: column;
                align-items: center;
                gap: 25px;
            }
            
            .role-card {
                width: 100%;
                max-width: 350px;
            }
            
            .role-title {
                font-size: 2rem;
            }
            
            .role-image {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="role-page">
        <div class="role-container">
            <div class="role-header">
                <h1 class="role-title">Pilih Peran Anda</h1>
                <p class="role-subtitle">Pilih peran yang sesuai untuk memulai di KosYuk</p>
            </div>
            
            <div class="role-cards">
                <!-- Pemilik Kos -->
                <div class="role-card">
                    <div class="role-image">
                        <img src="assets/images/role2.png" alt="Pemilik Kos">
                    </div>
                    <h2>Pemilik Kos</h2>
                    <p>Daftarkan kos Anda dan kelola informasi kamar, fasilitas, harga, dan pemesanan dengan mudah.</p>
                    <a href="register_pemilikkos.php" class="btn-role">Daftar sebagai Pemilik</a>
                </div>
                
                <!-- Pencari Kos -->
                <div class="role-card">
                    <div class="role-image">
                        <img src="assets/images/role1.png" alt="Pencari Kos">
                    </div>
                    <h2>Pencari Kos</h2>
                    <p>Temukan kos terdekat dari kampus dengan informasi lengkap fasilitas, harga, dan lokasi.</p>
                    <a href="registrasi_pencarikos.php" class="btn-role">Daftar sebagai Pencari</a>
                </div>
            </div>
            
            <div class="login-link">
                <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>
            
            <a href="index.php" class="back-link">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>