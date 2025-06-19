<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_user()) {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: #333;
    }

    .container {
      max-width: 1000px;
    }

    h4 {
      color: #0d6efd;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .btn {
      border-radius: 0.5rem;
    }

    .btn-primary {
      background-color: #0d6efd;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .card {
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1rem;
      box-shadow: 0 4px 16px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }

    .form-control {
      border-radius: 0.5rem;
    }

    .table {
      background-color: #ffffff;
      border-radius: 0.5rem;
    }

    .table th, .table td {
      vertical-align: middle;
    }

    footer {
      background-color: #f1f1f1;
      text-align: center;
      padding: 16px 0;
      margin-top: auto;
    }
  </style>
</head>
<body>
<div class="d-flex flex-column min-vh-100">

  <nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand text-primary fw-bold" href="#">Histori Absensi</a>
      <a href="dashboard.php" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
  </nav>

  <div class="container flex-grow-1 mt-5 pt-4">
    <!-- Filter -->
    <div class="card p-4">
      <form class="row g-3" method="GET">
        <div class="col-md-4">
          <label class="form-label">Tanggal Awal</label>
          <input type="date" name="awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary w-50">Filter</button>
          <a href="histori_absensi.php" class="btn btn-secondary w-50">Reset</a>
        </div>
      </form>
    </div>

    <!-- Tabel -->
    <div class="card p-4">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
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
              <tr>
                <td colspan="4" class="text-center">Tidak ada data absensi.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
