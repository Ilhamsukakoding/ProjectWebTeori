<?php
// user/isi_absensi.php
// Pastikan session_start() ada di includes/config.php di baris PALING ATAS
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

// === PENTING: Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_user()) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date('Y-m-d');
$message = '';
$message_type = '';

// Menggunakan fungsi get_today_attendance_status dari function.php untuk konsistensi
$attendance_status = get_today_attendance_status($user_id);
$data = null;
if ($attendance_status['status'] === 'masuk' || $attendance_status['status'] === 'pulang') {
    $stmt_detail = mysqli_prepare($conn, "SELECT id, jam_masuk, jam_keluar FROM absensi WHERE user_id = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt_detail, 'is', $user_id, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt_detail);
    $result_detail = mysqli_stmt_get_result($stmt_detail);
    $data = mysqli_fetch_assoc($result_detail);
    mysqli_stmt_close($stmt_detail);
}


if (isset($_POST['masuk'])) {
    if ($attendance_status['status'] === 'belum') {
        $keterangan = trim($_POST['keterangan'] ?? '');
        $jam_absen = $_POST['jam_absen'] ?? date('H:i:s');
        
        $stmt = mysqli_prepare($conn, "INSERT INTO absensi (user_id, tanggal, jam_masuk, keterangan) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'isss', $user_id, $tanggal_hari_ini, $jam_absen, $keterangan);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Absen masuk berhasil tercatat!";
                $message_type = "success";
            } else {
                $message = "Gagal mencatat absen masuk: " . mysqli_error($conn);
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Gagal menyiapkan statement absen masuk.";
            $message_type = "danger";
        }
    } else {
        $message = "Anda sudah absen masuk hari ini.";
        $message_type = "warning";
    }
    header("Location: isi_absensi.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

if (isset($_POST['keluar'])) {
    if ($attendance_status['status'] === 'masuk') {
        $jam_absen = $_POST['jam_absen'] ?? date('H:i:s');
        
        if ($data && !empty($data['id'])) {
            $stmt = mysqli_prepare($conn, "UPDATE absensi SET jam_keluar = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $jam_absen, $data['id']);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Absen keluar berhasil tercatat!";
                    $message_type = "success";
                } else {
                    $message = "Gagal mencatat absen keluar: " . mysqli_error($conn);
                    $message_type = "danger";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "Gagal menyiapkan statement absen keluar.";
                $message_type = "danger";
            }
        } else {
             $message = "Anda belum absen masuk hari ini atau data tidak ditemukan.";
             $message_type = "warning";
        }
    } else {
        $message = "Anda belum absen masuk atau sudah absen keluar hari ini.";
        $message_type = "warning";
    }
    header("Location: isi_absensi.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Isi Absensi - Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container">
    <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-person-check"></i> Isi Absensi</h4>
        <p class="text-muted">Lakukan absensi masuk atau keluar untuk hari ini.</p>
        <p class="tanggal-info">Hari ini: **<?= date('d M Y') ?>**</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
    
    <div class="mb-3 text-center">Jam sekarang: <span id="jamSekarang">--:--:--</span></div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <?php if ($attendance_status['status'] === 'belum'): ?>
            <h5 class="card-title mb-4 text-center">Absen Masuk</h5>
            <form method="POST" id="formMasuk">
                <input type="hidden" name="jam_absen" id="jamMasukInput">
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <select name="keterangan" id="keterangan" class="form-select" required>
                        <option value="" disabled selected>Pilih keterangan</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Alfa">Alfa</option>
                    </select>
                </div>
                <button type="submit" name="masuk" class="btn btn-success w-100">Absen Masuk</button>
            </form>
        <?php elseif ($attendance_status['status'] === 'masuk'): ?>
            <div class="absen-info">
                <p>Anda sudah **Absen Masuk** pada pukul <strong><?= htmlspecialchars($attendance_status['time']) ?></strong>.</p>
                <p>Silakan absen keluar saat jam pulang.</p>
            </div>
            
            <form method="POST" id="formKeluar">
                <input type="hidden" name="jam_absen" id="jamKeluarInput">
                <button type="submit" name="keluar" class="btn btn-warning w-100">Absen Keluar</button>
            </form>
        <?php else: // status === 'pulang' ?>
            <div class="alert alert-success text-center mb-0" role="alert">
                <p class="mb-1">Anda sudah **Absen Masuk** pada pukul <strong><?= htmlspecialchars($attendance_status['time_in']) ?></strong></p>
                <p class="mb-0">dan **Absen Pulang** pada pukul <strong><?= htmlspecialchars($attendance_status['time_out']) ?></strong> hari ini.</p>
            </div>
            <div class="alert alert-info mt-3 text-center mb-0">
                Absensi Anda hari ini sudah lengkap.
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateJamInput() {
        const now = new Date();
        const jam = String(now.getHours()).padStart(2, '0');
        const menit = String(now.getMinutes()).padStart(2, '0');
        const detik = String(now.getSeconds()).padStart(2, '0');
        const jamNow = `${jam}:${menit}:${detik}`;

        document.getElementById('jamSekarang').textContent = jamNow;

        const jamMasukInput = document.getElementById('jamMasukInput');
        if (jamMasukInput) jamMasukInput.value = jamNow;

        const jamKeluarInput = document.getElementById('jamKeluarInput');
        if (jamKeluarInput) jamKeluarInput.value = jamNow;
    }

    setInterval(updateJamInput, 1000);
    updateJamInput();
</script>

</body>
</html>