<?php
// includes/function.php
// Pastikan includes/config.php sudah di-require di halaman utama
// SEBELUM file ini di-require. Dengan begitu, variabel $conn sudah pasti ada.

/**
 * Fungsi untuk mendapatkan status absensi karyawan pada hari ini.
 *
 * @param int $user_id ID user (asumsikan ini juga ID karyawan di tabel 'karyawan')
 * @return array Array asosiatif berisi status, jam masuk, jam pulang, dan kemampuan checkout.
 */
function get_today_attendance_status($user_id) {
    global $conn; // Mengakses variabel koneksi global dari config.php

    $today = date('Y-m-d'); // Tanggal hari ini dalam format YYYY-MM-DD

    // Query untuk mencari absensi hari ini berdasarkan user_id dan tanggal
    $stmt = $conn->prepare("SELECT jam_masuk, jam_keluar FROM absensi WHERE user_id = ? AND tanggal = ?");
    
    // Periksa apakah prepare berhasil
    if ($stmt === false) {
        error_log("Failed to prepare get_today_attendance_status statement: " . $conn->error);
        return ['status' => 'error', 'message' => 'Database query preparation failed.'];
    }

    $stmt->bind_param("is", $user_id, $today); // "i" for integer (user_id), "s" for string (today)
    $stmt->execute();
    $result = $stmt->get_result();

    $status_data = [
        'status' => 'belum',
        'time' => null,
        'can_checkout' => false
    ];

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['jam_keluar'])) {
            // Sudah absen masuk dan absen keluar
            $status_data = [
                'status' => 'pulang',
                'time_in' => $row['jam_masuk'],
                'time_out' => $row['jam_keluar'],
                'can_checkout' => false
            ];
        } else {
            // Sudah absen masuk tapi belum absen keluar
            $status_data = [
                'status' => 'masuk',
                'time' => $row['jam_masuk'],
                'can_checkout' => true
            ];
        }
    }
    
    $stmt->close(); // Tutup statement di sini
    return $status_data;
}

/**
 * Fungsi untuk mendapatkan nama karyawan berdasarkan user_id.
 * Berguna untuk menampilkan nama di dashboard.
 *
 * @param int $user_id ID user (asumsi sama dengan ID di tabel karyawan)
 * @return string Nama karyawan atau 'Karyawan' jika tidak ditemukan.
 */
function get_karyawan_name($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT nama FROM karyawan WHERE id = ?");
    if ($stmt === false) {
        error_log("Failed to prepare get_karyawan_name statement: " . $conn->error);
        return "Karyawan";
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $karyawan_name = "Karyawan";
    if ($row = $result->fetch_assoc()) {
        $karyawan_name = htmlspecialchars($row['nama']);
    }
    
    $stmt->close(); // Tutup statement di sini
    return $karyawan_name;
}

/**
 * Fungsi untuk menyimpan pengajuan izin/cuti baru ke database.
 *
 * @param int $user_id ID karyawan yang mengajukan.
 * @param string $jenis Jenis pengajuan ('izin' atau 'cuti').
 * @param string $tanggal_mulai Tanggal mulai pengajuan (format YYYY-MM-DD).
 * @param string $tanggal_akhir Tanggal berakhir pengajuan (format YYYY-MM-DD).
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
    $stmt->close(); // Tutup statement di sini
    return $success;
}

/**
 * Fungsi untuk mendapatkan semua riwayat pengajuan izin/cuti untuk karyawan tertentu.
 *
 * @param int $user_id ID karyawan.
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
    $stmt->close(); // Tutup statement di sini
    return $requests;
}

/**
 * Fungsi untuk mendapatkan semua pengajuan izin/cuti dengan status 'pending'.
 * Menggabungkan dengan tabel karyawan untuk menampilkan nama pengaju.
 *
 * @return array Array berisi semua data pengajuan pending.
 */
function get_all_pending_leave_requests() {
    global $conn;
    $requests = [];

    // Mengambil data pengajuan dan nama karyawan yang mengajukan
    $stmt = $conn->prepare("
        SELECT
            pic.*,
            k.nama AS nama_karyawan,
            k.jabatan AS jabatan_karyawan,
            u.username AS admin_username -- Opsional, jika Anda ingin menampilkan username admin yang memproses
        FROM
            pengajuan_izin_cuti pic
        JOIN
            karyawan k ON pic.user_id = k.id
        LEFT JOIN
            user u ON pic.disetujui_oleh = u.id -- Join dengan tabel user untuk nama admin, jika disetujui_oleh merujuk ke user.id
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
    $stmt->close(); // Tutup statement di sini
    return $requests;
}

/**
 * Fungsi untuk mendapatkan semua riwayat pengajuan izin/cuti (baik pending, disetujui, maupun ditolak).
 * Berguna untuk admin melihat semua riwayat.
 *
 * @return array Array berisi semua data pengajuan.
 */
function get_all_leave_requests_history() {
    global $conn;
    $requests = [];

    $stmt = $conn->prepare("
        SELECT
            pic.*,
            k.nama AS nama_karyawan,
            k.jabatan AS jabatan_karyawan,
            u.username AS admin_username
        FROM
            pengajuan_izin_cuti pic
        JOIN
            karyawan k ON pic.user_id = k.id
        LEFT JOIN
            user u ON pic.disetujui_oleh = u.id
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
    $stmt->close(); // Tutup statement di sini
    return $requests;
}

/**
 * Fungsi untuk mengupdate status pengajuan izin/cuti oleh admin.
 *
 * @param int $request_id ID pengajuan yang akan diupdate.
 * @param string $status Status baru ('disetujui' atau 'ditolak').
 * @param int $admin_id ID admin yang melakukan persetujuan/penolakan.
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
    $stmt->close(); // Tutup statement di sini
    return $success;
}

?>