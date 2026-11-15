-- ============================================================
-- KampusDB sesuai skema pada PAW2026-07 Tugas Praktikum
-- Jalankan di MySQL / phpMyAdmin / terminal:
--   mysql -u root -p < KampusDB_tugas.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS KampusDB;
USE KampusDB;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabel user
-- ------------------------------------------------------------
CREATE TABLE user (
    id_user   TINYINT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(30) NOT NULL UNIQUE,
    password  VARCHAR(35) NOT NULL,
    nama      VARCHAR(50) NOT NULL,
    alamat    VARCHAR(150),
    hp        VARCHAR(20),
    level     TINYINT(1) NOT NULL DEFAULT 2
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel supplier
-- ------------------------------------------------------------
CREATE TABLE supplier (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nama    VARCHAR(100) NOT NULL,
    telp    VARCHAR(12),
    alamat  TEXT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel barang
-- ------------------------------------------------------------
CREATE TABLE barang (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang  VARCHAR(10) NOT NULL UNIQUE,
    nama_barang  VARCHAR(100) NOT NULL,
    harga        INT NOT NULL DEFAULT 0,
    stok         INT NOT NULL DEFAULT 0,
    supplier_id  INT,
    FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel pelanggan
-- ------------------------------------------------------------
CREATE TABLE pelanggan (
    id             VARCHAR(20) PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    jenis_kelamin  ENUM('L','P') NOT NULL,
    telp           VARCHAR(12),
    alamat         TEXT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel transaksi
-- ------------------------------------------------------------
CREATE TABLE transaksi (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    waktu_transaksi  DATE NOT NULL,
    keterangan       TEXT,
    total            INT NOT NULL DEFAULT 0,
    pelanggan_id     VARCHAR(20) NOT NULL,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel transaksi_detail
-- ------------------------------------------------------------
CREATE TABLE transaksi_detail (
    transaksi_id  INT NOT NULL,
    barang_id     INT NOT NULL,
    harga         INT NOT NULL,
    qty           INT NOT NULL DEFAULT 1,
    PRIMARY KEY (transaksi_id, barang_id),
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (barang_id)    REFERENCES barang(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel pembayaran
-- ------------------------------------------------------------
CREATE TABLE pembayaran (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    waktu_bayar   DATETIME NOT NULL,
    total         INT NOT NULL,
    metode        ENUM('TUNAI','TRANSFER','EDC') NOT NULL,
    transaksi_id  INT NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DATA CONTOH
-- ============================================================

INSERT INTO user (username, password, nama, alamat, hp, level) VALUES
('admin', 'admin123', 'Administrator', 'Surabaya', '081300000000', 1),
('kasir', 'kasir123', 'Kasir Satu',    'Surabaya', '081300000001', 2);

INSERT INTO supplier (nama, telp, alamat) VALUES
('PT Maju Jaya',     '0211234567', 'Jl. Melati 1, Jakarta'),
('CV Sumber Ilmu',   '0227654321', 'Jl. Kenanga 5, Bandung');

INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES
('BRG001', 'Buku Tulis A5 38 Lembar',   5000, 100, 1),
('BRG002', 'Pulpen Gel Hitam 0.5mm',    4000, 150, 1),
('BRG003', 'Pensil 2B Kayu',            2500, 200, 2),
('BRG004', 'Penghapus Karet Putih',     1500, 120, 2),
('BRG005', 'Penggaris Plastik 30cm',    3000,  80, 2),
('BRG006', 'Correction Tape',           6000,  60, 1);

INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES
('P001', 'Andi Pratama', 'L', '081200000001', 'Jl. Mawar 3'),
('P002', 'Citra Dewi',   'P', '081200000002', 'Jl. Anggrek 7'),
('P003', 'Budi Santoso', 'L', '081200000003', 'Jl. Dahlia 9');

INSERT INTO transaksi (id, waktu_transaksi, keterangan, total, pelanggan_id) VALUES
(1, '2026-01-01', 'Pembelian alat tulis',  23000, 'P001'),
(2, '2026-01-01', 'Pembelian pensil',      10000, 'P002'),
(3, '2026-01-02', 'Pembelian lainnya',     15000, 'P003'),
(4, '2026-01-03', 'Pembelian buku',        14500, 'P001'),
(5, '2026-01-03', 'Pembelian pulpen',      20000, 'P002');

INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES
(1, 1, 5000, 3),
(1, 2, 4000, 2),
(2, 3, 2500, 4),
(3, 6, 6000, 2),
(3, 5, 3000, 1),
(4, 1, 5000, 2),
(4, 4, 1500, 3),
(5, 2, 4000, 5);

INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id) VALUES
('2026-01-01 10:15:00', 23000, 'TUNAI',    1),
('2026-01-01 11:30:00', 10000, 'TRANSFER', 2),
('2026-01-02 09:00:00', 15000, 'EDC',      3),
('2026-01-03 14:20:00', 14500, 'TUNAI',    4),
('2026-01-03 16:45:00', 20000, 'TRANSFER', 5);
