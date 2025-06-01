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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #0e0e10;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar {
            border-bottom: 1px solid #444;
        }
        .card-custom {
            background-color: #1a1a1d;
            border: 1px solid #2d2d31;
            transition: transform 0.2s ease-in-out;
            border-radius: 16px;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 191, 255, 0.2);
        }
        a {
            text-decoration: none;
        }
        .list-group-item {
            background-color: transparent;
            border: none;
            color: #00bfff;
        }
        .list-group-item:hover {
            background-color: #2d2d31;
            border-radius: 12px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold text-info" href="#"><i class="bi bi-speedometer2"></i> Admin Panel</a>
    <div class="d-flex">
        <a href="../logout.php" class="btn btn-outline-info btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <h3>Selamat datang, <strong class="text-info"><?= htmlspecialchars($_SESSION['username']) ?></strong></h3>
    <div class="alert alert-dark mt-3 border-info">
        Anda login sebagai <strong class="text-info">Admin</strong>.
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="card card-custom p-3 text-light">
                <h5><i class="bi bi-person-lines-fill"></i> Kelola Data Karyawan</h5>
                <p class="text-light">Lihat, tambah, atau ubah informasi karyawan.</p>
                <a href="karyawan.php" class="btn btn-outline-info btn-sm">Lihat Data</a>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card card-custom p-3 text-light">
                <h5><i class="bi bi-calendar-check"></i> Kelola Data Absensi</h5>
                <p class="text-light">Pantau dan kelola data kehadiran.</p>
                <a href="absensi.php" class="btn btn-outline-info btn-sm">Lihat Absensi</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
