<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_user()) {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal = date('Y-m-d');

$query = "SELECT * FROM absensi WHERE user_id = ? AND tanggal = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'is', $user_id, $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

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

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
        }

        main {
            flex: 1;
        }

        .container {
            max-width: 600px;
            padding-top: 60px;
        }

        h4 {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 20px;
        }

        #jamSekarang {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }

        .card:hover {
            transform: scale(1.01);
            transition: 0.3s;
        }

        .alert {
            border-radius: 0.5rem;
        }

        .btn {
            border-radius: 0.5rem;
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
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            font-size: 0.9rem;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>

<main class="container">
    <h4>Absensi Hari Ini (<?= date('d-m-Y') ?>)</h4>
    <div class="mb-3">Jam sekarang: <span id="jamSekarang">--:--:--</span></div>

    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">← Kembali</a>

    <div class="card p-4">
        <?php if (!$data): ?>
            <!-- Form Absen Masuk -->
            <form method="POST" id="formMasuk">
                <input type="hidden" name="jam_absen" id="jamMasukInput">
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <select name="keterangan" id="keterangan" class="form-select" required>
                        <option value="" disabled selected>Pilih keterangan</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Tidak Hadir">Tidak Hadir</option>
                    </select>
                </div>
                <button type="submit" name="masuk" class="btn btn-success w-100">Absen Masuk</button>
            </form>
        <?php else: ?>
            <!-- Tampilkan jam masuk/keluar -->
            <p><strong>Jam Masuk:</strong> <?= $data['jam_masuk'] ?? '-' ?></p>
            <p><strong>Jam Keluar:</strong> <?= $data['jam_keluar'] ?? '-' ?></p>

            <?php if (!$data['jam_keluar']): ?>
                <form method="POST" id="formKeluar">
                    <input type="hidden" name="jam_absen" id="jamKeluarInput">
                    <button type="submit" name="keluar" class="btn btn-warning w-100">Absen Keluar</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-3">Anda sudah absen hari ini.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Jam Real-time -->
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

<?php include '../includes/footer.php'; ?>

</body>
</html>
