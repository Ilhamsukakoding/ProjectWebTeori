<?php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!is_user()) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_details = get_user_details($user_id); 
$nama_karyawan = $user_details['nama']; 

$message = '';
$message_type = '';

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_pengajuan = $_POST['jenis_pengajuan'] ?? '';
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_akhir = $_POST['tanggal_akhir'] ?? '';
    $alasan = trim($_POST['alasan'] ?? ''); 
    $dokumen_pendukung = null;

    if (empty($jenis_pengajuan) || empty($tanggal_mulai) || empty($tanggal_akhir) || empty($alasan)) {
        $message = "Semua kolom wajib diisi kecuali dokumen pendukung.";
        $message_type = "danger";
    } elseif (strtotime($tanggal_mulai) > strtotime($tanggal_akhir)) {
        $message = "Tanggal mulai tidak boleh lebih dari tanggal berakhir.";
        $message_type = "danger";
    } else {
        // Handle upload dokumen pendukung
        if (isset($_FILES['dokumen_pendukung']) && $_FILES['dokumen_pendukung']['error'] == 0) {
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5 MB
            $upload_dir = '../uploads/izin_cuti/'; 
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $_FILES['dokumen_pendukung']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($file_type, $allowed_types)) {
                $message = "Format file tidak diizinkan. Hanya PDF, JPG, PNG.";
                $message_type = "danger";
            } elseif ($_FILES['dokumen_pendukung']['size'] > $max_size) {
                $message = "Ukuran file terlalu besar (maks 5MB).";
                $message_type = "danger";
            } else {
                $file_extension = pathinfo($_FILES['dokumen_pendukung']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('doc_') . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['dokumen_pendukung']['tmp_name'], $target_file)) {
                    $dokumen_pendukung = $file_name;
                } else {
                    $message = "Gagal mengunggah dokumen.";
                    $message_type = "danger";
                }
            }
        }

        if ($message_type !== "danger") {
            if (submit_leave_request($user_id, $jenis_pengajuan, $tanggal_mulai, $tanggal_akhir, $alasan, $dokumen_pendukung)) {
                $message = "Pengajuan berhasil dikirim! Menunggu persetujuan admin.";
                $message_type = "success";
            } else {
                $message = "Gagal mengirim pengajuan. Silakan coba lagi.";
                $message_type = "danger";
            }
        }
    }
    header("Location: pengajuan_izin_cuti.php?msg=".urlencode($message)."&type=".urlencode($message_type));
    exit;
}

$riwayat_pengajuan = get_user_leave_requests($user_id);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Izin/Cuti - Absensi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet"> </head>
<body>

<div class="container">
    <div class="mb-4 page-header">
        <h4 class="text-primary"><i class="bi bi-file-earmark-text"></i> Pengajuan Izin / Cuti</h4>
        <p class="text-muted">Ajukan permohonan izin atau cuti Anda di sini.</p>
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
        <div class="card-header">
            Formulir Pengajuan
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="jenis_pengajuan" class="form-label">Jenis Pengajuan</label>
                    <select class="form-select" id="jenis_pengajuan" name="jenis_pengajuan" required>
                        <option value="">Pilih Jenis</option>
                        <option value="izin">Izin</option>
                        <option value="cuti">Cuti</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akhir" class="form-label">Tanggal Berakhir</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="alasan" class="form-label">Alasan</label>
                    <textarea class="form-control" id="alasan" name="alasan" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="dokumen_pendukung" class="form-label">Dokumen Pendukung (PDF, JPG, PNG - Maks 5MB)</label>
                    <input type="file" class="form-control" id="dokumen_pendukung" name="dokumen_pendukung" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text small-text">Contoh: Surat Dokter untuk izin sakit, dll.</div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-2"></i>Kirim Pengajuan</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Riwayat Pengajuan Anda
        </div>
        <div class="card-body">
            <?php if (empty($riwayat_pengajuan)): ?>
                <div class="alert alert-info mb-0" role="alert">
                    Anda belum memiliki riwayat pengajuan izin atau cuti.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Jenis</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($riwayat_pengajuan as $req): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= ucfirst($req['jenis_pengajuan']) ?></td>
                                    <td><?= date('d M Y', strtotime($req['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($req['tanggal_akhir'])) ?></td>
                                    <td><?= htmlspecialchars(substr($req['alasan'], 0, 50)) ?><?= (strlen($req['alasan']) > 50) ? '...' : '' ?></td>
                                    <td>
                                        <span class="status-badge status-badge-<?= htmlspecialchars($req['status']) ?>">
                                            <?= ucfirst($req['status']) ?>
                                        </span>
                                        <?php if ($req['status'] == 'ditolak' && !empty($req['catatan_admin'])): ?>
                                            <br><small class="text-danger small-text" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($req['catatan_admin']) ?>">
                                                (Catatan Admin)
                                            </small>
                                        <?php endif; ?>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>