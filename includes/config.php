<?php
// includes/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ganti jika username database Anda berbeda
define('DB_PASSWORD', '');     // Ganti jika password database Anda berbeda
define('DB_NAME', 'absensi_db'); // Pastikan nama database sudah benar

// Buat koneksi database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Periksa koneksi
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>