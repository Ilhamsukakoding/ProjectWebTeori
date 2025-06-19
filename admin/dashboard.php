<?php
require '../includes/config.php';
require '../includes/auth.php';

// Cek apakah user sudah login sebagai admin
if (!is_admin()) {
    header("Location: ../index.php");
    exit;
}

// Ambil nama lengkap admin dari sesi
$admin_display_name = isset($_SESSION['nama_lengkap']) ? 
    htmlspecialchars($_SESSION['nama_lengkap']) : 
    (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin');
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Absensi Pekerjaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"> 
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #f8fafc;
            color: #333;
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            font-size: 1.25rem;
        }

        .btn-outline-info {
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

        main {
            flex: 1;
            padding-top: 80px; /* menghindari tumpukan navbar */
            padding-bottom: 40px;
        }

        .container {
            max-width: 1000px;
        }

        h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #212529;
        }

        .text-info {
            color: #0d6efd !important;
        }

        .alert-dark {
            background-color: #e9f5ff;
            color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .grid-menu {
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
            margin-bottom: 15px;
        }

        .menu-card h5 {
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
            color: #333;
        }

        footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
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
        <a class="navbar-brand"><i class="bi bi-speedometer2"></i> Admin Panel</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-info btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</nav>

<main>
    <div class="container">
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
                    <i class="bi bi-file-earmark-check"></i>
                    <h5>Persetujuan Izin/Cuti</h5>
                </div>
            </a>
            <a href="kelola_pengumuman.php" class="card-link">
                <div class="menu-card">
                    <i class="bi bi-megaphone-fill"></i>
                    <h5>Kelola Pengumuman</h5>
                </div>
            </a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
