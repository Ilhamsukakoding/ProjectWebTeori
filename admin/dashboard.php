<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Absensi Pekerjaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Dashboard Admin</a>
    <div class="d-flex">
        <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h3>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></h3>

    <div class="alert alert-info mt-3">
        Anda login sebagai <strong>Admin</strong>.
    </div>
    <ul class="list-group">
        <li class="list-group-item"><a href="karyawan.php">Kelola Data Karyawan</a></li>
        <li class="list-group-item"><a href="absensi.php">Kelola Data Absensi</a></li>
    </ul>
</div>
</body>
</html>
