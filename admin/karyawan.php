<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../dashboard.php");
    exit;
}

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
            background-color: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
            padding: 30px 0;
        }

        .container {
            max-width: 1000px;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }

        .card-header {
            background-color: #e9f2ff;
            color: #0d6efd;
            font-weight: 600;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .form-control, .btn {
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
            background-color: #e74c3c;
            border-color: #e74c3c;
            border-radius: 0.5rem;
        }

        .table thead {
            background-color: #e9f2ff;
            color: #0d6efd;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .form-control::placeholder {
            color: #999;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="mb-4 text-center">
        <h4 class="fw-bold text-primary"><i class="bi bi-person-badge"></i> Data Karyawan</h4>
        <p class="text-muted">Kelola daftar karyawan dan informasi dasar mereka</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <!-- Form Tambah Karyawan -->
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
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
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
</div>

</body>
</html>
