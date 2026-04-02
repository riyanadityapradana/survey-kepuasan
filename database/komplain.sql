-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Apr 2026 pada 15.05
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `survei_rs`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `komplain`
--

CREATE TABLE `komplain` (
  `id` int(11) NOT NULL,
  `id_responden` int(11) DEFAULT NULL,
  `area_komplain` varchar(100) NOT NULL,
  `identitas_pasien` varchar(150) NOT NULL,
  `aduan` text NOT NULL,
  `tanggal_komplain` datetime NOT NULL,
  `tanggal_tindak_lanjut` datetime DEFAULT NULL,
  `status` enum('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  `keterangan_tindak_lanjut` text DEFAULT NULL,
  `sumber_data` enum('manual','survey_otomatis') NOT NULL DEFAULT 'manual',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `komplain`
--
ALTER TABLE `komplain`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_komplain_responden` (`id_responden`),
  ADD KEY `fk_komplain_updated_by` (`updated_by`),
  ADD KEY `fk_komplain_created_by` (`created_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `komplain`
--
ALTER TABLE `komplain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `komplain`
--
ALTER TABLE `komplain`
  ADD CONSTRAINT `fk_komplain_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_komplain_responden` FOREIGN KEY (`id_responden`) REFERENCES `responden` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_komplain_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
