<?php
// admin/kelola_pengumuman.php
require '../includes/config.php';
require '../includes/auth.php';

// === Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_admin()) {
    header("Location: ../login.php");
    exit;
}

// Tambah pengumuman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isi_pengumuman'])) {
    $isi = trim($_POST['isi_pengumuman'] ?? '');
    if (!empty($isi)) {
        $stmt = $conn->prepare("INSERT INTO pengumuman (isi, tanggal) VALUES (?, NOW())");
        if ($stmt) {
            $stmt->bind_param("s", $isi);
            if ($stmt->execute()) {
                // Pesan sukses opsional
            } else {
                // Pesan error opsional
            }
            $stmt->close();
        }
    }
    header("Location: kelola_pengumuman.php"); // Redirect untuk mencegah resubmission
    exit;
}

// Hapus pengumuman
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_to_delete = (int)$_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM pengumuman WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            // Pesan sukses opsional
        } else {
            // Pesan error opsional
        }
        $stmt->close();
    }
    header("Location: kelola_pengumuman.php"); // Redirect setelah hapus
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
    <title>Kelola Pengumuman - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container">
    <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-megaphone-fill"></i> Kelola Pengumuman</h4>
        <p class="text-muted">Buat, lihat, dan hapus pengumuman untuk karyawan.</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="card mb-4 p-4">
        <h5 class="card-title mb-3">Tulis Pengumuman Baru:</h5>
        <form method="post">
            <div class="mb-3">
                <label for="isi_pengumuman" class="form-label visually-hidden">Tulis Pengumuman Baru:</label>
                <textarea class="form-control" id="isi_pengumuman" name="isi_pengumuman" rows="4" placeholder="Tulis isi pengumuman di sini..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i> Tambah Pengumuman</button>
        </form>
    </div>

    <div class="card p-4">
        <h5 class="card-title mb-3">Daftar Pengumuman:</h5>
        <ul class="list-group">
            <?php if (empty($pengumuman)): ?>
                <li class="list-group-item text-center text-muted">Belum ada pengumuman.</li>
            <?php else: ?>
                <?php foreach ($pengumuman as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= htmlspecialchars($p['isi']) ?></strong><br>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('d M Y H:i', strtotime($p['tanggal'])) ?></small>
                        </div>
                        <a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')" title="Hapus Pengumuman">
                            <i class="bi bi-trash"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php /* include '../includes/footer.php'; */ ?>

</body>
</html>