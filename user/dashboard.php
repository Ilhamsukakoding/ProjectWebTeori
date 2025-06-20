<?php
// user/dashboard.php
// Pastikan session_start() ada di includes/config.php di baris PALING ATAS
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php'; // <-- PASTIKAN BARIS INI ADA DAN TIDAK DIKOMENTARI

// === PENTING: Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_user()) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
// PERBAIKAN PENTING: Menambahkan titik koma (;) di akhir baris ini
$user_details = get_user_details($user_id); // <-- TITIK KOMA DITAMBAHKAN DI SINI
$nama_karyawan = $user_details['nama']; // Sekarang $user_details akan terdefinisi dengan benar

$attendance_status = get_today_attendance_status($user_id);

// Ambil pengumuman dari database
$announcements = [];
// Pastikan $conn digunakan untuk query ke tabel 'pengumuman'
$result_pengumuman = $conn->query("SELECT isi FROM pengumuman ORDER BY tanggal DESC");
if ($result_pengumuman && $result_pengumuman->num_rows > 0) {
    while ($row = $result_pengumuman->fetch_assoc()) {
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
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="#"><i class="bi bi-person"></i> Absensi Karyawan</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<main>
    <div class="container">
        <div class="welcome">
            Selamat datang, <strong><?= $nama_karyawan ?></strong>!
        </div>

        <p class="text-muted mb-4">Hari ini: <?= date('d M Y') ?></p>

        <div class="info-card">
            <h4><i class="bi bi-clock"></i> Status Absensi Hari Ini</h4>
            <?php if ($attendance_status['status'] == 'masuk'): ?>
                <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        Anda sudah Absen Masuk pada pukul <?= htmlspecialchars($attendance_status['time']) ?>.
                        <?php if ($attendance_status['can_checkout']): ?>
                            <br>Jangan lupa untuk Absen Pulang nanti!
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($attendance_status['status'] == 'pulang'): ?>
                <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        Anda sudah Absen Masuk pada pukul <?= htmlspecialchars($attendance_status['time_in']) ?>
                        dan Absen Pulang pada pukul <?= htmlspecialchars($attendance_status['time_out']) ?>.
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
                        Anda belum melakukan Absen Masuk hari ini. Mohon segera lakukan absensi.
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
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-item">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> <?= nl2br(htmlspecialchars($announcement)) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?>

</body>
</html>