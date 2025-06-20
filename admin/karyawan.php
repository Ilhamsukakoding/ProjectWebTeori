<?php
// admin/karyawan.php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php'; 

// === Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_admin()) {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

// Handle Tambah Karyawan
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nama !== "") {
        $username = strtolower(str_replace(' ', '', $nama)); 
        $default_password = '123456'; 
        $role = 'user'; 

        // Cek apakah username atau email sudah ada
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = "Gagal menambahkan karyawan: Username atau Email sudah terdaftar.";
            $message_type = "danger";
        } else {
            // INSERT ke tabel 'user' (yang sekarang berisi semua data user/karyawan)
            $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, nama, jabatan, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $username, $default_password, $nama, $jabatan, $email, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Karyawan baru berhasil ditambahkan!";
                    $message_type = "success";
                } else {
                    $message = "Gagal menambahkan karyawan: " . mysqli_error($conn);
                    $message_type = "danger";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "Gagal menyiapkan statement tambah karyawan.";
                $message_type = "danger";
            }
        }
    } else {
        $message = "Nama lengkap wajib diisi.";
        $message_type = "danger";
    }
    header("Location: karyawan.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

// Handle Hapus Karyawan
if (isset($_GET['hapus'])) {
    $id_to_delete = (int) $_GET['hapus'];
    // Hapus dari tabel 'user' (yang akan menghapus karyawan & akun loginnya)
    // Pastikan hanya role 'user' yang bisa dihapus lewat sini untuk mencegah admin terhapus
    $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ? AND role = 'user'");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_to_delete);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Data karyawan berhasil dihapus!";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus karyawan: " . mysqli_error($conn);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Gagal menyiapkan statement hapus karyawan.";
        $message_type = "danger";
    }
    header("Location: karyawan.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

// Ambil data karyawan (hanya yang role='user') dari tabel `user`
$query_select_karyawan = "SELECT id, nama, jabatan, email FROM user WHERE role = 'user' ORDER BY nama ASC";
$result_karyawan = mysqli_query($conn, $query_select_karyawan);

// Ambil pesan dari URL jika ada
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Karyawan - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container admin-wide"> <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-person-badge"></i> Kelola Data Karyawan</h4>
        <p class="text-muted">Tambah, lihat, atau hapus informasi karyawan.</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 p-4">
        <h5 class="card-title mb-3"><i class="bi bi-person-plus"></i> Tambah Karyawan</h5>
        <form method="POST">
            <div class="row g-2 align-items-end">
                <div class="col-md">
                    <label for="nama" class="form-label visually-hidden">Nama lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap" required>
                </div>
                <div class="col-md">
                    <label for="jabatan" class="form-label visually-hidden">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" class="form-control" placeholder="Jabatan">
                </div>
                <div class="col-md">
                    <label for="email" class="form-label visually-hidden">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-auto">
                    <button type="submit" name="tambah" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <h5 class="card-title mb-3">Daftar Karyawan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center mb-0">
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
                if (mysqli_num_rows($result_karyawan) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result_karyawan)):
                ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                            <td>
                                <a href="karyawan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                <?php
                    endwhile;
                } else {
                    echo '<tr><td colspan="5" class="text-center py-3">Tidak ada data karyawan.</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>