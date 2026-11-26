-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Nov 2026 pada 05.17
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
-- Database: `db_voting`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontestan`
--

CREATE TABLE `kontestan` (
  `id` int(11) NOT NULL,
  `nomor_peserta` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `asal_instansi` varchar(150) NOT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kontestan`
--

INSERT INTO `kontestan` (`id`, `nomor_peserta`, `nama`, `kategori`, `foto`, `asal_instansi`, `visi`, `misi`, `deskripsi`) VALUES
(4, '02', 'Ronaldo', 'Putra', 'kontestan_6af56107ead35.jpg', 'SMKN 1 JENANGAN', 'semangat', 'semangat', 'goal'),
(5, '03', 'Messieeee', 'Putra', 'kontestan_6af5619891837.jpg', 'MAN 2 PONOROGO', 'goat', 'goat', 'mesie goat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode_voting`
--

CREATE TABLE `periode_voting` (
  `id` int(11) NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Nonaktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `periode_voting`
--

INSERT INTO `periode_voting` (`id`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`) VALUES
(1, 'Pemilihan !', '2026-11-12 10:53:00', '2026-11-13 10:53:00', 'Aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pemilih') DEFAULT 'pemilih',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@voting.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-11-12 03:06:10'),
(2, 'rasya', 'rsya@gmail.com', '08123456789', '$2y$10$1woFsZYnjjJr5sktQtFZiOVgBDuYI90Eveb0pkqwerpJROGExmPve', 'pemilih', '2026-11-12 03:33:18'),
(3, 'reva', 'reva@gmail.com', '0888888888', '$2y$10$kkHdqbsPbrOMo7tSVILET.l.HOCWQ37PWwGa/7MzWXP9PtpSQcZaO', 'pemilih', '2026-11-12 06:08:41'),
(4, 'zwei', 'zwei@gmail.com', '089191919191', '$2y$10$QHVrmPxaJozfJWRZfqicCuqPBh9U9247j3/UDTmPz4/EvVWUPR4OG', 'pemilih', '2026-11-12 06:28:02'),
(5, 'sakti', 'sakti@gmail.com', '08121222122', '$2y$10$zV9viKDWDRcj1l1EFWSByOPiW5xTEY3A.4HqZ.Q.OaQVvcvSOS6nW', 'pemilih', '2026-11-12 06:48:15'),
(6, 'fauzi', 'fauzi@gmail.com', '081313133131', '$2y$10$qdVIb25d14LT3t4pMkAqruOaHX2T7pFOe2I48SIbsf1rLvGRGJo86', 'pemilih', '2026-11-12 06:50:30'),
(7, 'faza', 'faza@gmail.com', '08141414141414', '$2y$10$A/THYCJpq/Mxzq55EhzWGuFie0wiShG4MW0cMlOyzx7uegerGdMyi', 'pemilih', '2026-11-13 03:50:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `voting`
--

CREATE TABLE `voting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kontestan_id` int(11) NOT NULL,
  `kategori` enum('Kategori A','Kategori B') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `voting`
--

INSERT INTO `voting` (`id`, `user_id`, `kontestan_id`, `kategori`, `created_at`) VALUES
(4, 5, 4, 'Kategori A', '2026-11-12 13:48:31'),
(5, 6, 5, 'Kategori A', '2026-11-12 13:50:43'),
(6, 7, 5, 'Kategori A', '2026-11-13 10:50:51');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kontestan`
--
ALTER TABLE `kontestan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `periode_voting`
--
ALTER TABLE `periode_voting`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `voting`
--
ALTER TABLE `voting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_user_kategori` (`user_id`,`kategori`),
  ADD KEY `fk_kontestan` (`kontestan_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kontestan`
--
ALTER TABLE `kontestan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `periode_voting`
--
ALTER TABLE `periode_voting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `voting`
--
ALTER TABLE `voting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `voting`
--
ALTER TABLE `voting`
  ADD CONSTRAINT `fk_kontestan` FOREIGN KEY (`kontestan_id`) REFERENCES `kontestan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
