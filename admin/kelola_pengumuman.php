<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../index.php");
    exit;
}

// Tambah pengumuman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isi_pengumuman'])) {
    $isi = trim($_POST['isi_pengumuman']);
    if (!empty($isi)) {
        $stmt = $conn->prepare("INSERT INTO pengumuman (isi) VALUES (?)");
        $stmt->bind_param("s", $isi);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: kelola_pengumuman.php");
    exit;
}

// Hapus pengumuman
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $stmt = $conn->prepare("DELETE FROM pengumuman WHERE id = ?");
    $stmt->bind_param("i", $_GET['hapus']);
    $stmt->execute();
    $stmt->close();
    header("Location: kelola_pengumuman.php");
    exit;
}

// Ambil semua pengumuman
$pengumuman = [];
$result = $conn->query("SELECT * FROM pengumuman ORDER BY tanggal DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pengumuman[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengumuman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Kelola Pengumuman</h2>

    <form method="post" class="mb-4">
        <div class="mb-3">
            <label for="isi_pengumuman" class="form-label">Tulis Pengumuman Baru:</label>
            <textarea class="form-control" id="isi_pengumuman" name="isi_pengumuman" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Tambah Pengumuman</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>

    <h5>Daftar Pengumuman:</h5>
    <ul class="list-group">
        <?php foreach ($pengumuman as $p): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                    <?= htmlspecialchars($p['isi']) ?><br>
                    <small class="text-muted"><?= $p['tanggal'] ?></small>
                </div>
                <a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pengumuman ini?')">
                    Hapus
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</body>
</html>
