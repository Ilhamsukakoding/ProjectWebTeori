<?php
require '../includes/config.php';
require '../includes/auth.php';

if (!is_admin()) {
    header("Location: ../dashboard.php");
    exit;
}

// Filter berdasarkan tanggal jika ada
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$query = "SELECT absensi.*, user.username FROM absensi 
          JOIN user ON absensi.user_id = user.id";

if ($tanggal_filter != '') {
    $tanggal_filter_escaped = mysqli_real_escape_string($conn, $tanggal_filter);
    $query .= " WHERE tanggal = '$tanggal_filter_escaped'";
}

$query .= " ORDER BY tanggal DESC, jam_masuk DESC";
$result = mysqli_query($conn, $query);

// Hapus data
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
</head>
<body>
<div class="container mt-4">
    <h3>Data Absensi Pengguna</h3>
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-2">← Kembali</a>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>" class="form-control">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filter</button>
            <a href="absensi.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
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
                    <a href="absensi.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
