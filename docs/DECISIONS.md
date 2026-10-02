# Decision Log

Catatan keputusan teknis/produk yang sudah final, supaya AI tidak mengubah atau mempertanyakan lagi. Tambahkan entri baru di atas (terbaru dulu).

## Template
### [YYYY-MM-DD] Judul keputusan
- **Keputusan:**
- **Alasan:**
- **Alternatif yang ditolak:**

---

### [2026-10-02] Cetak struk pakai window.print(), bukan ESC/POS
- **Keputusan:** Cetak struk sementara menggunakan `window.print()` dari browser (halaman HTML yang diformat untuk ukuran kertas thermal 58mm/80mm), bukan library ESC/POS.
- **Alasan:** Server Laravel tidak bisa langsung menyentuh printer USB yang terpasang di komputer kasir. ESC/POS asli butuh perantara seperti QZ Tray atau print agent lokal yang harus diinstal di tiap komputer kasir — kompleksitas ini belum dibutuhkan di versi awal.
- **Alternatif yang ditolak:** QZ Tray/print agent lokal (ditunda — akan dievaluasi ulang kalau ditemukan cara bypass tanpa software tambahan), network printer IP/LAN (hanya relevan kalau printer fisiknya tipe LAN, belum tentu).
- **Dampak:** Fitur "Konfigurasi Printer" (pilih printer, test print, cek status koneksi) dihapus dari scope versi awal. Lihat `docs/PRD.md` §5.1 dan §6.

---

### [2026-10-02] Pemisahan route & halaman per role: file terpisah (admin.php / kasir.php)
- **Keputusan:** Route admin dan kasir dipisah ke file masing-masing (`routes/admin.php`, `routes/kasir.php`), bukan digabung dalam satu `routes/web.php` dengan middleware per route. Halaman Vue juga dipisah folder: `resources/js/Pages/Admin/` dan `resources/js/Pages/Kasir/`.
- **Alasan:** Jumlah route admin (produk, kategori, user, laporan, pengaturan) akan jauh lebih banyak daripada route kasir begitu semua fitur di PRD §5.1 dikerjakan. Pemisahan dari awal lebih murah daripada merapikan setelah `web.php` gemuk di Fase 2–3. Middleware role dipasang sekali per grup, mengurangi risiko ada route admin yang lupa diberi guard role.
- **Alternatif yang ditolak:** Satu `routes/web.php` dengan middleware role per route (setup lebih minim tapi rawan lupa pasang middleware di route baru, dan file cepat panjang); menunda keputusan sampai saat coding (berisiko struktur tidak konsisten antar sesi pengerjaan).
- **Dampak:**
  - `bootstrap/app.php` perlu mendaftarkan `routes/admin.php` dan `routes/kasir.php` lewat `withRouting(then: ...)`.
  - Route name pakai prefix role: `admin.products.index`, `kasir.transactions.store`. Route shared (profil, logout) tetap di `routes/web.php` tanpa prefix.
  - Lihat `AGENTS.md` bagian Prinsip Arsitektur & Konvensi Kode untuk aturan lengkap, contoh kode ada di riwayat percakapan — akan dipindah ke sini atau ke `docs/features/` saat dibutuhkan.

---

### [2026-10-02] Kategori produk: 1 tabel self-referencing, bukan 2 tabel terpisah
- **Keputusan:** Kategori & subkategori produk disimpan dalam satu tabel `categories` dengan kolom `parent_id` yang merujuk ke `categories.id` sendiri (self-referencing), bukan dua tabel terpisah (`categories` + `subcategories`).
- **Alasan:** Lebih fleksibel untuk mendukung lebih dari 2 level kategori di masa depan tanpa migrasi skema ulang, dan tidak mengikat desain ke asumsi "selalu 2 level".
- **Alternatif yang ditolak:** 2 tabel terpisah (`categories` + `subcategories`) — lebih simpel & query lebih mudah untuk kasus 2 level saja, tapi tidak fleksibel kalau kebutuhan berubah.
- **Dampak:** Query tree/breadcrumb kategori butuh query bertingkat atau recursive (bukan cuma 1 join). Lihat `docs/DATABASE.md` tabel `categories` untuk skema & catatan implementasi.

---

### [2026-10-02] Data transaksi tidak boleh dihapus — hard delete maupun soft delete
- **Keputusan:** Tabel `transactions`, `transaction_items`, dan `stock_movements` tidak boleh dihapus sama sekali, baik hard delete maupun soft delete (`deleted_at`). Tidak ada kolom `softDeletes()` di migration tabel-tabel ini, dan tidak ada endpoint/method delete untuknya.
- **Alasan:** Menghapus data transaksi — walau soft delete — berisiko merusak integritas data: laporan penjualan jadi tidak akurat, riwayat stok terputus, dan audit trail hilang. "Pembatalan" transaksi harus tetap meninggalkan jejak, bukan menyembunyikan data.
- **Alternatif yang ditolak:** Soft delete (`deleted_at`) — ditolak karena secara default menyembunyikan row dari query (`SoftDeletes` trait otomatis exclude dari `all()`/`get()`), yang kontradiktif dengan kebutuhan "riwayat transaksi harus bisa ditelusuri" (PRD §2).
- **Dampak:** Pembatalan transaksi ditangani lewat kolom `status = 'voided'` (lihat `docs/DATABASE.md` tabel `transactions`), bukan delete. Stok dikembalikan lewat insert row baru `stock_movements` type `void_return`, bukan menghapus row `out` yang lama. Lihat `AGENTS.md` bagian Prinsip Arsitektur untuk larangan eksplisit.

