-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Agu 2025 pada 10.58
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
-- Database: `apotek_kasir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `subtotal` int(11) GENERATED ALWAYS AS (`jumlah` * `harga_satuan`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_produk`, `jumlah`, `harga_satuan`) VALUES
(1, 3, 7, 1, 13),
(2, 5, 8, 1, 150000),
(3, 6, 8, 1, 150000),
(4, 7, 8, 4, 150000),
(7, 10, 7, 1, 13),
(8, 11, 8, 1, 150000),
(11, 14, 7, 1, 13),
(12, 15, 8, 1, 150000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `nama_kategori` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `jumlah_produk` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `gambar`, `nama_kategori`, `keterangan`, `jumlah_produk`, `created_by`, `updated_by`) VALUES
(4, 'obat_keras_h7diak.png', 'Obat keras', 'obat yang hanya boleh dibeli menggunakan resep dokter. Tempat penjualan di Apotek. Pada obat bebas terbatas, selain terdapat tanda lingkaran biru, diberi pula tanda peringatan untuk aturan pakai obat sehingga obat ini aman digunakan untuk pengobatan sendiri.', 2, NULL, 3),
(5, 'obat+bebas.png', 'obat bebas', 'obat yang dijual bebas di pasaran dan dapat dibeli tanpa resep dokter. Tempat penjualan di Apotek dan Toko Obat Berijin. Logo lingkaran berwarna biru dengan garis tepi berwarna hitam', 0, 3, NULL),
(6, 'pers release dinkes - 1.jpg', 'Obat bebas terbatas', 'obat yang bisa diperoleh tanpa resep dokter namun tetap memerlukan kewaspadaan karena termasuk golongan obat keras yang memiliki batasan jumlah dan kadar zat aktifnya, ditandai dengan logo lingkaran biru bergaris tepi hitam pada kemasannya. Obat ini harus dibeli di apotek atau toko obat berizin dan disertai dengan tanda peringatan khusus yang berisi petunjuk cara penggunaan yang harus diikuti dengan benar.', 0, 3, NULL),
(7, 'JAMU_FIX_c3l0j5.png', 'Obat jamu', 'obat tradisional khas Indonesia yang terbuat dari bahan-bahan alami seperti rempah-rempah, akar, dan daun, yang telah digunakan secara turun-temurun untuk menjaga kesehatan dan mengatasi berbagai penyakit. Jamu adalah bentuk obat tradisional yang paling sederhana, di mana pembuktian khasiat dan keamanannya didasarkan pada bukti empiris dan pengalaman masyarakat dari generasi ke generasi.', 0, 3, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga_satuan` int(11) DEFAULT NULL,
  `harga_total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `laporan_keuntungan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `laporan_keuntungan` (
`id_transaksi` int(11)
,`tanggal` datetime
,`total_modal` decimal(42,0)
,`total_penjualan` decimal(32,0)
,`keuntungan` decimal(43,0)
,`kasir` varchar(100)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_kegiatan`
--

CREATE TABLE `log_kegiatan` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `aksi` varchar(255) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE `member` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `poin` int(11) DEFAULT 0,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member`
--

INSERT INTO `member` (`id`, `nama`, `no_hp`, `poin`, `status`) VALUES
(2, 'ibnu', '089765891324', 137, 'aktif'),
(10, 'yuki', '085697011994', 1200013, 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `member_point_log`
--

CREATE TABLE `member_point_log` (
  `id` int(11) NOT NULL,
  `id_member` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL,
  `poin_masuk` int(11) DEFAULT 0,
  `poin_keluar` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `harga_beli` int(11) DEFAULT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `izin_edar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `id_kategori`, `stok`, `harga_beli`, `harga_jual`, `expired_date`, `gambar`, `barcode`, `izin_edar`, `deskripsi`, `created_by`, `updated_by`) VALUES
