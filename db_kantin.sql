-- -----------------------------------------------------
-- Schema `db_kantin`
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_kantin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_kantin`;

-- -----------------------------------------------------
-- Table `db_kantin`.`user`
-- -----------------------------------------------------
CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'karyawan', 'user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `db_kantin`.`menu_produk`
-- -----------------------------------------------------
CREATE TABLE `menu_produk` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` VARCHAR(100) NOT NULL,
  `harga` DECIMAL(10, 2) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` ENUM('Kopi', 'Non-Kopi', 'Pastry') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `db_kantin`.`pesanan`
-- -----------------------------------------------------
CREATE TABLE `pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` VARCHAR(100) NOT NULL,
  `id_produk` INT(11) NOT NULL,
  `jumlah` INT(11) NOT NULL,
  `total_harga` DECIMAL(10, 2) NOT NULL,
  `status_pesanan` ENUM('Baru', 'Proses', 'Selesai') NOT NULL DEFAULT 'Baru',
  `tanggal_pesanan` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_produk`) REFERENCES `menu_produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pesan_kontak` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subjek` VARCHAR(255) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tanggal_dikirim` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `db_kantin`.`desain_pesanan`
-- -----------------------------------------------------
CREATE TABLE `desain_pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `file_desain_url` VARCHAR(255) NULL,
  `keterangan` TEXT NULL,
  `tanggal_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Data Sample
-- -----------------------------------------------------
-- Password untuk semua user adalah 'password123' (di-hash dengan MD5)
INSERT INTO `user` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', '482c811da5d5b4bc6d497ffa98491e38', 'Super Admin', 'admin'),
('karyawan01', '482c811da5d5b4bc6d497ffa98491e38', 'Budi Barista', 'karyawan'),
('customer01', '482c811da5d5b4bc6d497ffa98491e38', 'Andi Pelanggan', 'user');

-- Data Menu Kopi Kenangan-style
INSERT INTO `menu_produk` (`nama_produk`, `harga`, `deskripsi`, `kategori`) VALUES
('Kopi Kenangan Mantan', 25000.00, 'Racikan kopi nusantara dengan sentuhan karamel dan gula aren', 'Kopi'),
('Caramel Macchiato', 35000.00, 'Espresso dengan susu segar dan saus karamel', 'Kopi'),
('Avocado Coffee', 40000.00, 'Perpaduan creamy alpukat dengan kopi yang kuat', 'Non-Kopi'),
('Pandan Brew', 30000.00, 'Susu pandan dengan espresso shot', 'Non-Kopi'),
('Chocolate Croissant', 28000.00, 'Pastry berlapis dengan isian coklat premium', 'Pastry'),
('Almond Croissant', 30000.00, 'Croissant renyah dengan topping almond dan gula', 'Pastry');

-- Data Pesanan
INSERT INTO `pesanan` (`nama_pelanggan`, `id_produk`, `jumlah`, `total_harga`, `status_pesanan`) VALUES
('Rudi Hermawan', 1, 2, 50000.00, 'Proses'),
('Siti Nurhaliza', 2, 1, 35000.00, 'Selesai'),
('Joko Widodo', 3, 1, 40000.00, 'Baru');

INSERT INTO `pesan_kontak` (`nama`, `email`, `subjek`, `pesan`) VALUES
('Budi Santoso', 'budi@example.com', 'Pertanyaan Menu', 'Apakah ada menu vegetarian?'),
('Siti Nurhaliza', 'siti@example.com', 'Komentar', 'Makanannya enak-enak, recommended!');

INSERT INTO `desain_pesanan` (`id_pesanan`, `file_desain_url`, `keterangan`) VALUES
(1, '/uploads/desain_kue_ultah.jpg', 'Desain kue dengan tema superhero');
