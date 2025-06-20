<?php

/**
 * Fungsi untuk mendapatkan status absensi karyawan pada hari ini.
 * Sekarang merujuk ke tabel 'user' yang baru.
 *
 * @param int $user_id ID user (dari tabel 'user' gabungan)
 * @return array Array asosiatif berisi status, jam masuk, jam pulang, dan kemampuan checkout.
 */
function get_today_attendance_status($user_id) {
    global $conn;

    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT jam_masuk, jam_keluar FROM absensi WHERE user_id = ? AND tanggal = ?");
    
    if ($stmt === false) {
        error_log("Failed to prepare get_today_attendance_status statement: " . $conn->error);
        return ['status' => 'error', 'message' => 'Database query preparation failed.'];
    }

    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    $status_data = [
        'status' => 'belum',
        'time' => null,
        'can_checkout' => false
    ];

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['jam_keluar'])) {
            $status_data = [
                'status' => 'pulang',
                'time_in' => $row['jam_masuk'],
                'time_out' => $row['jam_keluar'],
                'can_checkout' => false
            ];
        } else {
            $status_data = [
                'status' => 'masuk',
                'time' => $row['jam_masuk'],
                'can_checkout' => true
            ];
        }
    }
    
    $stmt->close();
    return $status_data;
}

/**
 * Fungsi untuk mendapatkan nama dan jabatan karyawan berdasarkan user_id.
 * Sekarang merujuk ke tabel 'user' yang baru.
 *
 * @param int $user_id ID user (dari tabel 'user' gabungan)
 * @return array Array asosiatif berisi nama dan jabatan, atau default jika tidak ditemukan.
 */
function get_user_details($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT nama, jabatan FROM user WHERE id = ?"); // Mengacu ke tabel 'user'
    if ($stmt === false) {
        error_log("Failed to prepare get_user_details statement: " . $conn->error);
        return ['nama' => 'Karyawan', 'jabatan' => 'N/A'];
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = ['nama' => 'Karyawan', 'jabatan' => 'N/A'];
    if ($row = $result->fetch_assoc()) {
        $details['nama'] = htmlspecialchars($row['nama']);
        $details['jabatan'] = htmlspecialchars($row['jabatan'] ?? 'N/A');
    }
    
    $stmt->close();
    return $details;
}

/**
 * Fungsi untuk menyimpan pengajuan izin/cuti baru ke database.
 *
 * @param int $user_id ID karyawan yang mengajukan (dari tabel 'user' gabungan).
 * @param string $jenis Jenis pengajuan ('izin' atau 'cuti').
 * @param string $tanggal_mulai Tanggal mulai pengajuan (format WERE-MM-DD).
 * @param string $tanggal_akhir Tanggal berakhir pengajuan (format WERE-MM-DD).
 * @param string $alasan Alasan pengajuan.
 * @param string|null $dokumen_pendukung Path dokumen pendukung (opsional).
 * @return bool True jika berhasil, false jika gagal.
 */
function submit_leave_request($user_id, $jenis, $tanggal_mulai, $tanggal_akhir, $alasan, $dokumen_pendukung = null) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO pengajuan_izin_cuti (user_id, jenis_pengajuan, tanggal_mulai, tanggal_akhir, alasan, dokumen_pendukung, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    
    if ($stmt === false) {
        error_log("Failed to prepare submit_leave_request statement: " . $conn->error);
        return false;
    }

    $stmt->bind_param("isssss", $user_id, $jenis, $tanggal_mulai, $tanggal_akhir, $alasan, $dokumen_pendukung);
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fungsi untuk mendapatkan semua riwayat pengajuan izin/cuti untuk karyawan tertentu.
 *
 * @param int $user_id ID karyawan (dari tabel 'user' gabungan).
 * @return array Array berisi semua data pengajuan.
 */
function get_user_leave_requests($user_id) {
    global $conn;

    $requests = [];
    $stmt = $conn->prepare("SELECT * FROM pengajuan_izin_cuti WHERE user_id = ? ORDER BY tanggal_pengajuan DESC");

    if ($stmt === false) {
        error_log("Failed to prepare get_user_leave_requests statement: " . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
    return $requests;
}

/**
 * Fungsi untuk mendapatkan semua pengajuan izin/cuti dengan status 'pending'.
 * Menggabungkan dengan tabel 'user' yang baru untuk menampilkan nama dan jabatan pengaju.
 *
 * @return array Array berisi semua data pengajuan pending.
 */
function get_all_pending_leave_requests() {
    global $conn;
    $requests = [];

    $stmt = $conn->prepare("
        SELECT
            pic.*,
            u.nama AS nama_karyawan,
            u.jabatan AS jabatan_karyawan,
            u_admin.username AS admin_username
        FROM
            pengajuan_izin_cuti pic
        LEFT JOIN
            user u ON pic.user_id = u.id -- JOIN ke tabel 'user' yang baru
        LEFT JOIN
            user u_admin ON pic.disetujui_oleh = u_admin.id -- JOIN ke tabel 'user' untuk admin
        WHERE
            pic.status = 'pending'
        ORDER BY
            pic.tanggal_pengajuan ASC
    ");

    if ($stmt === false) {
        error_log("Failed to prepare get_all_pending_leave_requests statement: " . $conn->error);
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
    return $requests;
}

/**
 * Fungsi untuk mendapatkan semua riwayat pengajuan izin/cuti (baik pending, disetujui, maupun ditolak).
 * Sekarang merujuk ke tabel 'user' yang baru.
 *
 * @return array Array berisi semua data pengajuan.
 */
function get_all_leave_requests_history() {
    global $conn;
    $requests = [];

    $stmt = $conn->prepare("
        SELECT
            pic.*,
            u.nama AS nama_karyawan,
            u.jabatan AS jabatan_karyawan,
            u_admin.username AS admin_username
        FROM
            pengajuan_izin_cuti pic
        LEFT JOIN
            user u ON pic.user_id = u.id -- JOIN ke tabel 'user' yang baru
        LEFT JOIN
            user u_admin ON pic.disetujui_oleh = u_admin.id -- JOIN ke tabel 'user' untuk admin
        ORDER BY
            pic.tanggal_pengajuan DESC
    ");

    if ($stmt === false) {
        error_log("Failed to prepare get_all_leave_requests_history statement: " . $conn->error);
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
    return $requests;
}

/**
 * Fungsi untuk mengupdate status pengajuan izin/cuti oleh admin.
 *
 * @param int $request_id ID pengajuan yang akan diupdate.
 * @param string $status Status baru ('disetujui' atau 'ditolak').
 * @param int $admin_id ID admin yang melakukan persetujuan/penolakan (dari tabel 'user' gabungan).
 * @param string|null $catatan_admin Catatan tambahan dari admin (opsional).
 * @return bool True jika berhasil, false jika gagal.
 */
function update_leave_request_status($request_id, $status, $admin_id, $catatan_admin = null) {
    global $conn;

    $stmt = $conn->prepare("UPDATE pengajuan_izin_cuti SET status = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW(), catatan_admin = ? WHERE id = ?");

    if ($stmt === false) {
        error_log("Failed to prepare update_leave_request_status statement: " . $conn->error);
        return false;
    }

    $stmt->bind_param("sisi", $status, $admin_id, $catatan_admin, $request_id);

    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

?>