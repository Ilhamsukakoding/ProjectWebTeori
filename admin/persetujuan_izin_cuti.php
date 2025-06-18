<?php
session_start(); // Pastikan session_start() ada di includes/config.php, ini hanya jaga-jaga
require '../includes/config.php';
require '../includes/auth.php'; // Pastikan auth.php ada dan punya is_admin()
require '../includes/function.php'; // Panggil file function.php yang berisi fungsi-fungsi absensi/izin

// Cek apakah user sudah login dan levelnya adalah 'admin'
if (!is_admin()) {
    header("Location: ../index.php"); // Arahkan ke halaman login jika tidak valid
    exit;
}

// Pastikan admin_id ada di sesi saat login admin
// $_SESSION['user_id'] diatur di login.php, seharusnya sudah berisi ID user admin dari tabel 'user'
if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php"); // Logout jika ID admin tidak ditemukan (sesi tidak valid)
    exit;
}

$admin_id = $_SESSION['user_id']; // Ambil ID admin dari sesi (ini adalah user.id)
$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; // Ambil username admin dari sesi

$message = '';
$message_type = ''; // 'success' atau 'danger'

// Handle POST request untuk menyetujui/menolak pengajuan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? ''; // 'approve' atau 'reject'
    $catatan_admin = trim($_POST['catatan_admin'] ?? null); // Catatan admin (bisa kosong)

    // Validasi input
    if (empty($request_id) || !in_array($action, ['approve', 'reject'])) {
        $message = "Aksi tidak valid atau ID pengajuan tidak ditemukan.";
        $message_type = "danger";
    } else {
        $status_to_update = ($action == 'approve') ? 'disetujui' : 'ditolak';

        // Panggil fungsi untuk mengupdate status pengajuan
        if (update_leave_request_status($request_id, $status_to_update, $admin_id, $catatan_admin)) {
            $message = "Pengajuan berhasil " . ($status_to_update == 'disetujui' ? 'disetujui' : 'ditolak') . ".";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui status pengajuan. Silakan coba lagi.";
            $message_type = "danger";
        }
    }
}

// Ambil semua pengajuan yang statusnya 'pending' untuk ditampilkan di tabel pertama
$pending_requests = get_all_pending_leave_requests();

