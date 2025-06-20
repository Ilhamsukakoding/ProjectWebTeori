-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 19, 2025 at 08:41 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

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
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `keterangan`, `created_at`) VALUES
(2, 4, '2025-06-19', '03:37:03', '03:37:04', 'Hadir', '2025-06-19 20:37:04');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_izin_cuti`
--

CREATE TABLE `pengajuan_izin_cuti` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
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
-- Dumping data for table `pengajuan_izin_cuti`
--

INSERT INTO `pengajuan_izin_cuti` (`id`, `user_id`, `jenis_pengajuan`, `tanggal_mulai`, `tanggal_akhir`, `alasan`, `dokumen_pendukung`, `status`, `tanggal_pengajuan`, `disetujui_oleh`, `tanggal_persetujuan`, `catatan_admin`) VALUES
(6, 2, 'izin', '2025-06-18', '2025-06-19', 'baik', 'doc_6852c01325c6f_tv_lg_65uk6540ptd.png', 'disetujui', '2025-06-18 20:33:07', 1, '2025-06-18 21:01:42', ''),
(7, 2, 'izin', '2025-06-18', '2025-06-19', 'baik', 'doc_6852c09c2b0e7_tv_lg_65uk6540ptd.png', 'disetujui', '2025-06-18 20:35:24', 1, '2025-06-18 21:01:46', ''),
(8, NULL, 'izin', '2025-06-20', '2025-06-20', 'sakit demam', 'doc_685457fece0db.png', 'disetujui', '2025-06-20 01:33:34', 1, '2025-06-20 01:35:57', NULL),
(9, NULL, 'izin', '2025-06-20', '2025-06-20', 'daad', NULL, 'ditolak', '2025-06-20 01:36:37', 1, '2025-06-20 01:37:26', 'kamu keseringan sakit'),
(10, 4, 'izin', '2025-06-20', '2025-06-20', 'sakit', 'doc_685475603aa85.png', 'disetujui', '2025-06-20 03:38:56', 1, '2025-06-20 03:39:47', NULL),
(11, 4, 'izin', '2025-06-21', '2025-06-23', 'sakit lagi', 'doc_685475750dad2.png', 'ditolak', '2025-06-20 03:39:17', 1, '2025-06-20 03:39:52', 'sakit mulu');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `isi` text NOT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `isi`, `tanggal`) VALUES
(3, 'Jadwal Libur Nasional Idul Adha 2025: 16-17 Juni 2025, Rapat bulanan divisi IT akan diadakan tanggal 25 Juni 2025 di ruang Rapat B, Mohon untuk selalu melakukan absensi masuk dan pulang tepat waktu.', '2025-06-19 20:32:01');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `nama`, `jabatan`, `email`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator', NULL, NULL, 'admin', '2025-05-31 17:38:06'),
(2, 'ilham', '123456', 'ilham', 'IT staff', 'ilhamkurniawann12@gmail.com', 'user', '2025-05-31 19:44:21'),
(4, 'ady', '123456', 'ady', 'Kepala Divisi IT', 'ady@gmail.com', 'user', '2025-06-20 03:30:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_absensi_user` (`user_id`);

--
-- Indexes for table `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengajuan_user_id` (`user_id`),
  ADD KEY `fk_pengajuan_disetujui_oleh` (`disetujui_oleh`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pengajuan_izin_cuti`
--
ALTER TABLE `pengajuan_izin_cuti`
  ADD CONSTRAINT `fk_pengajuan_disetujui_oleh` FOREIGN KEY (`disetujui_oleh`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pengajuan_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
