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
        // Ambil jam dari input hidden yang dikirim client
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #jamSekarang {
            font-size: 1.25rem;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h4>Absensi Hari Ini (<?= date('d-m-Y') ?>)</h4>
    <div class="mb-3">Jam sekarang: <span id="jamSekarang">--:--:--</span></div>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>

    <div class="card">
        <div class="card-body">
            <?php if (!$data): ?>
                <form method="POST" id="formMasuk">
                    <input type="hidden" name="jam_absen" id="jamMasukInput">
                    <div class="mb-3">
                        <label>Keterangan (opsional)</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: WFH, Kantor, dll.">
                    </div>
                    <button type="submit" name="masuk" class="btn btn-success">Absen Masuk</button>
                </form>
            <?php else: ?>
                <p><strong>Jam Masuk:</strong> <?= htmlspecialchars($data['jam_masuk']) ?? '-' ?></p>
                <p><strong>Jam Keluar:</strong> <?= htmlspecialchars($data['jam_keluar']) ?? '-' ?></p>
                <?php if (!$data['jam_keluar']): ?>
                    <form method="POST" id="formKeluar">
                        <input type="hidden" name="jam_absen" id="jamKeluarInput">
                        <button type="submit" name="keluar" class="btn btn-warning">Absen Keluar</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">Anda sudah absen hari ini.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

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
    updateJamInput(); // update langsung saat halaman load
</script>

</body>
</html>
