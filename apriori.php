<?php
require_once 'config/database.php';

class AprioriAnalysis
{
    private $pdo;
    private $min_support;
    private $min_confidence;
    private $transactions;
    private $items;

    public function __construct($pdo, $min_support = 0.1, $min_confidence = 0.5)
    {
        $this->pdo = $pdo;
        $this->min_support = $min_support;
        $this->min_confidence = $min_confidence;
        $this->transactions = [];
        $this->items = [];
    }

    // Mengambil data transaksi dari database
    public function loadTransactions($start_date, $end_date)
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id, p.nama as produk
            FROM transaksi t
            JOIN detail_transaksi dt ON t.id = dt.transaksi_id
            JOIN produk p ON dt.produk_id = p.id
            WHERE DATE(t.tanggal) BETWEEN ? AND ?
            ORDER BY t.id
        ");
        $stmt->execute([$start_date, $end_date]);

        $current_transaction = [];
        $current_id = null;

        while ($row = $stmt->fetch()) {
            if ($current_id !== $row['id']) {
                if (!empty($current_transaction)) {
                    $this->transactions[] = $current_transaction;
                }
                $current_transaction = [];
                $current_id = $row['id'];
            }
            $current_transaction[] = $row['produk'];
            $this->items[$row['produk']] = true;
        }

        if (!empty($current_transaction)) {
            $this->transactions[] = $current_transaction;
        }

        $this->items = array_keys($this->items);
    }

    // Menghitung support untuk itemset
    private function calculateSupport($itemset)
    {
        $count = 0;
        foreach ($this->transactions as $transaction) {
            if (count(array_intersect($itemset, $transaction)) == count($itemset)) {
                $count++;
            }
        }
        return $count / count($this->transactions);
    }

    // Mendapatkan frequent itemsets
    private function getFrequentItemsets($k)
    {
        if ($k == 1) {
            $frequent_itemsets = [];
            foreach ($this->items as $item) {
                $support = $this->calculateSupport([$item]);
                if ($support >= $this->min_support) {
                    $frequent_itemsets[] = [$item];
                }
            }
            return $frequent_itemsets;
        }

        $prev_frequent_itemsets = $this->getFrequentItemsets($k - 1);
        $candidates = [];

        // Generate kandidat
        foreach ($prev_frequent_itemsets as $itemset1) {
            foreach ($prev_frequent_itemsets as $itemset2) {
                $merged = array_unique(array_merge($itemset1, $itemset2));
                if (count($merged) == $k) {
                    sort($merged);
                    $candidates[implode(',', $merged)] = $merged;
                }
            }
        }

        // Filter berdasarkan minimum support
        $frequent_itemsets = [];
        foreach ($candidates as $candidate) {
            $support = $this->calculateSupport($candidate);
            if ($support >= $this->min_support) {
                $frequent_itemsets[] = $candidate;
            }
        }

        return $frequent_itemsets;
    }

    // Generate association rules
    public function generateRules()
    {
        $rules = [];
        $max_length = 3; // Batasi panjang itemset untuk performa

        for ($k = 2; $k <= $max_length; $k++) {
            $frequent_itemsets = $this->getFrequentItemsets($k);

            foreach ($frequent_itemsets as $itemset) {
                $itemset_support = $this->calculateSupport($itemset);

                // Generate semua kemungkinan antecedent
                $subset_count = pow(2, count($itemset)) - 2; // -2 untuk mengabaikan kosong dan set lengkap
                for ($i = 1; $i <= $subset_count; $i++) {
                    $antecedent = [];
                    $consequent = [];

                    for ($j = 0; $j < count($itemset); $j++) {
                        if ($i & (1 << $j)) {
                            $antecedent[] = $itemset[$j];
                        } else {
                            $consequent[] = $itemset[$j];
                        }
                    }

                    if (!empty($antecedent) && !empty($consequent)) {
                        $antecedent_support = $this->calculateSupport($antecedent);
                        $confidence = $itemset_support / $antecedent_support;

                        if ($confidence >= $this->min_confidence) {
                            $rules[] = [
                                'antecedent' => $antecedent,
                                'consequent' => $consequent,
                                'support' => $itemset_support,
                                'confidence' => $confidence
                            ];
                        }
                    }
                }
            }
        }

        return $rules;
    }
}

// Halaman analisis Apriori
if (!isset($_GET['min_support'])) {
    $_GET['min_support'] = 0.1;
}
if (!isset($_GET['min_confidence'])) {
    $_GET['min_confidence'] = 0.5;
}
if (!isset($_GET['tanggal_awal'])) {
    $_GET['tanggal_awal'] = date('Y-m-d', strtotime('-1 month'));
}
if (!isset($_GET['tanggal_akhir'])) {
    $_GET['tanggal_akhir'] = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Apriori - Toko Harian</title>
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
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="produk.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transaksi.php">Kasir</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan.php">Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="apriori.php">Analisis Apriori</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Analisis Pola Pembelian (Apriori)</h2>

        <!-- Form Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control" name="tanggal_awal"
                            value="<?php echo $_GET['tanggal_awal']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="tanggal_akhir"
                            value="<?php echo $_GET['tanggal_akhir']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min. Support</label>
                        <input type="number" class="form-control" name="min_support"
                            value="<?php echo $_GET['min_support']; ?>"
                            step="0.01" min="0" max="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min. Confidence</label>
                        <input type="number" class="form-control" name="min_confidence"
                            value="<?php echo $_GET['min_confidence']; ?>"
                            step="0.01" min="0" max="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Analisis</button>
                    </div>
                </form>
            </div>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tanggal_awal'])) {
            $apriori = new AprioriAnalysis(
                $pdo,
                floatval($_GET['min_support']),
                floatval($_GET['min_confidence'])
            );

            $apriori->loadTransactions($_GET['tanggal_awal'], $_GET['tanggal_akhir']);
            $rules = $apriori->generateRules();

            if (empty($rules)) {
                echo '<div class="alert alert-warning">Tidak ditemukan pola pembelian yang signifikan dengan parameter yang diberikan.</div>';
            } else {
        ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Hasil Analisis Apriori</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Jika Membeli</th>
                                        <th>Maka Membeli</th>
                                        <th>Support</th>
                                        <th>Confidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules as $rule): ?>
                                        <tr>
                                            <td><?php echo implode(', ', $rule['antecedent']); ?></td>
                                            <td><?php echo implode(', ', $rule['consequent']); ?></td>
                                            <td><?php echo number_format($rule['support'] * 100, 1); ?>%</td>
                                            <td><?php echo number_format($rule['confidence'] * 100, 1); ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Rekomendasi Penataan Produk</h5>
                        <ul class="list-group">
                            <?php foreach ($rules as $rule): ?>
                                <li class="list-group-item">
                                    <strong>Rekomendasi:</strong> Letakkan produk "<?php echo implode('" dan "', $rule['consequent']); ?>"
                                    berdekatan dengan produk "<?php echo implode('" dan "', $rule['antecedent']); ?>"
                                    karena <?php echo number_format($rule['confidence'] * 100, 1); ?>% pelanggan yang membeli
                                    <?php echo implode(' dan ', $rule['antecedent']); ?> juga membeli
                                    <?php echo implode(' dan ', $rule['consequent']); ?>.
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>