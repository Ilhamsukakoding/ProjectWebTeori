<?php
// user/histori_absensi.php
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
$tanggal_awal = $_GET['awal'] ?? '';
$tanggal_akhir = $_GET['akhir'] ?? '';

$query = "SELECT * FROM absensi WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    if (strtotime($tanggal_awal) && strtotime($tanggal_akhir) && $tanggal_awal <= $tanggal_akhir) {
        $query .= " AND tanggal BETWEEN ? AND ?";
        $params[] = $tanggal_awal;
        $params[] = $tanggal_akhir;
        $types .= 'ss';
    } else {
        $tanggal_awal = '';
        $tanggal_akhir = '';
    }
}

$query .= " ORDER BY tanggal DESC";

$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die("Error preparing statement: " . htmlspecialchars($conn->error));
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absensi - Karyawan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container">
    <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-list-columns-reverse"></i> Riwayat Absensi</h4>
        <p class="text-muted">Lihat dan filter riwayat absensi Anda.</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="card p-4 mb-4">
        <form class="row g-3" method="GET">
            <div class="col-md-4">
                <label for="tanggalAwal" class="form-label">Tanggal Awal</label>
                <input type="date" name="awal" id="tanggalAwal" class="form-control" value="<?= htmlspecialchars($tanggal_awal) ?>">
            </div>
            <div class="col-md-4">
                <label for="tanggalAkhir" class="form-label">Tanggal Akhir</label>
                <input type="date" name="akhir" id="tanggalAkhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-50"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="histori_absensi.php" class="btn btn-secondary w-50"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-3">Tidak ada data absensi dalam rentang tanggal ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>