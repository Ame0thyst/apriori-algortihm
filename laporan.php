<?php
require_once 'config/database.php';

// Set default tanggal jika tidak ada filter
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Handler untuk export Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Set header untuk download file Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="laporan_penjualan.xls"');
    header('Cache-Control: max-age=0');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Toko Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!isset($_GET['export'])): ?>
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                .card {
                    border: none !important;
                }
            }
        </style>
    <?php endif; ?>
</head>

<body>
    <?php if (!isset($_GET['export'])): ?>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">Toko Harian</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                                href="index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : ''; ?>"
                                href="produk.php">Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : ''; ?>"
                                href="transaksi.php">Kasir</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>"
                                href="laporan.php">Laporan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apriori.php' ? 'active' : ''; ?>"
                                href="apriori.php">Analisis Apriori</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="container mt-4">
        <?php if (!isset($_GET['export'])): ?>
            <!-- Form Filter -->
            <div class="card mb-4 no-print">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" name="tanggal_awal" value="<?php echo $tanggal_awal; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="tanggal_akhir" value="<?php echo $tanggal_akhir; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                                <button type="button" class="btn btn-success" onclick="window.print();">Print</button>
                                <a href="?export=excel&tanggal_awal=<?php echo $tanggal_awal; ?>&tanggal_akhir=<?php echo $tanggal_akhir; ?>"
                                    class="btn btn-warning">Export Excel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header Laporan -->
        <div class="text-center mb-4">
            <h2>Laporan Penjualan</h2>
            <p>Periode: <?php echo date('d/m/Y', strtotime($tanggal_awal)); ?> -
                <?php echo date('d/m/Y', strtotime($tanggal_akhir)); ?></p>
        </div>

        <!-- Ringkasan -->
        <div class="row mb-4">
            <?php
            // Total transaksi
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_transaksi,
                    SUM(total) as total_pendapatan,
                    COUNT(DISTINCT DATE(tanggal)) as total_hari
                FROM transaksi 
                WHERE DATE(tanggal) BETWEEN ? AND ?
            ");
            $stmt->execute([$tanggal_awal, $tanggal_akhir]);
            $summary = $stmt->fetch();

            // Rata-rata per hari
            $rata_rata = $summary['total_hari'] ? ($summary['total_pendapatan'] / $summary['total_hari']) : 0;
            ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Transaksi</h5>
                        <h2><?php echo $summary['total_transaksi']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Pendapatan</h5>
                        <h2>Rp <?php echo number_format($summary['total_pendapatan'], 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata per Hari</h5>
                        <h2>Rp <?php echo number_format($rata_rata, 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produk Terlaris -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Produk Terlaris</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    p.nama,
                                    SUM(dt.jumlah) as total_qty,
                                    SUM(dt.subtotal) as total_pendapatan
                                FROM detail_transaksi dt
                                JOIN produk p ON dt.produk_id = p.id
                                JOIN transaksi t ON dt.transaksi_id = t.id
                                WHERE DATE(t.tanggal) BETWEEN ? AND ?
                                GROUP BY p.id
                                ORDER BY total_qty DESC
                                LIMIT 10
                            ");
                            $stmt->execute([$tanggal_awal, $tanggal_akhir]);

                            while ($row = $stmt->fetch()) {
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo $row['total_qty']; ?></td>
                                    <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Transaksi -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detail Transaksi</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    t.tanggal,
                                    p.nama as nama_produk,
                                    dt.jumlah,
                                    dt.harga,
                                    dt.subtotal
                                FROM detail_transaksi dt
                                JOIN transaksi t ON dt.transaksi_id = t.id
                                JOIN produk p ON dt.produk_id = p.id
                                WHERE DATE(t.tanggal) BETWEEN ? AND ?
                                ORDER BY t.tanggal DESC
                            ");
                            $stmt->execute([$tanggal_awal, $tanggal_akhir]);

                            while ($row = $stmt->fetch()) {
                            ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td><?php echo $row['jumlah']; ?></td>
                                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (!isset($_GET['export'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php endif; ?>
</body>

</html>