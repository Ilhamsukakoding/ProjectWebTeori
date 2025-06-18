<?php
// Pastikan koneksi database ($conn) sudah tersedia,
// misalnya dari includes/config.php yang sudah di-require sebelumnya.

if (!isset($conn)) {
    // Jika $conn belum ada, coba panggil config.php lagi
    // atau pastikan config.php di-require sebelum function.php di halaman utama
    require_once 'config.php';
}

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
        // Handle error, misalnya log error atau tampilkan pesan
        error_log("Failed to prepare statement: " . $conn->error);
        return ['status' => 'error', 'message' => 'Database query preparation failed.'];
    }

    $stmt->bind_param("is", $user_id, $today); // "i" for integer (user_id), "s" for string (today)
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Data absensi untuk hari ini ditemukan
        if (!empty($row['jam_keluar'])) {
            // Sudah absen masuk dan absen keluar
            return [
                'status' => 'pulang',
                'time_in' => $row['jam_masuk'],
                'time_out' => $row['jam_keluar'],
                'can_checkout' => false // Tidak bisa checkout lagi
            ];
        } else {
            // Sudah absen masuk tapi belum absen keluar
            return [
                'status' => 'masuk',
                'time' => $row['jam_masuk'],
                'can_checkout' => true // Bisa melakukan checkout
            ];
        }
    } else {
        // Belum ada data absensi untuk hari ini
        return [
            'status' => 'belum',
            'time' => null,
            'can_checkout' => false // Belum bisa checkout (karena belum masuk)
        ];
    }
    $stmt->close();
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

    // Asumsi user_id di tabel 'user' adalah id yang sama dengan 'id' di tabel 'karyawan'
    // ATAU jika tabel 'user' memiliki 'karyawan_id' yang merujuk ke tabel 'karyawan'
    // Saya akan asumsikan user_id yang disimpan di session adalah ID langsung dari tabel 'karyawan'.
    // Jika tidak, Anda perlu menyesuaikan join antara tabel 'user' dan 'karyawan'.
    
    $stmt = $conn->prepare("SELECT nama FROM karyawan WHERE id = ?");
    if ($stmt === false) {
        error_log("Failed to prepare statement for getting employee name: " . $conn->error);
        return "Karyawan"; // Default jika ada error
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return htmlspecialchars($row['nama']);
    }
    return "Karyawan"; // Default jika nama tidak ditemukan
    $stmt->close();
}

// Anda bisa menambahkan fungsi-fungsi lain di sini nanti,
// seperti get_total_hadir_bulan_ini($user_id), dll.

?>