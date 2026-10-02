# Roadmap

Checklist pengerjaan. Centang `[x]` setelah fitur selesai dan teruji.

## Fase 0 — Setup
- [x] Install project Laravel 12 baru (`laravel new pos-app`)
- [x] Install starter kit Inertia + Vue (TypeScript) — `php artisan install:api` tidak dipakai; pakai `laravel new --vue --typescript` atau breeze/jetstream varian Inertia-Vue-TS
- [x] Setup koneksi database PostgreSQL di `.env` (local) — pastikan `pgsql` extension aktif di PHP
- [x] Setup Tailwind CSS (biasanya sudah ikut starter kit, tinggal verifikasi)
- [x] Install & setup Pest (`php artisan pest:install` kalau belum ikut starter kit)
- [x] Install & setup `darkaonline/l5-swagger`, pastikan `/api/documentation` bisa diakses (boleh kosong dulu, isi API-nya nanti)
- [x] Buat struktur folder tambahan: `app/Actions/`, `app/Services/`, `resources/js/types/`
- [x] Setup auth bawaan starter kit (login/register/logout) — sesuaikan nanti dengan role di `docs/PRD.md`
- [x] Buat `.env.example` yang mencerminkan `.env` lokal (tanpa secret asli)
- [x] Pastikan `composer run dev` menjalankan server + Vite tanpa error
- [x] Commit awal: project kosong siap jalan ("hello world" / halaman login tampil)

## Fase 1 — Core POS

### 1.1 Fondasi Data
- [x] Migration `store_settings` (single-row: nama toko, timezone, alamat, telepon) + seeder default
- [x] Migration `users` tambahan (`role`, `is_active`) di atas default Laravel — pastikan PK UUID (`HasUuids`)
- [x] Migration `categories` (self-referencing, `parent_id`)
- [x] Migration `products` (termasuk `stock` sebagai kolom cache, `min_stock`)
- [x] Migration `shifts`
- [x] Migration `transactions` (termasuk kolom void: `voided_by`, `voided_at`, `void_reason`)
- [x] Migration `transaction_items`
- [x] Migration `stock_movements` (polymorphic `reference_type`/`reference_id`)
- [x] Factory + seeder dummy untuk `categories` & `products` (data development)
- [x] Model Eloquent untuk semua tabel di atas, pakai trait `HasUuids`, relasi sesuai `docs/DATABASE.md`

### 1.2 Design System & Komponen Dasar
> Dikerjakan sebelum halaman apa pun dibuat — semua fase berikutnya bergantung pada ini. Daftar lengkap komponen & aturan state ada di `docs/UI.md` bagian "Komponen UI" & "Design Tokens" — jangan duplikasi daftarnya di sini, cukup checklist progres per kelompok.
- [x] Design tokens ditentukan & diterapkan ke konfigurasi Tailwind
- [x] Komponen dasar (Button, Input, Badge, Modal, Table, dll — lihat `docs/UI.md`) selesai dibuat
- [x] Komponen khusus POS (ProductSearch, CartItem, PaymentSummary, ReceiptPreview, dll) selesai dibuat
- [x] Layout (`AdminLayout`, `KasirLayout`, `AuthLayout`) selesai dibuat
- [x] Component showcase dibuat untuk verifikasi visual seluruh komponen & state (`/dev/components`, local only)
- [x] Diterapkan ke halaman Login dan halaman Auth/Profile lain; komponen Breeze lama (PrimaryButton, TextInput, dll) dihapus
- [x] ESLint + Prettier di-setup (belum ada sejak Fase 0, lihat `docs/DECISIONS.md`)

Komponen chart (StatCard, LineChart, dll) sengaja ditunda ke Fase 1.11 — lihat `docs/DECISIONS.md`.

### 1.3 Autentikasi & Role
- [x] Implementasi sesuai `docs/features/auth-login.md`
- [x] Middleware `role:admin` / `role:kasir`
- [x] Middleware `EnsureShiftActive` untuk route kasir
- [x] Setup `routes/admin.php` & `routes/kasir.php` sesuai `AGENTS.md` (pemisahan route per role)
- [x] Redirect login sesuai role + cek shift aktif

### 1.4 Manajemen Produk & Kategori (Admin)
- [x] CRUD Kategori (termasuk pilih parent untuk subkategori)
- [x] CRUD Produk (kategori, SKU/barcode, harga jual, harga modal, stok awal, stok minimum)
- [x] Validasi unik SKU/barcode
- [x] Halaman daftar produk dengan indikator stok menipis/habis

### 1.5 Shift Kasir
- [x] Buka shift (input modal awal kas)
- [x] Tutup shift (rekap kas sistem vs input fisik, tampilkan selisih)
- [x] Constraint satu shift aktif per kasir
- [x] Riwayat shift (admin & kasir)

### 1.6 Transaksi (Checkout) — Fitur Inti
- [x] Implementasi sesuai `docs/features/checkout.md`
- [x] Search produk (nama/SKU) + input barcode scanner
- [x] Keranjang: tambah/kurang quantity, hitung total real-time
- [x] Blokir tambah produk stok 0 ke keranjang
- [x] Form pembayaran (cash/transfer/debit), hitung kembalian otomatis
- [x] `CreateTransactionAction`: insert transaction + items + stock_movements dalam satu `DB::transaction()`
- [x] Generate `transaction_number` unik (tangani race condition)
- [x] Update cache `products.stock` setelah transaksi

### 1.7 Struk
- [ ] Halaman/komponen `ReceiptPreview` format thermal 58mm/80mm
- [ ] Cetak via `window.print()`
- [ ] Cetak ulang struk dari Riwayat Transaksi

### 1.8 Riwayat & Void Transaksi
- [ ] Riwayat transaksi kasir (hanya miliknya)
- [ ] Riwayat transaksi admin (semua, filter tanggal/kasir/status)
- [ ] Detail transaksi
- [ ] Void transaksi (admin only): update status, insert `stock_movements` type `void_return`, wajib alasan

### 1.9 Manajemen Stok
- [ ] Riwayat pergerakan stok (`stock_movements`), filter produk/tipe/tanggal
- [ ] Penyesuaian stok manual (`adjustment`), wajib catatan
- [ ] Alert/badge stok menipis & stok habis

### 1.10 Manajemen User (Admin)
- [ ] CRUD user (set role, set `is_active`)
- [ ] Nonaktifkan user (bukan hapus — riwayat transaksi tetap utuh)

### 1.11 Laporan Dasar
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
