<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../dashboard.php");
    exit;
}

// Tambah karyawan
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $email = trim($_POST['email']);

    if ($nama != "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO karyawan (nama, jabatan, email) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $jabatan, $email);
        mysqli_stmt_execute($stmt);

        $username = strtolower(str_replace(' ', '', $nama));
        $default_password = '123456';
        $role = 'user';

        $stmtUser = mysqli_prepare($conn, "INSERT INTO user (username, password, nama, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtUser, 'ssss', $username, $default_password, $nama, $role);
        mysqli_stmt_execute($stmtUser);
    }

    header("Location: karyawan.php");
    exit;
}

// Hapus karyawan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM karyawan WHERE id = $id");
    header("Location: karyawan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Karyawan</title>
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
        .card {
            background-color: #1e1e2f;
            border: none;
        }
        .card-header {
            background-color: #2c2c3a;
            color: #fff;
            font-weight: bold;
        }
        .form-control, .btn {
            border-radius: 10px;
        }
        .form-control {
            background-color: #1e1e2f;
            color: #fff;
            border-color: #333;
        }
        .form-control::placeholder {
            color: #bbb;
        }
        .btn-primary {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }
        .btn-outline-secondary {
            color: #ccc;
            border-color: #444;
        }
        .table {
            background-color: #1f1f2e;
            color: #fff;
        }
        .table-dark {
            background-color: #2c2c3a;
        }
        .table-hover tbody tr:hover {
            background-color: #343447;
        }
        .table-bordered td, .table-bordered th {
            border-color: #444;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-4"><i class="bi bi-person-badge text-info"></i> Data Karyawan</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

    <!-- Form Tambah -->
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-person-plus"></i> Tambah Karyawan</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-2 align-items-end">
                    <div class="col-md">
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
                    </div>
                    <div class="col-md">
                        <input type="text" name="jabatan" class="form-control" placeholder="Jabatan">
                    </div>
                    <div class="col-md">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Karyawan -->
    <div class="table-responsive">
        <table class="table table-dark table-bordered table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY id DESC");
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)):
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['jabatan']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="karyawan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">
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
