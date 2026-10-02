# Roadmap

Checklist pengerjaan. Centang `[x]` setelah fitur selesai dan teruji.

## Fase 0 — Setup
- [ ] Install project Laravel 12 baru (`laravel new pos-app`)
- [ ] Install starter kit Inertia + Vue (TypeScript) — `php artisan install:api` tidak dipakai; pakai `laravel new --vue --typescript` atau breeze/jetstream varian Inertia-Vue-TS
- [ ] Setup koneksi database PostgreSQL di `.env` (local) — pastikan `pgsql` extension aktif di PHP
- [ ] Setup Tailwind CSS (biasanya sudah ikut starter kit, tinggal verifikasi)
- [ ] Install & setup Pest (`php artisan pest:install` kalau belum ikut starter kit)
- [ ] Install & setup `darkaonline/l5-swagger`, pastikan `/api/documentation` bisa diakses (boleh kosong dulu, isi API-nya nanti)
- [ ] Buat struktur folder tambahan: `app/Actions/`, `app/Services/`, `resources/js/types/`
- [ ] Setup auth bawaan starter kit (login/register/logout) — sesuaikan nanti dengan role di `docs/PRD.md`
- [ ] Buat `.env.example` yang mencerminkan `.env` lokal (tanpa secret asli)
- [ ] Pastikan `composer run dev` menjalankan server + Vite tanpa error
- [ ] Commit awal: project kosong siap jalan ("hello world" / halaman login tampil)

## Fase 1 — Core POS

### 1.1 Fondasi Data
- [ ] Migration `store_settings` (single-row: nama toko, timezone, alamat, telepon) + seeder default
- [ ] Migration `users` tambahan (`role`, `is_active`) di atas default Laravel — pastikan PK UUID (`HasUuids`)
- [ ] Migration `categories` (self-referencing, `parent_id`)
- [ ] Migration `products` (termasuk `stock` sebagai kolom cache, `min_stock`)
- [ ] Migration `shifts`
- [ ] Migration `transactions` (termasuk kolom void: `voided_by`, `voided_at`, `void_reason`)
- [ ] Migration `transaction_items`
- [ ] Migration `stock_movements` (polymorphic `reference_type`/`reference_id`)
- [ ] Factory + seeder dummy untuk `categories` & `products` (data development)
- [ ] Model Eloquent untuk semua tabel di atas, pakai trait `HasUuids`, relasi sesuai `docs/DATABASE.md`

### 1.2 Autentikasi & Role
- [ ] Implementasi sesuai `docs/features/auth-login.md`
- [ ] Middleware `role:admin` / `role:kasir`
- [ ] Middleware `EnsureShiftActive` untuk route kasir
- [ ] Setup `routes/admin.php` & `routes/kasir.php` sesuai `AGENTS.md` (pemisahan route per role)
- [ ] Redirect login sesuai role + cek shift aktif

### 1.3 Manajemen Produk & Kategori (Admin)
- [ ] CRUD Kategori (termasuk pilih parent untuk subkategori)
- [ ] CRUD Produk (kategori, SKU/barcode, harga jual, harga modal, stok awal, stok minimum)
- [ ] Validasi unik SKU/barcode
- [ ] Halaman daftar produk dengan indikator stok menipis/habis

### 1.4 Shift Kasir
- [ ] Buka shift (input modal awal kas)
- [ ] Tutup shift (rekap kas sistem vs input fisik, tampilkan selisih)
- [ ] Constraint satu shift aktif per kasir
- [ ] Riwayat shift (admin & kasir)

### 1.5 Transaksi (Checkout) — Fitur Inti
- [ ] Implementasi sesuai `docs/features/checkout.md`
- [ ] Search produk (nama/SKU) + input barcode scanner
- [ ] Keranjang: tambah/kurang quantity, hitung total real-time
- [ ] Blokir tambah produk stok 0 ke keranjang
- [ ] Form pembayaran (cash/transfer/debit), hitung kembalian otomatis
- [ ] `CreateTransactionAction`: insert transaction + items + stock_movements dalam satu `DB::transaction()`
- [ ] Generate `transaction_number` unik (tangani race condition)
- [ ] Update cache `products.stock` setelah transaksi

### 1.6 Struk
- [ ] Halaman/komponen `ReceiptPreview` format thermal 58mm/80mm
- [ ] Cetak via `window.print()`
- [ ] Cetak ulang struk dari Riwayat Transaksi

### 1.7 Riwayat & Void Transaksi
- [ ] Riwayat transaksi kasir (hanya miliknya)
- [ ] Riwayat transaksi admin (semua, filter tanggal/kasir/status)
- [ ] Detail transaksi
- [ ] Void transaksi (admin only): update status, insert `stock_movements` type `void_return`, wajib alasan

### 1.8 Manajemen Stok
- [ ] Riwayat pergerakan stok (`stock_movements`), filter produk/tipe/tanggal
- [ ] Penyesuaian stok manual (`adjustment`), wajib catatan
- [ ] Alert/badge stok menipis & stok habis

### 1.9 Manajemen User (Admin)
- [ ] CRUD user (set role, set `is_active`)
- [ ] Nonaktifkan user (bukan hapus — riwayat transaksi tetap utuh)

### 1.10 Laporan Dasar
- [ ] Laporan penjualan per rentang tanggal
- [ ] Laporan per shift/kasir
- [ ] Produk terlaris

## Fase 2 — Fitur Tambahan
- [ ] Finalisasi aturan diskon (PRD §6 masih "perlu diisi") → update PRD & DATABASE dulu sebelum coding
- [ ] Finalisasi aturan pajak/PPN (PRD §6 masih "perlu diisi") → update PRD & DATABASE dulu sebelum coding
- [ ] Dashboard ringkas admin (grafik penjualan, produk terlaris)
- [ ] Export laporan ke Excel/PDF
- [ ] Manajemen pelanggan (member/poin) — opsional, perlu didetailkan dulu di PRD kalau jadi dipakai

## Fase 3 — Polish & Deploy
- [ ] Pengaturan Toko (nama, alamat, telepon, timezone) — halaman admin untuk `store_settings`
- [ ] Review & lengkapi dokumentasi Swagger untuk endpoint `routes/api.php` (kalau ada)
- [ ] Audit keamanan dasar (rate limiting, validasi role di semua route, CSRF)
- [ ] Review performa query (N+1, index) terutama di Laporan & Riwayat Transaksi
- [ ] Setup environment production (`.env` production, build asset)
- [ ] Testing menyeluruh end-to-end alur kasir (buka shift → transaksi → tutup shift)
- [ ] Deploy

## Backlog (belum dijadwalkan)
- Mode offline-first (di luar scope PRD saat ini, lihat PRD §3)
- Integrasi payment gateway/QRIS otomatis (di luar scope PRD saat ini)
- Multi-cabang (di luar scope PRD saat ini)
- Integrasi ESC/POS asli (lihat `docs/DECISIONS.md` — ditunda, dievaluasi ulang kalau ada solusi tanpa agent lokal)
