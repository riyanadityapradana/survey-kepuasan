-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Sep 2025 pada 18.27
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
-- Database: `survey_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `answers`
--

CREATE TABLE `answers` (
  `id_answer` int(11) NOT NULL,
  `id_response` int(11) DEFAULT NULL,
  `id_question` int(11) DEFAULT NULL,
  `jawaban` enum('senang','biasa','buruk') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `answers`
--

INSERT INTO `answers` (`id_answer`, `id_response`, `id_question`, `jawaban`) VALUES
(1, 4, 1, 'buruk'),
(3, 6, 2, 'senang'),
(4, 7, 2, 'biasa'),
(5, 8, 2, 'senang'),
(6, 9, 2, 'senang'),
(7, 10, 2, 'buruk'),
(8, 11, 1, 'senang'),
(9, 12, 1, 'biasa');

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `id_question` int(11) NOT NULL,
  `id_survey` int(11) DEFAULT NULL,
  `pertanyaan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `questions`
--

INSERT INTO `questions` (`id_question`, `id_survey`, `pertanyaan`) VALUES
(1, 1, 'dsasdas?'),
(2, 2, 'Bagaimana kesan anda dalam pelayanan kami ?'),
(3, 3, 'Bagaimana kesan anda dalam pelayanan farmasi ?');

-- --------------------------------------------------------

--
-- Struktur dari tabel `responses`
--

CREATE TABLE `responses` (
  `id_response` int(11) NOT NULL,
  `id_survey` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `responses`
--

INSERT INTO `responses` (`id_response`, `id_survey`, `created_at`) VALUES
(4, 1, '2025-09-30 11:52:27'),
(5, 1, '2025-09-30 11:52:42'),
(6, NULL, '2025-09-30 13:30:58'),
(7, NULL, '2025-09-30 13:35:40'),
(8, NULL, '2025-09-30 13:38:27'),
(9, NULL, '2025-09-30 13:43:00'),
(10, NULL, '2025-09-30 13:46:44'),
(11, NULL, '2025-09-30 13:46:52'),
(12, NULL, '2025-09-30 13:46:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `surveys`
--

CREATE TABLE `surveys` (
  `id_survey` int(11) NOT NULL,
  `judul_survey` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `surveys`
--

INSERT INTO `surveys` (`id_survey`, `judul_survey`, `deskripsi`, `created_at`) VALUES
(1, 'aaa', 'afsf', '2025-09-30 11:47:02'),
(2, 'Pendaftaraan', 'Alur pendaftaran', '2025-09-30 13:28:17'),
(3, 'farmasi', 'pelayanan terhadap pasien', '2025-09-30 14:41:07');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id_answer`),
  ADD KEY `id_response` (`id_response`),
  ADD KEY `id_question` (`id_question`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id_question`),
  ADD KEY `id_survey` (`id_survey`);

--
-- Indeks untuk tabel `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id_response`),
  ADD KEY `id_survey` (`id_survey`);

--
-- Indeks untuk tabel `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id_survey`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `answers`
--
ALTER TABLE `answers`
  MODIFY `id_answer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id_question` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `responses`
--
ALTER TABLE `responses`
  MODIFY `id_response` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id_survey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`id_response`) REFERENCES `responses` (`id_response`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id_question`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`id_survey`) REFERENCES `surveys` (`id_survey`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`id_survey`) REFERENCES `surveys` (`id_survey`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
