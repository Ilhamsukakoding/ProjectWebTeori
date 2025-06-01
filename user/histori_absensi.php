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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1e1e2f;
            color: #e0e0e0;
            padding-top: 40px;
        }

        .container {
            max-width: 1000px;
        }

        h4 {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #3b82f6;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2563eb;
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
            color: #e0e0e0;
        }

        .form-control:focus {
            background-color: #2a2a3d;
            color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.25);
        }

        .table {
            background-color: #2a2a3d;
            color: #e0e0e0;
        }

        .table thead {
            background-color: #3a3a50;
        }

        .table tbody tr:hover {
            background-color: #3f3f59;
        }

        .table-bordered th,
        .table-bordered td {
            border-color: #444;
        }

        .alert {
            background-color: #3a3a50;
            border: none;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <h4>Histori Absensi</h4>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">← Kembali</a>

    <!-- Form Filter -->
    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <input type="date" name="awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal) ?>" required>
        </div>
        <div class="col-md-4">
            <input type="date" name="akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-50">Filter</button>
            <a href="histori_absensi.php" class="btn btn-secondary w-50">Reset</a>
        </div>
    </form>

    <!-- Tabel -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
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
</div>
</body>
</html>
