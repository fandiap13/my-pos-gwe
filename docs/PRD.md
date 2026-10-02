# Product Requirements Document

## 1. Latar Belakang
Toko DiPosKan saat ini masih menggunakan pencatatan transaksi penjualan secara manual melalui buku dan/atau Excel. Proses tersebut membuat pengelolaan penjualan dan stok menjadi kurang efisien, serta meningkatkan risiko kesalahan pencatatan dan perhitungan.

Seiring meningkatnya kebutuhan akan pengelolaan data yang lebih cepat, akurat, dan terintegrasi, diperlukan digitalisasi proses operasional toko melalui aplikasi Point of Sale (POS). Aplikasi ini dirancang untuk membantu kasir dalam mencatat transaksi penjualan, mengelola dan memantau stok secara terpusat, serta membantu pemilik toko dalam memantau dan merekap data penjualan.

Dengan adanya digitalisasi tersebut, proses pencatatan transaksi, pengelolaan stok, dan pemantauan penjualan diharapkan dapat dilakukan secara lebih terstruktur dan mudah dilacak.

## 2. Tujuan
- Mempercepat proses transaksi di kasir (checkout < 1 menit per transaksi).
- Stok produk selalu akurat secara real-time setelah setiap transaksi.
- Pemilik bisa melihat laporan penjualan harian/bulanan tanpa rekap manual.
- Sistem menyediakan riwayat transaksi dan pergerakan stok yang dapat ditelusuri berdasarkan kasir, waktu, item yang terjual, serta aktivitas barang masuk dan barang keluar akibat transaksi penjualan.

## 3. Scope

### Termasuk (In Scope)

- Manajemen produk (CRUD, kategori, subkategori, harga, dan stok).
- Transaksi penjualan (checkout) oleh kasir.
- Pembaruan stok otomatis berdasarkan transaksi penjualan.
- Pencatatan pergerakan stok, termasuk barang masuk dan barang keluar.
- Riwayat transaksi beserta detail setiap transaksi.
- Laporan penjualan (harian, rentang tanggal, dan bulanan).
- Manajemen pengguna dan role (Admin dan Kasir).
- Pencetakan struk transaksi (POS 58mm/80mm) lewat browser print, atau download struk sebagai fallback.
- Pencatatan metode pembayaran (cash, transfer, dan debit) sebagai informasi transaksi.

### Tidak Termasuk (Out of Scope)

- Multi-cabang / multi-outlet (asumsi satu toko dan satu lokasi).
- Integrasi payment gateway atau pembayaran digital otomatis (QRIS/e-wallet). Pembayaran dicatat secara manual berdasarkan metode pembayaran yang dipilih.
- Manajemen supplier dan purchase order (pembelian stok dari supplier).
- Aplikasi mobile terpisah. Sistem dikembangkan sebagai web app responsive.
- Aplikasi berfokus pada **mode kasir untuk transaksi penjualan**, sedangkan pengelolaan produk, stok, pengguna, dan laporan dilakukan melalui mode admin.


## 4. User Role

| Role | Deskripsi | Hak Akses |
|---|---|---|
| Admin/Owner | Pemilik toko, akses penuh | Kelola produk, kategori, dan subkategori; kelola user; lihat seluruh laporan penjualan; lihat seluruh riwayat transaksi; void transaksi; kelola pengaturan toko; kelola barang masuk dan keluar,  |
| Kasir | Petugas yang melayani transaksi | Buat transaksi baru, lihat riwayat transaksi miliknya, buka/tutup shift, cetak struk |

## 5. Daftar Fitur

### 5.1 Fitur Inti

