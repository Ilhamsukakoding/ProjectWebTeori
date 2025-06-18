<?php
// admin/dashboard.php
// Pastikan includes/config.php sudah memiliki session_start() di paling atas
require '../includes/config.php';
require '../includes/auth.php';
// require '../includes/function.php'; // Mungkin diperlukan nanti untuk menampilkan ringkasan data, tapi tidak untuk auth check awal

// Cek apakah user sudah login dan levelnya adalah 'admin'
if (!is_admin()) {
    header("Location: ../index.php"); // Arahkan ke halaman login jika tidak valid
    exit;
}

// Ambil nama lengkap admin dari sesi (yang sudah diatur di login.php)
$admin_display_name = isset($_SESSION['nama_lengkap']) ? htmlspecialchars($_SESSION['nama_lengkap']) : htmlspecialchars($_SESSION['username']);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Absensi Pekerjaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"> <style>
        body {
            background-color: #f8fafc; /* Latar belakang cerah */
            color: #333; /* Warna teks gelap */
            font-family: 'Inter', sans-serif; /* Menggunakan font Inter */
            margin: 0;
        }
        .navbar {
            background-color: #ffffff; /* Navbar putih */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem; /* Padding navbar ringkas */
        }
        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important; /* Biru terang */
            font-size: 1.25rem;
        }
        .btn-outline-info { /* Mengganti ke btn-outline-dark untuk konsistensi */
             border-color: #ced4da;
             color: #333;
             font-weight: 500;
             font-size: 0.9rem;
             padding: 0.375rem 0.75rem;
         }
         .btn-outline-info:hover {
             background-color: #0d6efd;
             color: #fff;
             border-color: #0d6efd;
         }

        .container {
            max-width: 1000px; /* Lebar container disesuaikan */
            padding-top: 80px; /* Padding atas sesuai navbar */
            padding-bottom: 40px;
        }
        h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #212529;
        }
        .text-info { /* Mengganti warna info jadi biru standar Bootstrap */
            color: #0d6efd !important;
        }
        .alert-dark { /* Mengubah alert menjadi alert-info */
            background-color: #e9f5ff;
            color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .grid-menu { /* Menggunakan grid layout untuk kartu menu */
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 160px;
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(13, 110, 253, 0.18);
        }

        .menu-card i {
            font-size: 3.2rem;
            color: #0d6efd;
            display: block;
            margin-bottom: 15px;
        }

        .menu-card h5 {
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
            color: #333;
        }

        @media (max-width: 768px) {
            .grid-menu {
                grid-template-columns: 1fr;
            }
            h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
    <div class="d-flex">
        <a href="../logout.php" class="btn btn-outline-info btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <h3>Selamat datang, <strong class="text-info"><?= $admin_display_name ?></strong></h3>
    <div class="alert alert-info mt-3">
        Anda login sebagai <strong class="text-info">Admin</strong>.
    </div>

    <div class="grid-menu">
        <a href="karyawan.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-person-lines-fill"></i>
                <h5>Kelola Karyawan</h5>
            </div>
        </a>
        <a href="absensi.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-calendar-check"></i>
                <h5>Kelola Absensi</h5>
            </div>
        </a>
        <a href="persetujuan_izin_cuti.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-file-earmark-check"></i> <h5>Persetujuan Izin/Cuti</h5>
            </div>
        </a>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>