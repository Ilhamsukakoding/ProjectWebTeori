<?php
// includes/config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Ganti dengan username database Anda
define('DB_PASSWORD', '');     // Ganti dengan password database Anda
define('DB_NAME', 'absensi_db'); // Nama database Anda

// Buat koneksi database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Periksa koneksi
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>