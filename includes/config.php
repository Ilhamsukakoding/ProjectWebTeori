<?php
$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan jika ada password MySQL
$dbname = "absensi_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
