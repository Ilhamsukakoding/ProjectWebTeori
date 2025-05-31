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
        // 1. Tambah ke tabel karyawan
        $stmt = mysqli_prepare($conn, "INSERT INTO karyawan (nama, jabatan, email) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $jabatan, $email);
        mysqli_stmt_execute($stmt);

        // 2. Tambah otomatis ke tabel user (akun login)
        $username = strtolower(str_replace(' ', '', $nama)); // contoh: Budi Santoso -> budisantoso
        $default_password = '123456'; // Password default, bisa diganti
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

    // Hapus dari tabel karyawan
    mysqli_query($conn, "DELETE FROM karyawan WHERE id = $id");

    // Opsional: Hapus juga dari tabel user berdasarkan nama
    // (Anda bisa tambahkan kolom id_karyawan di tabel user agar lebih akurat)
    // mysqli_query($conn, "DELETE FROM user WHERE nama = (SELECT nama FROM karyawan WHERE id = $id)");

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
</head>
<body>
<div class="container mt-4">
    <h3>Data Karyawan</h3>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>

    <!-- Form Tambah -->
    <div class="card mb-3">
        <div class="card-header">Tambah Karyawan</div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
                    </div>
                    <div class="col">
                        <input type="text" name="jabatan" class="form-control" placeholder="Jabatan">
                    </div>
                    <div class="col">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Karyawan -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
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
                    <a href="karyawan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
