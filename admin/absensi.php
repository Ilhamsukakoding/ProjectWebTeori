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
            background-color: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
            padding: 30px 0;
        }

        .container {
            max-width: 1000px;
        }

        .btn, .form-control {
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #ced4da;
        }

        .btn-danger {
            border-radius: 0.5rem;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            background-color: #ffffff;
        }

        .table thead {
            background-color: #e9f2ff;
            color: #0d6efd;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        input[type="date"] {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="mb-4 text-center">
        <h4 class="fw-bold text-primary"><i class="bi bi-calendar-check"></i> Data Absensi Pengguna</h4>
        <p class="text-muted">Kelola dan pantau kehadiran karyawan</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label class="form-label mb-0">Filter Tanggal</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>" class="form-control">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="absensi.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
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
</div>

</body>
</html>
