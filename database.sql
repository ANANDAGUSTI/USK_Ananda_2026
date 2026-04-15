CREATE DATABASE IF NOT EXISTS usk_kasir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE usk_kasir;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS pengguna;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE pengguna (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','petugas') NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password: admin | petugas (bcrypt via PHP password_hash)
INSERT INTO pengguna (username, password, role) VALUES
('Admin', '$2y$12$YgAdNXZbpoZPjRY1zrnnL.otD/cYLGXz3WKY5TscrrVhQLX0GTrDa', 'admin'),
('petugas', '$2y$12$FVAXCcAeAIWMJlDgGs1KyeD7WJH0Dk1E89.Z6o3hsGODCI8mrKbNe', 'petugas');

CREATE TABLE produk (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(200) NOT NULL,
  deskripsi TEXT NULL,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO produk (nama, deskripsi, harga, stok) VALUES
('Kopi Susu', 'Kopi susu gula aren 250ml', 15000, 50),
('Nasi Goreng', 'Nasi goreng spesial', 22000, 30),
('Air Mineral', 'Botol 600ml', 4000, 100);

CREATE TABLE pesanan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_pembeli VARCHAR(120) NOT NULL,
  telepon VARCHAR(32) NOT NULL,
  alamat TEXT NOT NULL,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('menunggu','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pesanan_status (status),
  KEY idx_pesanan_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE detail_pesanan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pesanan_id INT UNSIGNED NOT NULL,
  produk_id INT UNSIGNED NOT NULL,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  harga_satuan DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(14,2) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_dp_pesanan (pesanan_id),
  KEY fk_dp_produk (produk_id),
  CONSTRAINT fk_dp_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan (id) ON DELETE CASCADE,
  CONSTRAINT fk_dp_produk FOREIGN KEY (produk_id) REFERENCES produk (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
