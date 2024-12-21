<?php
$host = 'localhost';
$dbname = 'toko_harian';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}

// Buat tabel jika belum ada
$sql_produk = "CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) UNIQUE,
    nama VARCHAR(100),
    harga DECIMAL(10,2),
    stok INT
)";

$sql_transaksi = "CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATETIME,
    total DECIMAL(10,2)
)";

$sql_detail = "CREATE TABLE IF NOT EXISTS detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT,
    produk_id INT,
    jumlah INT,
    harga DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id),
    FOREIGN KEY (produk_id) REFERENCES produk(id)
)";

try {
    $pdo->exec($sql_produk);
    $pdo->exec($sql_transaksi);
    $pdo->exec($sql_detail);
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