---

### [2026-10-02] Primary key pakai UUID, bukan auto-increment integer
- **Keputusan:** Semua tabel pakai UUID sebagai primary key (`id`, tipe `uuid`), bukan `bigint` auto-increment. Disarankan UUIDv7 (`Str::uuid7()`, time-ordered) kalau tersedia di versi Laravel yang dipakai, supaya performa index B-tree tetap bagus dibanding UUIDv4 random.
- **Alasan:** Permintaan eksplisit user. UUID juga punya manfaat praktis untuk POS: ID tidak bisa ditebak/diurut (mengurangi risiko enumerasi `/admin/transactions/5` → `/admin/transactions/6`), dan aman dipakai sebagai referensi publik (misal di URL struk) tanpa membocorkan jumlah transaksi/row.
- **Alternatif yang ditolak:** `bigint` auto-increment (default Laravel) — lebih ringan secara storage/index dan lebih gampang dibaca manusia, tapi ditolak sesuai keputusan user.
- **Dampak:**
  - Semua model Eloquent pakai trait `HasUuids`.
  - Semua migration: kolom `id` jadi `$table->uuid('id')->primary()`, semua foreign key jadi `$table->foreignUuid(...)`.
  - `transactions.transaction_number` tetap dipertahankan sebagai kolom terpisah (string pendek, human-readable) karena UUID tidak praktis dicetak di struk fisik atau diucapkan ke pelanggan.
  - Lihat `docs/DATABASE.md` untuk skema lengkap yang sudah disesuaikan.

---

### [2026-10-02] Timestamp disimpan UTC, zona waktu tampilan diatur lewat data (store_settings), bukan config statis
- **Keputusan:** Semua `created_at`/`updated_at` dan timestamp lain disimpan dalam UTC (`config('app.timezone')` tetap `UTC`). Zona waktu untuk tampilan (struk, laporan, riwayat transaksi) diambil dari kolom `store_settings.timezone` (data yang bisa diubah lewat aplikasi), bukan nilai tetap di `.env`/`config/app.php`.
- **Alasan:** User ingin timezone bisa diatur dari dalam aplikasi, bukan hardcode saat deploy. Menyimpan dalam UTC adalah best practice standar (default Laravel, tidak perlu migrasi data kalau toko pindah zona waktu atau produk ini dipakai di toko lain dengan zona waktu berbeda), konversi ke lokal hanya dilakukan di layer tampilan.
- **Alternatif yang ditolak:** Menyimpan timestamp langsung dalam zona waktu lokal di database (lebih sederhana untuk dibaca langsung dari DB, tapi berisiko ambigu/salah kalau config berubah, dan tidak portable); zona waktu hardcode di `.env` (`APP_TIMEZONE=Asia/Jakarta`) — ditolak karena tidak bisa diubah tanpa redeploy.
- **Dampak:**
  - Tabel baru `store_settings` (single-row) dengan kolom `timezone` (format IANA, misal `Asia/Jakarta`), dibuat seed default saat `migrate:fresh --seed`.
  - Konversi UTC → lokal dilakukan di `Http/Resources` atau accessor saat data dikirim ke frontend, tidak pernah di level query/database.
  - Lihat `docs/DATABASE.md` tabel `store_settings` untuk skema & catatan implementasi.

---

### [2026-10-02] Checkout diblokir total kalau stok produk = 0 (stok tidak pernah minus)
- **Keputusan:** Produk dengan stok 0 tidak bisa ditambah ke keranjang/checkout. Stok tidak pernah bernilai minus. Validasi stok dilakukan di client (UX, cepat) DAN wajib divalidasi ulang di server saat submit transaksi (sumber kebenaran), untuk menangani race condition saat dua kasir checkout produk yang sama secara bersamaan.
- **Alasan:** Mencegah data stok minus yang membingungkan dan tidak konsisten dengan prinsip `stock_movements` sebagai sumber kebenaran tunggal (`AGENTS.md`). Lebih mudah dipahami kasir: stok habis = tidak bisa dijual, titik.
- **Alternatif yang ditolak:** Tetap izinkan checkout walau stok 0 (stok jadi minus, dikoreksi manual admin nanti) — ditolak karena berisiko stok minus menumpuk tanpa disadari kalau admin lupa koreksi, dan membingungkan saat lihat laporan stok.
- **Dampak:**
  - `CreateTransactionAction` (lihat `AGENTS.md`) wajib cek `products.stock >= quantity` untuk tiap item di dalam `DB::transaction()` yang sama dengan insert — kalau gagal, rollback semua dan kembalikan error, bukan partial success.
  - UI: tombol tambah ke keranjang nonaktif untuk produk stok 0, dan server tetap jadi validasi final kalau terjadi race condition. Lihat `docs/UI.md` bagian Alur Stok Menipis & Stok Habis.
  - Koreksi stok fisik ekstra (ditemukan barang tambahan) hanya lewat halaman **Stok — Penyesuaian Manual** (`stock_movements` type `adjustment`), tidak lewat alur transaksi penjualan.

---
