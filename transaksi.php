<?php
require_once 'config/database.php';

// Handler untuk request AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] == 'simpan_transaksi') {
        try {
            $pdo->beginTransaction();

            // Insert ke tabel transaksi
            $stmt = $pdo->prepare("INSERT INTO transaksi (tanggal, total) VALUES (NOW(), ?)");
            $stmt->execute([$_POST['total']]);
            $transaksi_id = $pdo->lastInsertId();

            // Insert detail transaksi dan update stok
            $items = json_decode($_POST['items'], true);
            $stmt_detail = $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_stok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

            foreach ($items as $item) {
                $stmt_detail->execute([
                    $transaksi_id,
                    $item['id'],
                    $item['qty'],
                    $item['harga'],
                    $item['subtotal']
                ]);

                $stmt_stok->execute([$item['qty'], $item['id']]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Toko Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
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
        <div class="row">
            <!-- Daftar Produk -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Produk</h5>
                            <input type="text" id="searchInput" class="form-control form-control-sm w-auto"
                                placeholder="Cari produk...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="productList">
                            <?php
                            $stmt = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama");
                            while ($row = $stmt->fetch()) {
                            ?>
                                <div class="col-md-4 mb-3 product-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($row['nama']); ?></h6>
                                            <p class="card-text">
                                                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?><br>
                                                Stok: <?php echo $row['stok']; ?>
                                            </p>
                                            <button class="btn btn-sm btn-primary w-100 add-to-cart"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                                data-harga="<?php echo $row['harga']; ?>"
                                                data-stok="<?php echo $row['stok']; ?>">
                                                Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keranjang -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Keranjang</h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-items">
                            <!-- Items will be added here -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Total:</h5>
                            <h5 id="total">Rp 0</h5>
                        </div>
                        <button class="btn btn-success w-100" id="btnProcess">Proses Transaksi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <script>
        // Array untuk menyimpan item di keranjang
        let cartItems = [];

        // Format number ke Rupiah
        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Update tampilan keranjang
        function updateCart() {
            const cartDiv = document.getElementById('cart-items');
            const totalDiv = document.getElementById('total');
            let total = 0;

            cartDiv.innerHTML = '';
            cartItems.forEach((item, index) => {
                total += item.subtotal;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'mb-3';
                itemDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${item.nama}</strong><br>
                            ${formatRupiah(item.harga)} x ${item.qty}
                        </div>
                        <div class="text-end">
                            <div>${formatRupiah(item.subtotal)}</div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="updateQuantity(${index}, -1)">-</button>
                                <button class="btn btn-outline-primary" onclick="updateQuantity(${index}, 1)">+</button>
                                <button class="btn btn-outline-danger" onclick="removeItem(${index})">×</button>
                            </div>
                        </div>
                    </div>
                `;
                cartDiv.appendChild(itemDiv);
            });

            totalDiv.textContent = formatRupiah(total);
        }

        // Update jumlah item
        function updateQuantity(index, change) {
            const item = cartItems[index];
            const newQty = item.qty + change;

            if (newQty > item.stok) {
                Swal.fire('Error', 'Stok tidak mencukupi!', 'error');
                return;
            }

            if (newQty > 0) {
                item.qty = newQty;
                item.subtotal = item.harga * newQty;
            }

            updateCart();
        }

        // Hapus item dari keranjang
        function removeItem(index) {
            cartItems.splice(index, 1);
            updateCart();
        }

        // Event listener untuk tombol tambah ke keranjang
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const product = button.dataset;
                const existingItem = cartItems.find(item => item.id === product.id);

                if (existingItem) {
                    if (existingItem.qty < parseInt(product.stok)) {
                        existingItem.qty++;
                        existingItem.subtotal = existingItem.harga * existingItem.qty;
                    } else {
                        Swal.fire('Error', 'Stok tidak mencukupi!', 'error');
                        return;
                    }
                } else {
                    cartItems.push({
                        id: product.id,
                        nama: product.nama,
                        harga: parseInt(product.harga),
                        qty: 1,
                        stok: parseInt(product.stok),
                        subtotal: parseInt(product.harga)
                    });
                }

                updateCart();
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.querySelector('.card-title').textContent.toLowerCase();
                if (name.includes(search)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Proses transaksi
        document.getElementById('btnProcess').addEventListener('click', () => {
            if (cartItems.length === 0) {
                Swal.fire('Error', 'Keranjang masih kosong!', 'error');
                return;
            }

            const total = cartItems.reduce((sum, item) => sum + item.subtotal, 0);

            Swal.fire({
                title: 'Konfirmasi Transaksi',
                text: `Total pembayaran: ${formatRupiah(total)}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Proses',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim data transaksi ke server
                    const formData = new FormData();
                    formData.append('action', 'simpan_transaksi');
                    formData.append('items', JSON.stringify(cartItems));
                    formData.append('total', total);

                    fetch('transaksi.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Sukses', data.message, 'success').then(() => {
                                    cartItems = [];
                                    updateCart();
                                    location.reload(); // Refresh untuk update stok
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        });
                }
            });
        });
    </script>
</body>

</html>