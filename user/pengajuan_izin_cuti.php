
<?php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/function.php';

if (!is_user()) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../logout.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$nama_karyawan = get_karyawan_name($user_id); // Untuk display di navbar atau welcome

$message = '';
$message_type = ''; // 'success' atau 'danger'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_pengajuan = $_POST['jenis_pengajuan'] ?? '';
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_akhir = $_POST['tanggal_akhir'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $dokumen_pendukung = null;

    // Validasi input
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
            $upload_dir = '../uploads/izin_cuti/'; // Pastikan folder ini ada dan writable!
            
            // Buat folder jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = uniqid('doc_') . '_' . basename($_FILES['dokumen_pendukung']['name']);
            $target_file = $upload_dir . $file_name;
            $file_type = mime_content_type($_FILES['dokumen_pendukung']['tmp_name']);

            if (!in_array($file_type, $allowed_types)) {
                $message = "Format file tidak diizinkan. Hanya PDF, JPG, PNG.";
                $message_type = "danger";
            } elseif ($_FILES['dokumen_pendukung']['size'] > $max_size) {
                $message = "Ukuran file terlalu besar (maks 5MB).";
                $message_type = "danger";
            } else {
                if (move_uploaded_file($_FILES['dokumen_pendukung']['tmp_name'], $target_file)) {
                    $dokumen_pendukung = $file_name;
                } else {
                    $message = "Gagal mengunggah dokumen.";
                    $message_type = "danger";
                }
            }
        }

        // Jika tidak ada error upload atau upload tidak dilakukan
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
}

// Ambil riwayat pengajuan untuk ditampilkan
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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
        }
       /* NAVBAR - versi ramping & cerah */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 0.3rem 0.8rem; 
            height: 90px;
            display: flex;
            align-items: center;
        }

        .navbar-brand {
            font-weight: 600;
            color: #0d6efd !important;
            font-size: 1.1rem;
            line-height: 1;
            margin: 0;
            padding-top: 0.2rem;
        }



        .btn-outline-dark {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.3rem 0.8rem;
            border-radius: 0.4rem;
            border-color: #ced4da;
            color: #333;
            display: flex;
            align-items: center;
        }


        .btn-outline-dark:hover {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        }

        .container {
            max-width: 1000px;
            padding-top: 100px;
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
        .form-label {
            font-weight: 600;
            color: #555;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
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

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="dashboard.php">Absensi Karyawan</a>
        <a href="../logout.php" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</nav>



<div class="container">
    <h2><i class="bi bi-file-earmark-text"></i> Pengajuan Izin / Cuti</h2>

    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-4">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

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
                    <table class="table table-striped table-hover">
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

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
</body>
</html>