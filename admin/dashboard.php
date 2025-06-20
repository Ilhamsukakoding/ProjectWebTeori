<?php
// admin/dashboard.php
// Pastikan session_start() ada di includes/config.php di baris PALING ATAS
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

// === PENTING: Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_admin()) {
    header("Location: ../login.php");
    exit;
}

// Mengambil detail admin (nama) dari sesi atau DB jika diperlukan
$admin_display_name = isset($_SESSION['nama_lengkap']) ? htmlspecialchars($_SESSION['nama_lengkap']) : htmlspecialchars($_SESSION['username']);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Absensi Pekerjaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="dashboard.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
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