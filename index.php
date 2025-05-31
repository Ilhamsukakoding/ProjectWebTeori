<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fungsi cek role (contoh, sesuaikan dengan implementasi kamu di includes/auth.php)
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Redirect ke dashboard sesuai role
if (is_admin()) {
    header("Location: admin/dashboard.php");
    exit;
} elseif (is_user()) {
    header("Location: user/dashboard.php");
    exit;
} else {
    // Role tidak dikenali, logout atau tampilkan error
    echo "Role pengguna tidak dikenali.";
    exit;
}
