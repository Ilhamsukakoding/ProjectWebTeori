-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 19 Jun 2025 pada 14.35
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `keterangan`, `created_at`) VALUES
(5, 4, '2025-05-31', '18:59:33', '18:59:44', 'hadir', '2025-05-31 11:59:34'),
(6, 6, '2025-05-31', '19:41:42', '19:41:50', 'hadir', '2025-05-31 12:41:42'),
(9, 9, '2025-05-31', '19:47:30', '19:47:35', 'hadir', '2025-05-31 12:47:30'),
(12, 7, '2025-06-17', '21:13:59', '21:14:05', 'Hadir', '2025-06-17 14:14:00'),
(13, 7, '2025-06-18', '19:42:55', '19:43:05', 'Hadir', '2025-06-18 12:42:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`id`, `nama`, `jabatan`, `email`, `created_at`) VALUES
(4, 'ilham', 'IT staff', 'ilhamkurniawannn12@gmail.com', '2025-05-31 12:44:21'),
(5, 'adyatma', 'Kepala Divisi IT', 'adyatma03@gmail.com', '2025-05-31 12:45:06'),
(10, 'wibi', 'IT staff', 'wibi@gmail.com', '2025-06-19 14:12:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_izin_cuti`
--

CREATE TABLE `pengajuan_izin_cuti` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `jenis_pengajuan` enum('izin','cuti') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `alasan` text NOT NULL,
  `dokumen_pendukung` varchar(255) DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_pengajuan` datetime DEFAULT CURRENT_TIMESTAMP,
  `disetujui_oleh` int DEFAULT NULL,
  `tanggal_persetujuan` datetime DEFAULT NULL,
  `catatan_admin` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengajuan_izin_cuti`
--

INSERT INTO `pengajuan_izin_cuti` (`id`, `user_id`, `jenis_pengajuan`, `tanggal_mulai`, `tanggal_akhir`, `alasan`, `dokumen_pendukung`, `status`, `tanggal_pengajuan`, `disetujui_oleh`, `tanggal_persetujuan`, `catatan_admin`) VALUES
(6, 4, 'izin', '2025-06-18', '2025-06-19', 'baik', 'doc_6852c01325c6f_tv_lg_65uk6540ptd.png', 'disetujui', '2025-06-18 20:33:07', 1, '2025-06-18 21:01:42', ''),
(7, 4, 'izin', '2025-06-18', '2025-06-19', 'baik', 'doc_6852c09c2b0e7_tv_lg_65uk6540ptd.png', 'disetujui', '2025-06-18 20:35:24', 1, '2025-06-18 21:01:46', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `isi` text NOT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `isi`, `tanggal`) VALUES
(1, 'Jadwal Libur Nasional Idul Adha 2025: 16-17 Juni 2025,\r\nRapat bulanan divisi IT akan diadakan tanggal 25 Juni 2025 di ruang Rapat B,\r\nMohon untuk selalu melakukan absensi masuk dan pulang tepat waktu.', '2025-06-19 14:33:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `nama`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'admin', '2025-05-31 10:38:06'),
(7, 'ilham', '123456', 'ilham', 'user', '2025-05-31 12:44:21'),
(8, 'adyatma', '123456', 'adyatma', 'user', '2025-05-31 12:45:06'),
(12, 'wibi', '123456', 'wibi', 'user', '2025-06-19 14:12:16');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  ADD CONSTRAINT `pengajuan_izin_cuti_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
