-- Menggunakan database
CREATE DATABASE IF NOT EXISTS pasar_coffee;
USE pasar_coffee;

-- Membuat tabel produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) UNIQUE,
    nama VARCHAR(100),
    harga DECIMAL(10,2),
    stok INT
);

-- Membuat tabel transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATETIME,
    total DECIMAL(10,2)
);

-- Membuat tabel detail transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT,
    produk_id INT,
    jumlah INT,
    harga DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS apriori_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    antecedent TEXT NOT NULL,
    consequent TEXT NOT NULL,
    support DECIMAL(10,4) NOT NULL,
    confidence DECIMAL(10,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
