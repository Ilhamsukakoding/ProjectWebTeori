<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_user()) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Karyawan - Absensi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 600;
            color: #0d6efd !important;
        }

        .btn-outline-dark {
            border-color: #ced4da;
            color: #333;
        }

        .btn-outline-dark:hover {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        .container {
            max-width: 1000px;
            padding-top: 60px;
            padding-bottom: 40px;
        }

        .welcome {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #0d6efd;
        }

        .alert {
            border-radius: 0.5rem;
            font-size: 0.95rem;
        }

        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .menu-card {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(13, 110, 253, 0.15);
        }

        .menu-card span {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 10px;
        }

        .menu-card h5 {
            font-weight: 600;
            margin: 0;
        }

        @media (max-width: 576px) {
            .menu-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">Absensi Karyawan</a>
    <div class="d-flex">
        <a href="../logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
    <div class="welcome">
        Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!
    </div>

    <div class="alert alert-info">
        Anda login sebagai <strong>Karyawan</strong>.
    </div>

    <div class="grid-menu">
        <a href="isi_absensi.php" class="card-link">
            <div class="menu-card">
                <span>📝</span>
                <h5>Isi Absensi</h5>
            </div>
        </a>
        <a href="histori_absensi.php" class="card-link">
            <div class="menu-card">
                <span>📅</span>
                <h5>Riwayat Absensi</h5>
            </div>
        </a>
    </div>
</div>

</body>
</html>
