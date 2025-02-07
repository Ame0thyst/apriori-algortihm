# Sistem Toko Harian dengan Analisis Apriori 🏪

Sistem manajemen toko harian sederhana dengan fitur analisis pola pembelian menggunakan algoritma Apriori. Sistem ini dibangun menggunakan PHP native dan MySQL.

## 📋 Fitur Utama

- ⚡ Dashboard interaktif
- 📦 Manajemen produk
- 💰 Sistem kasir
- 📊 Laporan penjualan
- 🔍 Analisis pola pembelian (Apriori)
- 🖨️ Export laporan ke Excel

## 🚀 Cara Instalasi

1. **Persiapan Lingkungan**

   ```bash
   # Pastikan XAMPP sudah terinstall
   # Clone repository ini ke folder htdocs
   cd C:/xampp/htdocs
   git clone https://github.com/Ame0thyst/apriori-algortihm.git
   cd toko-harian
   ```

2. **Setup Database**

   - Start Apache dan MySQL di XAMPP
   - Buka browser dan akses: `http://localhost/apriori-algortihm/database/migration.php`
   - Tunggu proses migrasi selesai

3. **Akses Aplikasi**
   - Buka browser
   - Akses: `http://localhost/apriori-algortihm`

## 💡 Cara Kerja Algoritma Apriori

Algoritma Apriori dalam sistem ini bekerja dengan tahapan:

1. **Pengumpulan Data Transaksi**

   - Sistem mengumpulkan data transaksi dari tabel `transaksi` dan `detail_transaksi`
   - Data dikelompokkan berdasarkan transaksi yang terjadi bersamaan

2. **Parameter Analisis**

   - `Minimum Support`: Frekuensi minimum kemunculan pola (default: 20%)
   - `Minimum Confidence`: Tingkat kepercayaan minimum pola (default: 50%)

3. **Proses Analisis**

   ```plaintext
   Contoh:
   Total 100 transaksi
   30 transaksi membeli Mie + Telur (Support: 30%)
   Dari 40 pembeli Mie, 30 membeli Telur (Confidence: 75%)
   ```

4. **Hasil Analisis**
   - Menampilkan pola pembelian yang memenuhi minimum support dan confidence
   - Memberikan rekomendasi penataan produk berdasarkan pola

## 📈 Cara Menggunakan Analisis Apriori

1. **Persiapan Data**

   - Tambahkan minimal 10 produk di menu Produk
   - Lakukan minimal 20 transaksi dengan pola yang sering terjadi
   - Contoh pola transaksi yang baik:
     ```
     Transaksi 1: Mie + Telur + Saos
     Transaksi 2: Mie + Telur + Kecap
     Transaksi 3: Telur + Kecap + Saos
     ```

2. **Melakukan Analisis**

   - Buka menu "Analisis Apriori"
   - Atur parameter:
     - Min Support: 0.2 (20%)
     - Min Confidence: 0.5 (50%)
   - Pilih rentang tanggal
   - Klik "Analisis"

3. **Interpretasi Hasil**

   ```plaintext
   Jika membeli [Mie, Telur]
   Maka membeli [Saos]
   Support: 25%
   Confidence: 75%

   Artinya:
   - 25% dari seluruh transaksi membeli Mie, Telur, dan Saos
   - 75% pembeli Mie dan Telur juga membeli Saos
   ```

## 🎯 Rekomendasi Penggunaan

1. **Jumlah Data Minimum**

   - Minimal 10 jenis produk
   - Minimal 20 transaksi
   - Rentang waktu minimal 1 minggu

2. **Setting Parameter**

   - Untuk toko kecil:
     - Min Support: 0.1 - 0.2 (10-20%)
     - Min Confidence: 0.4 - 0.6 (40-60%)
   - Untuk toko besar:
     - Min Support: 0.05 - 0.1 (5-10%)
     - Min Confidence: 0.3 - 0.5 (30-50%)

3. **Optimasi Hasil**
   - Analisis secara berkala (mingguan/bulanan)
   - Sesuaikan parameter berdasarkan jumlah transaksi
   - Gunakan hasil untuk:
     - Penataan produk
     - Bundling produk
     - Strategi promosi

## 📝 Struktur Database

```sql
-- Struktur tabel utama
toko_harian/
├── produk
│   ├── id
│   ├── kode
│   ├── nama
│   ├── harga
│   └── stok
│
├── transaksi
│   ├── id
│   ├── tanggal
│   └── total
│
├── detail_transaksi
│   ├── id
│   ├── transaksi_id
│   ├── produk_id
│   ├── jumlah
│   ├── harga
│   └── subtotal
│
└── apriori_rules
    ├── id
    ├── antecedent
    ├── consequent
    ├── support
    └── confidence
```

## 🛠️ Teknologi yang Digunakan

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- JavaScript
- HTML5/CSS3

## 📄 Lisensi

MIT License - Silakan gunakan dan modifikasi sesuai kebutuhan.

## 🤝 Kontribusi

Kontribusi selalu welcome! Silakan buat pull request atau laporkan issues.

---

⌨️ Developed dengan ❤️ oleh TIM
