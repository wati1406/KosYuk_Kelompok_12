<?php
session_start();

// Dummy user (contoh)
$users = [
    [
        'email' => 'admin@gmail.com',
        'password' => 'admin123',
        'role' => 'admin',
        'name' => 'Admin KosYuk'
    ],
    [
        'email' => 'user@gmail.com',
        'password' => 'user123',
        'role' => 'user',
        'name' => 'User KosYuk'
    ]
];

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Email dan password wajib diisi.';
    header("Location: login.php");
    exit;
}

foreach ($users as $user) {
    if ($user['email'] === $email && $user['password'] === $password) {

        $_SESSION['logged_in'] = true;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}

// Kalau gagal login
$_SESSION['login_error'] = 'Email atau password salah!';
header("Location: login.php");
exit;
