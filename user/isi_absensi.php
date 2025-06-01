<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_user()) {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal = date('Y-m-d');

// Cek apakah sudah absen hari ini
$query = "SELECT * FROM absensi WHERE user_id = ? AND tanggal = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'is', $user_id, $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Proses Absen Masuk
if (isset($_POST['masuk'])) {
    if (!$data) {
        $keterangan = trim($_POST['keterangan']) ?? '';
        $jam_absen = $_POST['jam_absen'] ?? date('H:i:s');
        $stmt = mysqli_prepare($conn, "INSERT INTO absensi (user_id, tanggal, jam_masuk, keterangan) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $user_id, $tanggal, $jam_absen, $keterangan);
        mysqli_stmt_execute($stmt);
        header("Location: isi_absensi.php");
        exit;
    }
}

// Proses Absen Keluar
if (isset($_POST['keluar']) && $data && $data['jam_keluar'] == null) {
    $jam_absen = $_POST['jam_absen'] ?? date('H:i:s');
    $stmt = mysqli_prepare($conn, "UPDATE absensi SET jam_keluar = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $jam_absen, $data['id']);
    mysqli_stmt_execute($stmt);
    header("Location: isi_absensi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Isi Absensi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1e1e2f;
            color: #ffffff;
            padding-top: 40px;
        }

        label, p, .form-label, .alert, .btn, .card-body {
            color: #ffffff !important;
        }

        .form-control::placeholder {
            color: #cccccc;
        }

        .container {
            max-width: 600px;
        }

        h4 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #ffffff;
        }

        #jamSekarang {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3b82f6;
        }

        .btn-success {
            background-color: #10b981;
            border: none;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-warning {
            background-color: #f59e0b;
            border: none;
            color: #1e1e2f;
        }

        .btn-warning:hover {
            background-color: #d97706;
        }

        .btn-secondary {
            background-color: #4b5563;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #374151;
        }

        .form-control {
            background-color: #2a2a3d;
            border: 1px solid #444;
            color: #ffffff;
        }

        .form-control:focus {
            background-color: #2a2a3d;
            color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.25);
        }

        .card {
            background-color: #2a2a3d;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.01);
        }

        .alert {
            background-color: #3a3a50;
            border: none;
        }

        .mb-3 span {
            font-size: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h4>Absensi Hari Ini (<?= date('d-m-Y') ?>)</h4>
    <div class="mb-3">Jam sekarang: <span id="jamSekarang">--:--:--</span></div>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">← Kembali</a>

    <div class="card">
        <div class="card-body">
            <?php if (!$data): ?>
                <!-- Form Absen Masuk -->
                <form method="POST" id="formMasuk">
                    <input type="hidden" name="jam_absen" id="jamMasukInput">
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <input type="text" name="keterangan" id="keterangan" class="form-control" placeholder="Contoh: WFH, Kantor, dll.">
                    </div>
                    <button type="submit" name="masuk" class="btn btn-success w-100">Absen Masuk</button>
                </form>
            <?php else: ?>
                <!-- Tampilkan jam masuk/keluar -->
                <p><strong>Jam Masuk:</strong> <?= htmlspecialchars($data['jam_masuk']) ?? '-' ?></p>
                <p><strong>Jam Keluar:</strong> <?= htmlspecialchars($data['jam_keluar']) ?? '-' ?></p>
                <?php if (!$data['jam_keluar']): ?>
                    <!-- Form Absen Keluar -->
                    <form method="POST" id="formKeluar">
                        <input type="hidden" name="jam_absen" id="jamKeluarInput">
                        <button type="submit" name="keluar" class="btn btn-warning w-100">Absen Keluar</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mt-3">Anda sudah absen hari ini.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Jam Digital -->
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
    updateJamInput(); // update saat pertama kali
</script>

</body>
</html>
