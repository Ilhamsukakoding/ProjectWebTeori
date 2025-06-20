<?php
// admin/persetujuan_izin_cuti.php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

// === PENTING: Mengontrol Cache Browser ===
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// ==========================================

if (!is_admin()) {
    header("Location: ../login.php");
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

// Handle POST request untuk menyetujui/menolak pengajuan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');
    if (empty($catatan_admin)) {
        $catatan_admin = null;
    }

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
    header("Location: persetujuan_izin_cuti.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

// Ambil pesan dari URL jika ada
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

$pending_requests = get_all_pending_leave_requests();
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
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container admin-wide"> <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-calendar2-check"></i> Persetujuan Izin / Cuti</h4>
        <p class="text-muted">Kelola dan pantau pengajuan izin serta cuti karyawan.</p>
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
                    <table class="table table-bordered table-striped table-hover">
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
                                        <strong><?= htmlspecialchars($req['nama_karyawan'] ?? 'N/A') ?></strong>
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
                    <table class="table table-bordered table-striped table-hover">
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
                                        <strong><?= htmlspecialchars($req['nama_karyawan'] ?? 'N/A') ?></strong>
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
    var rejectModal = document.getElementById('rejectModal');
    rejectModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var requestId = button.getAttribute('data-request-id');
        var modalRequestId = rejectModal.querySelector('#modalRequestId');
        modalRequestId.value = requestId;
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>