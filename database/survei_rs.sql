-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Apr 2026 pada 18.30
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
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aksi` varchar(50) NOT NULL,
  `entitas` varchar(50) NOT NULL,
  `entitas_id` int(11) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `aksi`, `entitas`, `entitas_id`, `deskripsi`, `created_at`) VALUES
(1, 1, 'tambah', 'user', 3, 'Menambahkan user petugas dengan role petugas.', '2026-04-02 00:30:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jawaban`
--

CREATE TABLE `jawaban` (
  `id` int(11) NOT NULL,
  `id_responden` int(11) DEFAULT NULL,
  `id_pertanyaan` int(11) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pertanyaan`
--

CREATE TABLE `pertanyaan` (
  `id` int(11) NOT NULL,
  `jenis` enum('ralan','ranap') NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `pertanyaan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pertanyaan`
--

INSERT INTO `pertanyaan` (`id`, `jenis`, `kategori`, `pertanyaan`) VALUES
(4, 'ranap', 'KEPUASAN PASIEN TENTANG DAYA TANGGAP PELAYANAN PENUNJANG', 'PETUGAS PENUNJANG (PARKIR,SATPAM DAN PETUGAS LAINNYA LAB, RAD, GIZI FARMASI DLL) MEMBERIKAN PELAYANAN DENGAN CEPAT TEPAT ?'),
(5, 'ranap', 'KEPUASAN PASIEN TENTANG DAYA TANGGAP PELAYANAN PENUNJANG', 'RESPON TIME/ WAKTU TANGGAP CEPAT DAN TEPAT PETUGAS PENUNJANG SESUAI KEBUTUHAN PASIEN'),
(6, 'ranap', 'KEPUASAN PASIEN TENTANG KEHANDALAN PELAYANAN ADMINISTRASI', 'PETUGAS REGISTRASI (FO/RECEPTIONIST DAN KASIR) TERKAIT PENGURUSAN ADMINISTRASI MELAYANI DENGAN TELITI DAN BAIK ?'),
(7, 'ranap', 'KEPUASAN PASIEN TENTANG KEHANDALAN PELAYANAN ADMINISTRASI', 'RESPON TIME/ WAKTU TANGGAP CEPAT DAN TEPAT PETUGAS ADMINISTRASI SESUAI KEBUTUHAN PASIEN ?'),
(8, 'ranap', 'KEPUASAN PASIEN TENTANG PERAWAT RUANGAN', 'PERAWAT MEMAHAMI KEBUTUHAN PASIEN DAN BERSIKAP RAMAH,SOPAN, SERTA BERBICARA SANTUN ?'),
(9, 'ranap', 'KEPUASAN PASIEN TENTANG PERAWAT RUANGAN', 'PERAWAT PUNYA KEMAMPUAN MENYUNTIK, MEMASANG INFUS/TINDAKAN KEPERAWATAN DENGAN BENAR DAN MELAYANI DENGAN HATI-HATI DAN TELITI ?'),
(10, 'ranap', 'KEPUASAN PASIEN TENTANG DOKTER PENANGGUNG JAWAB', 'DOKTER MAMPU MENDIAGNOSIS PENYAKIT DENGAN BENAR DAN MELAKUKUKAN PEMERIKSAAN DENGAN SOPAN ?'),
(11, 'ranap', 'KEPUASAN PASIEN TENTANG DOKTER PENANGGUNG JAWAB ?', 'DOKTER SELALU CEPAT TANGGAP DALAM MENYELESAIKAN SETIAP KELUHAN DARI PASIEN'),
(12, 'ranap', 'KEPUASAN PASIEN TENTANG BUKTI FISIK PELAYANAN', 'RUANG PERAWATAN YANG BERSIH, RAPIH,NYAMAN, DAN TENANG?'),
(13, 'ranap', 'KEPUASAN PASIEN TENTANG BUKTI FISIK PELAYANAN', 'SARANA PRASARANA RUANGAN, PERALATAN YANG MEMADAI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `responden`
--

CREATE TABLE `responden` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-Laki','Perempuan') DEFAULT NULL,
  `lokasi_aduan` varchar(250) NOT NULL,
  `jenis` enum('ralan','ranap') DEFAULT NULL,
  `saran` text DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas') DEFAULT 'petugas',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$hIFWdZRBIRz6rfVXWlB5ku94zY8.N3vxZViZfoMO/YESM3l5PguhS', 'admin', '2026-04-01 23:04:59'),
(2, 'riyan', '$2y$10$yXGZZWGqorgCuI9Xc8yRDuxdpGw0KILqD3boRC7WhkXAUfM.s5/Mi', 'admin', '2026-04-01 23:36:56'),
(3, 'petugas', '$2y$10$34doUtC7FDjO0QUwEYVgleKSbV.bGNZATvzhPbj7ObV1Pn/zCwMfK', 'petugas', '2026-04-02 00:30:12');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indeks untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_responden` (`id_responden`),
  ADD KEY `id_pertanyaan` (`id_pertanyaan`);

--
-- Indeks untuk tabel `pertanyaan`
--
ALTER TABLE `pertanyaan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `responden`
--
ALTER TABLE `responden`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pertanyaan`
--
ALTER TABLE `pertanyaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `responden`
--
ALTER TABLE `responden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jawaban`
--
ALTER TABLE `jawaban`
  ADD CONSTRAINT `jawaban_ibfk_1` FOREIGN KEY (`id_responden`) REFERENCES `responden` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jawaban_ibfk_2` FOREIGN KEY (`id_pertanyaan`) REFERENCES `pertanyaan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