- **Autentikasi**: login, logout, dan pembatasan akses berdasarkan role (Admin/Kasir).
- **Manajemen Produk**: tambah/edit/hapus produk, kategori produk, SKU/barcode, harga jual, harga modal (opsional untuk menghitung profit), dan stok awal.
- **Transaksi (Checkout)**: cari produk berdasarkan nama/barcode, tambah produk ke keranjang, atur jumlah/quantity, hitung total otomatis, pilih metode pembayaran, input nominal pembayaran, hitung kembalian, dan simpan transaksi.
- **Manajemen Stok**: stok berkurang otomatis setelah transaksi berhasil, pencatatan riwayat pergerakan stok (stock movement), stok minimum, dan alert stok menipis.
- **Riwayat Transaksi**: daftar transaksi dengan filter tanggal dan kasir, detail item setiap transaksi, serta cetak ulang struk.
- **Laporan**: total penjualan per hari/rentang tanggal, laporan bulanan, laporan tahunan, produk terlaris, serta laporan berdasarkan kasir/shift.
- **Shift Kasir**: buka shift dengan modal awal kas, tutup shift dengan rekap kas aktual dibandingkan dengan kas berdasarkan sistem, riwayat shift, dan riwayat transaksi yang dilakukan kasir.
- **Cetak Struk**: mencetak struk lewat `window.print()` dari browser ke printer thermal (58mm/80mm) menggunakan halaman struk HTML yang diformat khusus untuk ukuran kertas thermal. Struk berisi nomor transaksi, waktu, item, total pembayaran, nominal yang dibayar, dan kembalian. Tidak ada integrasi ESC/POS langsung di versi awal — lihat `docs/DECISIONS.md`.


### 5.2 Fitur Tambahan (nice to have)
- Diskon per item / per transaksi (persentase atau nominal).
- Retur / void transaksi (dengan approval admin).
- Export laporan ke Excel/PDF.
- Dashboard ringkas (grafik penjualan, produk terlaris) untuk admin.
- Manajemen pelanggan (member/poin, opsional).

## 6. Aturan Bisnis
- **Diskon**: belum aktif di versi awal (lihat 5.2). Kalau diaktifkan: diskon per item ATAU per transaksi (pilih salah satu dulu), maksimal diskon perlu ditentukan, siapa yang boleh memberi diskon (kasir langsung atau perlu approval admin) — *perlu diisi*.
- **Pajak**: belum ada PPN (toko retail kecil, non-PKP). Kalau diperlukan, tentukan: PPN 11% include atau exclude dari harga jual — *perlu diisi*.
- **Retur / void transaksi**: hanya admin yang boleh void transaksi (bukan kasir). Void mengembalikan stok otomatis. Transaksi yang sudah di-void tetap tercatat di riwayat (soft delete / status `voided`), tidak dihapus permanen.
- **Shift kasir**: satu kasir hanya bisa punya satu shift aktif dalam satu waktu. Transaksi hanya bisa dibuat saat shift dalam status aktif (terbuka). Tutup shift wajib input jumlah kas fisik untuk dibandingkan dengan sistem.
- **Cetak Struk**: menggunakan `window.print()` dari browser ke printer thermal 58mm/80mm (lihat `docs/DECISIONS.md` untuk alasan). Jika di kemudian hari ditemukan solusi bypass tanpa QZ Tray/agent lokal yang tetap mendukung ESC/POS asli, keputusan ini akan dievaluasi ulang.
- **Multi-cabang / single-store**: single-store (1 toko, 1 lokasi). Semua data produk & stok bersifat global, tidak ada pemisahan per cabang.
- **Satuan uang & pembulatan**: Rupiah, tanpa desimal (bulat). Disimpan sebagai integer di database (bukan float), lihat `AGENTS.md`. Pembulatan kembalian: tidak ada pembulatan khusus, kembalian dihitung pas (uang dibayar − total belanja).
- **Stok habis**: produk dengan stok 0 diblokir dari checkout (tidak bisa ditambah ke keranjang). Stok tidak pernah minus. Validasi ulang dilakukan di server saat submit transaksi untuk menghindari race condition (dua kasir checkout produk yang sama bersamaan). Koreksi stok manual lewat `stock_movements` type `adjustment`, bukan lewat transaksi penjualan. Lihat `docs/UI.md` & `docs/DECISIONS.md`.

## 7. Asumsi & Batasan
- Satu toko, satu lokasi fisik — tidak perlu sinkronisasi data antar cabang.
- Koneksi internet stabil tersedia di lokasi toko (tidak perlu mode offline-first).
- Satu kasir aktif per device/terminal (tidak dirancang untuk multi-kasir di 1 device bersamaan).
- Barcode scanner (jika dipakai) terbaca sebagai input keyboard biasa (USB/Bluetooth HID), tidak perlu driver khusus.
- Harga modal produk opsional diisi — kalau tidak diisi, fitur laporan profit tidak akurat/tidak ditampilkan.
