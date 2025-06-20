<?php
// admin/absensi.php
require '../includes/config.php';
require '../includes/auth.php';

// === Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_admin()) {
    header("Location: ../login.php");
    exit;
}

$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$message = '';
$message_type = '';

// Handle hapus data absensi
if (isset($_GET['hapus'])) {
    $id_to_delete = (int) $_GET['hapus'];
    $stmt_delete = $conn->prepare("DELETE FROM absensi WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_to_delete);
        if (mysqli_stmt_execute($stmt_delete)) {
            $message = "Data absensi berhasil dihapus.";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus data absensi: " . mysqli_error($conn);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        $message = "Gagal menyiapkan statement hapus data absensi.";
        $message_type = "danger";
    }
    header("Location: absensi.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

// Ambil pesan dari URL jika ada
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Query untuk mengambil data absensi dengan JOIN ke tabel 'user' yang baru
$query = "
    SELECT 
        absensi.*, 
        user.nama AS nama_karyawan, 
        user.jabatan AS jabatan_karyawan 
    FROM 
        absensi 
    LEFT JOIN 
        user ON absensi.user_id = user.id"; 

$params = [];
$types = '';

if ($tanggal_filter != '') {
    if (strtotime($tanggal_filter)) {
        $query .= " WHERE absensi.tanggal = ?";
        $params[] = $tanggal_filter;
        $types .= 's';
    } else {
        $message = "Format tanggal filter tidak valid.";
        $message_type = "danger";
        $tanggal_filter = '';
    }
}

$query .= " ORDER BY absensi.tanggal DESC, absensi.jam_masuk DESC";

$stmt_select = $conn->prepare($query);

if ($stmt_select === false) {
    die("Error preparing statement: " . htmlspecialchars($conn->error));
}

if (!empty($params)) {
    $stmt_select->bind_param($types, ...$params);
}

$stmt_select->execute();
$result_absensi = $stmt_select->get_result();
mysqli_stmt_close($stmt_select);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>

<div class="container admin-wide">
    <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-calendar-check"></i> Data Absensi Pengguna</h4>
        <p class="text-muted">Kelola dan pantau kehadiran karyawan.</p>
    </div>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <h5 class="card-title mb-3">Filter Absensi</h5>
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-auto">
                <label for="tanggalFilter" class="form-label mb-0">Tanggal</label>
                <input type="date" name="tanggal" id="tanggalFilter" value="<?= htmlspecialchars($tanggal_filter) ?>" class="form-control">
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="absensi.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <h5 class="card-title mb-3">Daftar Absensi Karyawan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_absensi) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result_absensi)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_karyawan'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['jabatan_karyawan'] ?? 'N/A') ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td>
                                    <a href="absensi.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data ini?')" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-3">Tidak ada data absensi yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php /* include '../includes/footer.php'; */ ?>

</body>
</html>