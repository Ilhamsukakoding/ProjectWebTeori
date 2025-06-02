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
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1e1e2f;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #2c2c3c;
            border-bottom: 1px solid #3a3a4d;
        }

        .navbar-brand {
            font-weight: 600;
            color: #ffffff !important;
        }

        .btn-outline-light {
            border-color: #555;
            color: #ccc;
        }

        .btn-outline-light:hover {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .container {
            max-width: 1000px;
            margin-top: 60px;
            padding: 20px;
        }

        .welcome {
            font-size: 1.6rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .menu-card {
            background-color: #2a2a3d;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .menu-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 12px 24px rgba(59,130,246,0.4);
            background-color: #31314a;
        }

        .menu-card h5 {
            margin-top: 10px;
            font-weight: 600;
        }

        .menu-card span {
            font-size: 2rem;
            display: block;
        }

        @media (max-width: 576px) {
            .menu-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#">Absensi Karyawan</a>
    <div class="d-flex">
        <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
    <div class="welcome">
        Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!
    </div>

    <div class="alert alert-success rounded-3 text-light" style="background-color: #3a3a50; border: none;">
        Anda login sebagai <strong>Karyawan</strong>.
    </div>

    <div class="grid-menu mt-4">
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
