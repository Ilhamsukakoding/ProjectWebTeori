<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

if (!is_admin()) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $catatan_admin = trim($_POST['catatan_admin'] ?? null);

    if (empty($request_id) || !in_array($action, ['approve', 'reject'])) {
        $message = "Aksi tidak valid atau ID pengajuan tidak ditemukan.";
        $message_type = "danger";
    } else {
        $status_to_update = ($action == 'approve') ? 'disetujui' : 'ditolak';
        if (update_leave_request_status($request_id, $status_to_update, $admin_id, $catatan_admin)) {
            $message = "Pengajuan berhasil " . ($status_to_update == 'disetujui' ? 'disetujui' : 'ditolak') . ".";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui status pengajuan. Silakan coba lagi.";
            $message_type = "danger";
        }
    }
}

$pending_requests = get_all_pending_leave_requests();
$all_requests_history = get_all_leave_requests_history();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Izin/Cuti</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f8fafc;
            color: #333;
        }

        main {
            flex: 1;
            padding-top: 80px;
            padding-bottom: 40px;
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
        }

        .navbar-brand {
            font-weight: bold;
            color: #0d6efd !important;
        }

        footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px 0;
            font-size: 0.9rem;
            color: #555;
            margin-top: auto;
        }

        h2 {
            color: #0d6efd;
            font-weight: 700;
        }

        .status-badge {
            padding: 0.3em 0.6em;
            border-radius: 0.3rem;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-badge-pending { background-color: #ffc107; color: #333; }
        .status-badge-disetujui { background-color: #28a745; color: #fff; }
        .status-badge-ditolak { background-color: #dc3545; color: #fff; }

        .bg-warning-custom {
            background-color: #ffc107 !important;
            color: #333 !important;
        }

        .small-text {
            font-size: 0.85em;
            color: #666;
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

<main>
    <div class="container">
        <h2 class="mb-3"><i class="bi bi-calendar2-check"></i> Persetujuan Izin / Cuti</h2>

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

        <!-- Pending -->
        <div class="card mb-4">
            <div class="card-header bg-warning-custom">
                <i class="bi bi-hourglass-split me-2"></i> Pengajuan Menunggu Persetujuan
            </div>
            <div class="card-body">
                <?php if (empty($pending_requests)): ?>
                    <div class="alert alert-info mb-0">Tidak ada pengajuan menunggu persetujuan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th><th>Karyawan</th><th>Jenis</th><th>Tanggal</th>
                                    <th>Alasan</th><th>Diajukan</th><th>Dokumen</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pending_requests as $req): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($req['nama_karyawan']) ?></strong><br><small><?= htmlspecialchars($req['jabatan_karyawan']) ?></small></td>
                                        <td><?= ucfirst($req['jenis_pengajuan']) ?></td>
                                        <td><?= date('d M Y', strtotime($req['tanggal_mulai'])) ?> <br>s/d<br> <?= date('d M Y', strtotime($req['tanggal_akhir'])) ?></td>
                                        <td><?= htmlspecialchars(substr($req['alasan'], 0, 80)) ?><?= strlen($req['alasan']) > 80 ? '...' : '' ?></td>
                                        <td><?= date('d M Y H:i', strtotime($req['tanggal_pengajuan'])) ?></td>
                                        <td>
                                            <?php if (!empty($req['dokumen_pendukung'])): ?>
                                                <a href="../uploads/izin_cuti/<?= htmlspecialchars($req['dokumen_pendukung']) ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="bi bi-download"></i> Lihat</a>
                                            <?php else: ?>-<?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm mb-1" onclick="return confirm('Yakin menyetujui?')">
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

        <!-- Riwayat -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Riwayat Semua Pengajuan
            </div>
            <div class="card-body">
                <?php if (empty($all_requests_history)): ?>
                    <div class="alert alert-info mb-0">Belum ada riwayat pengajuan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th><th>Karyawan</th><th>Jenis</th><th>Tanggal</th><th>Alasan</th>
                                    <th>Status</th><th>Diajukan</th><th>Dokumen</th><th>Diproses Oleh</th><th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($all_requests_history as $req): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($req['nama_karyawan']) ?></strong><br><small><?= htmlspecialchars($req['jabatan_karyawan']) ?></small></td>
                                        <td><?= ucfirst($req['jenis_pengajuan']) ?></td>
                                        <td><?= date('d M Y', strtotime($req['tanggal_mulai'])) ?> <br>s/d<br> <?= date('d M Y', strtotime($req['tanggal_akhir'])) ?></td>
                                        <td><?= htmlspecialchars(substr($req['alasan'], 0, 80)) ?><?= strlen($req['alasan']) > 80 ? '...' : '' ?></td>
                                        <td><span class="status-badge status-badge-<?= htmlspecialchars($req['status']) ?>"><?= ucfirst($req['status']) ?></span></td>
                                        <td><?= date('d M Y H:i', strtotime($req['tanggal_pengajuan'])) ?></td>
                                        <td>
                                            <?php if (!empty($req['dokumen_pendukung'])): ?>
                                                <a href="../uploads/izin_cuti/<?= htmlspecialchars($req['dokumen_pendukung']) ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="bi bi-download"></i> Lihat</a>
                                            <?php else: ?>-<?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($req['status'] != 'pending'): ?>
                                                <?= htmlspecialchars($req['admin_username'] ?? 'N/A') ?><br><small><?= date('d M Y H:i', strtotime($req['tanggal_persetujuan'])) ?></small>
                                            <?php else: ?>Belum Diproses<?php endif; ?>
                                        </td>
                                        <td><?= !empty($req['catatan_admin']) ? htmlspecialchars($req['catatan_admin']) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tolak -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Tolak Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="request_id" id="modalRequestId">
                <label class="form-label">Catatan Penolakan (opsional):</label>
                <textarea name="catatan_admin" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" name="action" value="reject" class="btn btn-danger">Tolak Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const rejectModal = document.getElementById('rejectModal');
    rejectModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const requestId = button.getAttribute('data-request-id');
        const input = rejectModal.querySelector('#modalRequestId');
        input.value = requestId;
    });
</script>
</body>
</html>
