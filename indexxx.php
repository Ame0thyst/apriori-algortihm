<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
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

    <!-- Content -->
    <div class="container mt-4">
        <h2 class="mb-4">Dashboard</h2>

        <div class="row">
            <!-- Total Produk -->
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM produk");
                        $total_produk = $stmt->fetchColumn();
                        ?>
                        <h5 class="card-title">Total Produk</h5>
                        <h2><?php echo $total_produk; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Transaksi Hari Ini -->
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE DATE(tanggal) = CURDATE()");
                        $transaksi_hari_ini = $stmt->fetchColumn();
                        ?>
                        <h5 class="card-title">Transaksi Hari Ini</h5>
                        <h2><?php echo $transaksi_hari_ini; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Pendapatan Hari Ini -->
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->query("SELECT SUM(total) FROM transaksi WHERE DATE(tanggal) = CURDATE()");
                        $pendapatan_hari_ini = $stmt->fetchColumn() ?: 0;
                        ?>
                        <h5 class="card-title">Pendapatan Hari Ini</h5>
                        <h2>Rp <?php echo number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- apriori -->


        <!-- Stok Menipis -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Produk dengan Stok Menipis</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Stok</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM produk WHERE stok <= 5 ORDER BY stok ASC");
                            while ($row = $stmt->fetch()) {
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['kode']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td>
                                        <span class="badge bg-danger"><?php echo $row['stok']; ?></span>
                                    </td>
                                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transaksi Terakhir -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaksi Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Total</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM transaksi ORDER BY tanggal DESC LIMIT 5");
                            while ($row = $stmt->fetch()) {
                            ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                                    <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick="showTransactionDetail(<?php echo $row['id']; ?>)">
                                            Detail
                                        </button>
                                    </td>
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

    <!-- Modal Detail Transaksi -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    <!-- testin -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTransactionDetail(id) {
            // Implementasi AJAX untuk memuat detail transaksi
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        }
    </script>
</body>

</html>