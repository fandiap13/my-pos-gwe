# Fitur: Transaksi (Checkout)

## Deskripsi
Kasir mencari produk, menambahkannya ke keranjang, lalu menyelesaikan pembayaran. Sistem menghitung total otomatis, mencatat transaksi, mengurangi stok, dan menyediakan struk untuk dicetak. Ini adalah fitur paling sering dipakai di seluruh aplikasi — harus cepat dan minim friksi.

## User Role Terkait
- Kasir — satu-satunya role yang bisa membuat transaksi. Wajib punya shift `status = open` (lihat `docs/features/auth-login.md`).

## Alur
1. Kasir berada di halaman Transaksi (`/kasir`), shift sudah aktif (digate oleh middleware, lihat `auth-login.md`).
2. Kasir cari produk lewat `ProductSearchInput` (nama/SKU) atau scan barcode (barcode scanner terbaca sebagai keyboard input biasa, lihat PRD §7).
3. Produk ditemukan → klik/scan untuk tambah ke keranjang (`CartTable`). Kalau stok produk = 0, tombol tambah nonaktif (lihat `docs/UI.md` & `docs/DECISIONS.md`).
4. Kasir atur quantity per item (+/-, atau input manual). Quantity tidak boleh melebihi stok tersedia (validasi client, dikonfirmasi ulang di server).
5. Subtotal & total dihitung otomatis dan real-time saat keranjang berubah.
6. Kasir klik "Bayar" → pilih `payment_method` (`cash`/`transfer`/`debit`).
7. Kalau `cash` → input nominal dibayar, sistem hitung `change_amount` otomatis (`paid_amount - total`). Kalau `transfer`/`debit`, nominal dibayar = total (tidak ada kembalian, lihat Aturan Bisnis).
8. Kasir konfirmasi → submit ke server.
9. Server (dalam satu `DB::transaction()`):
   a. Validasi ulang stok tiap item (`products.stock >= quantity`) — kalau ada yang gagal, rollback semua, kembalikan error spesifik item mana yang bermasalah.
   b. Generate `transaction_number` (format `TRX-YYYYMMDD-XXXX`, lihat `docs/DATABASE.md` — "Hal yang Masih Perlu Diputuskan").
   c. Insert `transactions` (snapshot `subtotal`, `total`, `paid_amount`, `change_amount`, `payment_method`, `status = completed`, `shift_id` = shift aktif kasir).
   d. Insert `transaction_items` per item, snapshot `product_name` & `price` saat ini (bukan live reference ke `products`).
   e. Insert `stock_movements` type `out` per item (`reference_type/id` ke `transaction_items`).
   f. Update cache `products.stock` (lihat `docs/DATABASE.md` — cara kalkulasi masih perlu diputuskan: observer/event atau on-the-fly).
10. Transaksi sukses → tampilkan `ReceiptPreview`, kasir bisa klik "Cetak" (`window.print()`) atau "Transaksi Baru" (skip cetak).
11. Keranjang dikosongkan, kembali ke langkah 2.

## Aturan Bisnis Khusus
- Stok 0 memblokir checkout, divalidasi di client DAN server (lihat `docs/DECISIONS.md` — "Checkout diblokir total kalau stok produk = 0").
- Uang disimpan & dihitung sebagai integer rupiah, tanpa desimal, tanpa pembulatan khusus (lihat `AGENTS.md`, PRD §6).
- Metode pembayaran `transfer`/`debit`: sistem **tidak** memvalidasi bukti transfer/EDC secara otomatis (di luar scope — lihat PRD §3, tidak ada integrasi payment gateway). Kasir mencatat metode secara manual sebagai informasi saja; asumsikan nominal dibayar = total (tidak ada kembalian untuk metode non-cash) — *perlu dikonfirmasi kalau asumsi ini salah*.
- Diskon & pajak: **belum ada di versi ini** (PRD §6 masih "perlu diisi"). Jangan tambahkan kolom/logic diskon-pajak sampai aturan bisnisnya difinalkan di PRD.
- Transaksi yang sudah dibuat tidak bisa diedit — hanya bisa di-void oleh admin (lihat `docs/DECISIONS.md` — data transaksi tidak boleh dihapus).
- Satu transaksi hanya terikat ke satu shift (`shift_id`) — kalau shift ditutup di tengah proses checkout (kasus tepi/edge case), submit harus ditolak dengan pesan jelas, bukan menyimpan transaksi ke shift yang sudah closed.

## Data yang Terlibat
- Tabel: `transactions`, `transaction_items`, `stock_movements`, `products` (baca saja untuk cek stok & harga), `shifts` (baca untuk `shift_id` aktif)
- Field penting: `transactions.status`, `transactions.payment_method`, `transaction_items.price` (snapshot), `stock_movements.type = 'out'`

## Acceptance Criteria
- [ ] Kasir tanpa shift aktif tidak bisa mengakses halaman Transaksi (redirect ke Buka Shift).
- [ ] Produk dengan stok 0 tidak bisa ditambah ke keranjang.
- [ ] Total & kembalian terhitung benar untuk berbagai kombinasi quantity dan nominal bayar.
- [ ] Setelah checkout sukses: `transactions` ter-insert dengan `status = completed`, `transaction_items` sesuai isi keranjang, `stock_movements` type `out` ter-insert per item, dan `products.stock` (cache) berkurang sesuai.
- [ ] Checkout gagal (misal stok berubah jadi 0 di antara waktu kasir menambah ke keranjang dan submit) → tidak ada row yang ter-insert sama sekali (rollback penuh), pesan error jelas.
- [ ] `transaction_number` unik, tidak collision meski dua transaksi dibuat bersamaan (race condition saat generate nomor).
- [ ] Snapshot `product_name`/`price` di `transaction_items` tidak berubah meski produk aslinya diedit setelahnya.
- [ ] Test: Feature test checkout sukses, checkout gagal karena stok habis, checkout dengan shift tidak aktif (Pest).

## Catatan Teknis
- Logic utama di `app/Actions/CreateTransactionAction.php` (lihat `AGENTS.md` — single-action class), dipanggil dari `TransactionController@store`.
- Validasi input lewat `StoreTransactionRequest` (Form Request): struktur items (array product_id + quantity), payment_method, paid_amount.
- Seluruh langkah 9 di atas WAJIB dalam satu `DB::transaction()` — kalau satu langkah gagal, semua rollback (lihat `AGENTS.md`).
- Precision race condition saat generate `transaction_number` sekuensial: pertimbangkan `lockForUpdate()` pada query counter, atau pakai pendekatan lain yang dibahas di `docs/DATABASE.md`.
- `ReceiptPreview` sebaiknya halaman/komponen terpisah yang menerima `transaction_id`, dipakai juga oleh fitur "cetak ulang struk" di Riwayat Transaksi — jangan duplikasi markup struk di dua tempat.
