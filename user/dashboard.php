<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

if (!is_user()) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_karyawan = get_karyawan_name($user_id);

$attendance_status = get_today_attendance_status($user_id);

$announcements = [];
$result = $conn->query("SELECT isi FROM pengumuman ORDER BY tanggal DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row['isi'];
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Karyawan - Absensi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
            padding: 0.3rem 0.8rem; 
            height: 90px; 
        }

        .navbar-brand {
            font-weight: 600;
            color: #0d6efd !important;
            font-size: 1.05rem; 
            line-height: 1;
        }

        .btn-outline-dark {
            border-color: #ced4da;
            color: #333;
            font-weight: 500;
            font-size: 0.8rem; 
            padding: 0.25rem 0.6rem; 
            height: 34px;
            display: flex;
            align-items: center;
        }
        

        .container {
            max-width: 1000px;
            padding-top: 70px; /* Tetap cukup untuk menghindari tumpang tindih */
            padding-bottom: 40px;
        }


        .welcome {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #212529;
        }
        .welcome strong {
            color: #0d6efd;
        }

        .alert {
            border-radius: 0.5rem;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }
        
        .info-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .info-card h4 {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-card h4 i {
            font-size: 1.5rem;
        }

        .info-card p {
            margin-bottom: 8px;
            font-size: 1rem;
            color: #555;
        }

        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 0px;
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

        .announcement-section {
            background-color: #e9f5ff;
            border-left: 5px solid #0d6efd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .announcement-section h6 {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .announcement-section h6 i {
            font-size: 1.1rem;
        }

        .announcement-section ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .announcement-section ul li {
            font-size: 0.95rem;
            color: #444;
            margin-bottom: 5px;
        }
        .announcement-section ul li:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .grid-menu {
                grid-template-columns: 1fr;
            }
            .welcome {
                font-size: 1.5rem;
            }
            .info-card {
                padding: 20px;
            }
        }

        .announcement-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .announcement-item {
            background-color: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid #d0e7ff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            font-size: 0.95rem;
            color: #333;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
        }   
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">Absensi Karyawan</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="welcome">
        Selamat datang, <strong><?= $nama_karyawan ?></strong>!
    </div>

    <p class="text-muted mb-4">Hari ini: **<?= date('d M Y') ?>**</p>

    <div class="info-card">
        <h4><i class="bi bi-clock"></i> Status Absensi Hari Ini</h4>
        <?php if ($attendance_status['status'] == 'masuk'): ?>
            <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    Anda sudah **Absen Masuk** pada pukul **<?= htmlspecialchars($attendance_status['time']) ?>**.
                    <?php if ($attendance_status['can_checkout']): ?>
                        <br>Jangan lupa untuk Absen Pulang nanti!
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($attendance_status['status'] == 'pulang'): ?>
            <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    Anda sudah **Absen Masuk** pada pukul **<?= htmlspecialchars($attendance_status['time_in']) ?>**
                    dan **Absen Pulang** pada pukul **<?= htmlspecialchars($attendance_status['time_out']) ?>**.
                </div>
            </div>
        <?php elseif ($attendance_status['status'] == 'error'): ?>
            <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <div>
                    Terjadi kesalahan saat mengambil status absensi: <?= htmlspecialchars($attendance_status['message']) ?>
                </div>
            </div>
        <?php else: // status == 'belum' ?>
            <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    Anda **belum** melakukan Absen Masuk hari ini. Mohon segera lakukan absensi.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid-menu">
        <a href="isi_absensi.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-pencil-square"></i>
                <h5>Isi Absensi</h5>
            </div>
        </a>
        <a href="histori_absensi.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-calendar-check"></i>
                <h5>Riwayat Absensi</h5>
            </div>
        </a>
        <a href="pengajuan_izin_cuti.php" class="card-link">
            <div class="menu-card">
                <i class="bi bi-file-earmark-text"></i>
                <h5>Pengajuan Izin/Cuti</h5>
            </div>
        </a>
    </div>

    <?php if (!empty($announcements)): ?>
    <div class="announcement-section">
        <h6><i class="bi bi-megaphone-fill"></i> Pengumuman Terbaru</h6>
            <div class="announcement-list">
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-item">
                        <i class="bi bi-megaphone-fill text-primary me-2"></i>
                        <?= nl2br(htmlspecialchars($announcement)) ?>
                    </div>
                <?php endforeach; ?>
            </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>