(7, 'teblet', 4, 7, 12, 13, '2025-08-13', '689738926b72a_lambang-dan-logo-removebg-preview.png', '0000000000007', '344', 'ff', NULL, NULL),
(8, 'doxycyclineE', 4, 79, 90000, 150000, '2025-08-13', '6897432e70358_images.jpeg', '0000000000008', 'GKL9605021001A1', 'Dalam mengobati jerawat, doxycycline membunuh bakteri yang menginfeksi pori-pori kulit. Selain itu, obat ini juga mampu mengurangi produksi minyak berlebih yang memicu timbulnya jerawat.', 3, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `reset_password`
--

CREATE TABLE `reset_password` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `id_kasir` int(11) DEFAULT NULL,
  `id_member` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `metode_bayar` varchar(20) DEFAULT NULL,
  `diskon` int(11) DEFAULT 0,
  `bayar` int(11) DEFAULT 0,
  `pakai_poin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `id_kasir`, `id_member`, `total`, `tanggal`, `metode_bayar`, `diskon`, `bayar`, `pakai_poin`) VALUES
(1, 8, NULL, 13, '2025-08-10 10:09:29', NULL, 0, 0, 0),
(2, 8, NULL, 13, '2025-08-10 10:13:36', NULL, 0, 0, 0),
(3, 8, NULL, 13, '2025-08-10 10:14:32', NULL, 0, 0, 0),
(4, 8, 10, 150000, '2025-08-10 10:40:51', NULL, 0, 0, 0),
(5, 8, 10, 150000, '2025-08-10 10:42:05', NULL, 0, 0, 0),
(6, 8, 10, 150000, '2025-08-10 11:09:57', NULL, 0, 0, 0),
(7, 8, 10, 600000, '2025-08-10 11:14:34', NULL, 0, 0, 0),
(10, 8, NULL, 13, '2025-08-10 12:15:29', 'cash', 0, 5000, 0),
(11, 8, 10, 150000, '2025-08-10 12:15:58', 'qris', 0, 0, 0),
(14, 8, 2, 0, '2025-08-10 13:30:26', 'cash', 150, 0, 1),
(15, 8, 2, 150000, '2025-08-10 13:34:19', 'cash', 0, 150000, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','kasir') DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `email`, `reset_token`, `reset_expiry`, `foto`) VALUES
(3, 'Super Admin', 'admin', '$2y$10$zf6cvpM69q.0CFwafbkO8ufSKzifuAyTz6pGEt3HVcXsGBDizJdwa', 'superadmin', 'ayu.syafira93@smk.belajar.id', NULL, NULL, 'foto_6897536c89b68.jpg'),
(4, 'Kasir Satu', 'kasir1', '$2y$10$YdPSmY7j10vt4ZrliyuOze7r13QMEA3cs0QN9CEA/2.ROnnOkhdF.', 'kasir', 'ayushafira2107@gmail.com', NULL, NULL, 'foto_689753849f68c.jpeg'),
(8, 'Yoshi', 'yoshi ganteng', '$2y$10$i.KEFspF7pllQ7EN4vxUA.9hkZKzIA3NmioevExyJg5nhVIKqzXka', 'kasir', 'yoshi@gmail.com', NULL, NULL, 'foto_68975302a5576.jpeg');

-- --------------------------------------------------------

--
-- Struktur untuk view `laporan_keuntungan`
--
DROP TABLE IF EXISTS `laporan_keuntungan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `laporan_keuntungan`  AS SELECT `t`.`id` AS `id_transaksi`, `t`.`tanggal` AS `tanggal`, sum(`k`.`qty` * `p`.`harga_beli`) AS `total_modal`, sum(`k`.`harga_total`) AS `total_penjualan`, sum(`k`.`harga_total` - `k`.`qty` * `p`.`harga_beli`) AS `keuntungan`, `u`.`nama` AS `kasir` FROM (((`transaksi` `t` join `keranjang` `k` on(`t`.`id` = `k`.`id_transaksi`)) join `produk` `p` on(`k`.`id_produk` = `p`.`id`)) join `users` `u` on(`t`.`id_kasir` = `u`.`id`)) GROUP BY `t`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `log_kegiatan`
--
ALTER TABLE `log_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `member_point_log`
--
ALTER TABLE `member_point_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_member` (`id_member`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `reset_password`
--
ALTER TABLE `reset_password`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kasir` (`id_kasir`),
  ADD KEY `id_member` (`id_member`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_kegiatan`
--
ALTER TABLE `log_kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `member_point_log`
--
ALTER TABLE `member_point_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `reset_password`
--
ALTER TABLE `reset_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `log_kegiatan`
--
ALTER TABLE `log_kegiatan`
  ADD CONSTRAINT `log_kegiatan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `member_point_log`
--
ALTER TABLE `member_point_log`
  ADD CONSTRAINT `member_point_log_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `member` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kasir`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_member`) REFERENCES `member` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
