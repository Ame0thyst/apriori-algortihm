<?php
// Koneksi ke MySQL tanpa memilih database
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Koneksi awal ke MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buat database jika belum ada
    $sql = "CREATE DATABASE IF NOT EXISTS toko_harian";
    $pdo->exec($sql);
    echo "Database toko_harian berhasil dibuat atau sudah ada.<br>";

    // Pilih database toko_harian
    $pdo->exec("USE toko_harian");

    // Buat tabel produk
    $sql = "CREATE TABLE IF NOT EXISTS produk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode VARCHAR(10) UNIQUE,
        nama VARCHAR(100) NOT NULL,
        harga DECIMAL(10,2) NOT NULL,
        stok INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Tabel produk berhasil dibuat.<br>";

    // Buat tabel transaksi
    $sql = "CREATE TABLE IF NOT EXISTS transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tanggal DATETIME NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Tabel transaksi berhasil dibuat.<br>";
    //table apriori
    $sql = "CREATE TABLE IF NOT EXISTS apriori_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    antecedent TEXT NOT NULL,
    consequent TEXT NOT NULL,
    support DECIMAL(10,4) NOT NULL,
    confidence DECIMAL(10,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
    $pdo->exec($sql);
    echo "Tabel apriori_rules berhasil dibuat.<br>";

    // Buat tabel detail_transaksi
    $sql = "CREATE TABLE IF NOT EXISTS detail_transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaksi_id INT NOT NULL,
        produk_id INT NOT NULL,
        jumlah INT NOT NULL,
        harga DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
        FOREIGN KEY (produk_id) REFERENCES produk(id)
    )";
    $pdo->exec($sql);
    echo "Tabel detail_transaksi berhasil dibuat.<br>";

    // Masukkan data contoh untuk produk
    $sql = "INSERT INTO produk (kode, nama, harga, stok) VALUES 
        ('P001', 'Beras 5kg', 65000, 20),
        ('P002', 'Minyak Goreng 2L', 28000, 30),
        ('P003', 'Gula 1kg', 15000, 50),
        ('P004', 'Telur 1kg', 25000, 40),
        ('P005', 'Tepung Terigu 1kg', 12000, 25)
    ON DUPLICATE KEY UPDATE 
        nama=VALUES(nama), 
        harga=VALUES(harga), 
        stok=VALUES(stok)";
    $pdo->exec($sql);
    echo "Data contoh produk berhasil ditambahkan.<br>";

    echo "<br>Migrasi database berhasil dilakukan!<br>";
    echo "<a href='../index.php'>Kembali ke Dashboard</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$sql = "CREATE TABLE IF NOT EXISTS apriori_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    antecedent TEXT NOT NULL,
    consequent TEXT NOT NULL,
    support DECIMAL(10,4) NOT NULL,
    confidence DECIMAL(10,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($sql);
echo "Tabel apriori_rules berhasil dibuat.<br>";
