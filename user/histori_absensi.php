<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_user()) {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses filter tanggal
$tanggal_awal = $_GET['awal'] ?? '';
$tanggal_akhir = $_GET['akhir'] ?? '';

$query = "SELECT * FROM absensi WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND tanggal BETWEEN ? AND ?";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
    $types .= 'ss';
}

$query .= " ORDER BY tanggal DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Histori Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h4>Histori Absensi</h4>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>

    <!-- Form Filter Tanggal -->
    <form class="row g-2 mb-3" method="GET">
        <div class="col-md-4">
            <input type="date" name="awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal) ?>" required>
        </div>
        <div class="col-md-4">
            <input type="date" name="akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="histori_absensi.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Tabel Riwayat -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
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
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= $row['jam_masuk'] ?? '-' ?></td>
                    <td><?= $row['jam_keluar'] ?? '-' ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">Tidak ada data absensi.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
