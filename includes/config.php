<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// includes/config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'absensi_db');

// Buat koneksi database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Periksa koneksi
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>