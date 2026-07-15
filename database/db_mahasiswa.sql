SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `db_mahasiswa`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `db_mahasiswa`;

DROP TABLE IF EXISTS `mahasiswa`;
DROP TABLE IF EXISTS `kelas`;
DROP TABLE IF EXISTS `prodi`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `prodi` (
  `id_prodi` int(11) NOT NULL AUTO_INCREMENT,
  `kode_prodi` varchar(20) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `jenjang` enum('D3','D4','S1','S2','S3') DEFAULT 'S1',
  PRIMARY KEY (`id_prodi`),
  UNIQUE KEY `kode_prodi` (`kode_prodi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL AUTO_INCREMENT,
  `id_prodi` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `tahun_angkatan` year(4) NOT NULL,
  PRIMARY KEY (`id_kelas`),
  KEY `id_prodi` (`id_prodi`),
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `prodi` (`id_prodi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `id_prodi` int(11) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `status` enum('aktif','cuti','lulus','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_mahasiswa`),
  UNIQUE KEY `nim` (`nim`),
  KEY `id_prodi` (`id_prodi`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `prodi` (`id_prodi`),
  CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator') DEFAULT 'operator',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prodi` (`id_prodi`, `kode_prodi`, `nama_prodi`, `jenjang`) VALUES
(1, 'TI', 'Teknik Informatika', 'S1'),
(2, 'SI', 'Sistem Informasi', 'S1');

INSERT INTO `kelas` (`id_kelas`, `id_prodi`, `nama_kelas`, `tahun_angkatan`) VALUES
(1, 1, 'TI-2024-A', '2024'),
(2, 2, 'SI-2024-A', '2024');

-- Password akun admin adalah: admin123
INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status`) VALUES
(1, 'Administrator', 'admin', '$2y$12$JtmMhUYnWMqQarhCbwFcFOagYzo.bd0IP3yUMP3DB5wPFgk3TIAm2', 'admin', 'aktif');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