// Ambil semua riwayat pengajuan (termasuk yang sudah diproses) untuk ditampilkan di tabel kedua
$all_requests_history = get_all_leave_requests_history();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Izin/Cuti - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
        }
        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            font-size: 1.25rem;
        }
        .btn-outline-dark {
            border-color: #ced4da;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.375rem 0.75rem;
        }
        .btn-outline-dark:hover {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
        .container {
            max-width: 1200px; /* Lebar lebih besar untuk admin */
            padding-top: 70px;
            padding-bottom: 40px;
        }
        h2 {
            color: #0d6efd;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #0d6efd;
            color: #fff;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1rem 1.5rem;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .status-badge-pending { background-color: #ffc107; color: #333; }
        .status-badge-disetujui { background-color: #28a745; color: #fff; }
        .status-badge-ditolak { background-color: #dc3545; color: #fff; }
        .status-badge {
            padding: 0.3em 0.6em;
            border-radius: 0.3rem;
            font-size: 0.85em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            display: inline-block;
        }
        .small-text {
            font-size: 0.85em;
            color: #666;
        }
        .bg-warning-custom { /* Custom background for pending header */
            background-color: #ffc107 !important;
            color: #333 !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <h2><i class="bi bi-calendar2-check"></i> Persetujuan Izin / Cuti</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-warning-custom">
            <i class="bi bi-hourglass-split me-2"></i> Pengajuan Menunggu Persetujuan
        </div>
        <div class="card-body">
            <?php if (empty($pending_requests)): ?>
                <div class="alert alert-info mb-0" role="alert">
                    Tidak ada pengajuan izin/cuti yang menunggu persetujuan.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Karyawan</th>
                                <th scope="col">Jenis</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col">Dokumen</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($pending_requests as $req): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($req['nama_karyawan']) ?></strong>
                                        <br><small class="text-muted">(<?= htmlspecialchars($req['jabatan_karyawan'] ?? 'N/A') ?>)</small>
                                    </td>
                                    <td><?= ucfirst($req['jenis_pengajuan']) ?></td>
                                    <td>
                                        <?= date('d M Y', strtotime($req['tanggal_mulai'])) ?>
                                        <br>s/d
                                        <br><?= date('d M Y', strtotime($req['tanggal_akhir'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars(substr($req['alasan'], 0, 80)) ?><?= (strlen($req['alasan']) > 80) ? '...' : '' ?></td>
                                    <td><?= date('d M Y H:i', strtotime($req['tanggal_pengajuan'])) ?></td>
                                    <td>
                                        <?php if (!empty($req['dokumen_pendukung'])): ?>
                                            <a href="../uploads/izin_cuti/<?= htmlspecialchars($req['dokumen_pendukung']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-download"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm mb-1" onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan ini?');">
                                                <i class="bi bi-check-circle"></i> Setujui
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal" data-request-id="<?= $req['id'] ?>">
                                            <i class="bi bi-x-circle"></i> Tolak
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i> Riwayat Semua Pengajuan
        </div>
        <div class="card-body">
            <?php if (empty($all_requests_history)): ?>
                <div class="alert alert-info mb-0" role="alert">
                    Belum ada riwayat pengajuan izin/cuti.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Karyawan</th>
                                <th scope="col">Jenis</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col">Dokumen</th>
                                <th scope="col">Diproses Oleh</th>
                                <th scope="col">Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($all_requests_history as $req): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($req['nama_karyawan']) ?></strong>
                                        <br><small class="text-muted">(<?= htmlspecialchars($req['jabatan_karyawan'] ?? 'N/A') ?>)</small>
                                    </td>
                                    <td><?= ucfirst($req['jenis_pengajuan']) ?></td>
                                    <td>
                                        <?= date('d M Y', strtotime($req['tanggal_mulai'])) ?>
                                        <br>s/d
                                        <br><?= date('d M Y', strtotime($req['tanggal_akhir'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars(substr($req['alasan'], 0, 80)) ?><?= (strlen($req['alasan']) > 80) ? '...' : '' ?></td>
                                    <td>
                                        <span class="status-badge status-badge-<?= htmlspecialchars($req['status']) ?>">
                                            <?= ucfirst($req['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($req['tanggal_pengajuan'])) ?></td>
                                    <td>
                                        <?php if (!empty($req['dokumen_pendukung'])): ?>
                                            <a href="../uploads/izin_cuti/<?= htmlspecialchars($req['dokumen_pendukung']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-download"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($req['status'] != 'pending'): ?>
                                            <?= htmlspecialchars($req['admin_username'] ?? 'N/A') ?>
                                            <br><small class="text-muted">(<?= date('d M Y H:i', strtotime($req['tanggal_persetujuan'])) ?>)</small>
                                        <?php else: ?>
                                            Belum Diproses
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($req['catatan_admin'])): ?>
                                            <span class="small-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($req['catatan_admin']) ?>">
                                                Lihat Catatan
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">Tolak Pengajuan Izin/Cuti</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="modalRequestId">
                    <p>Anda akan menolak pengajuan ini. Berikan catatan jika perlu:</p>
                    <div class="mb-3">
                        <label for="catatanAdmin" class="form-label">Catatan Admin (Opsional)</label>
                        <textarea class="form-control" id="catatanAdmin" name="catatan_admin" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script untuk mengisi ID pengajuan ke modal tolak
    var rejectModal = document.getElementById('rejectModal');
    rejectModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var requestId = button.getAttribute('data-request-id');
        var modalRequestId = rejectModal.querySelector('#modalRequestId');
        modalRequestId.value = requestId;
    });

    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>