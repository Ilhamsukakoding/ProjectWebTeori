<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../dashboard.php");
    exit;
}

$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$query = "SELECT absensi.*, user.username FROM absensi 
          JOIN user ON absensi.user_id = user.id";

if ($tanggal_filter != '') {
    $tanggal_filter_escaped = mysqli_real_escape_string($conn, $tanggal_filter);
    $query .= " WHERE tanggal = '$tanggal_filter_escaped'";
}

$query .= " ORDER BY tanggal DESC, jam_masuk DESC";
$result = mysqli_query($conn, $query);

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM absensi WHERE id = $id");
    header("Location: absensi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f1117;
            color: #f1f1f1;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 40px;
        }
        .btn, .form-control {
            border-radius: 10px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table-dark {
            background-color: #1f1f2e;
        }
        .table-hover tbody tr:hover {
            background-color: #2c2c3a;
        }
        .btn-primary {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
        }
        .btn-outline-secondary {
            color: #ccc;
            border-color: #444;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }
        .table-bordered td, .table-bordered th {
            border-color: #333;
        }
        input[type="date"] {
            background-color: #1e1e2f;
            color: #fff;
            border-color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4"><i class="bi bi-calendar-check text-info"></i> Data Absensi Pengguna</h3>

    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

    <form method="GET" class="row g-2 align-items-center mb-4">
        <div class="col-auto">
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>" class="form-control text-light">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="absensi.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-dark table-bordered table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)):
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['jam_masuk'] ?></td>
                    <td><?= $row['jam_keluar'] ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                    <td>
                        <a href="absensi.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
