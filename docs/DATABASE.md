# Database Design

## Konvensi
- Primary key: **UUID** di semua tabel (kolom `id`, tipe `uuid`, generate lewat `HasUuids` trait di model Eloquent — bukan auto-increment integer). Lihat `docs/DECISIONS.md` untuk alasan.
- Tipe uang/harga: `integer` (rupiah, tanpa desimal) — lihat `AGENTS.md`.
- Soft delete: pakai `softDeletes()` untuk tabel master (produk, kategori, user) supaya data historis di transaksi lama tidak rusak saat produk/kategori dihapus. **Tabel transaksi (`transactions`, `transaction_items`, `stock_movements`) TIDAK BOLEH dihapus sama sekali — hard delete maupun soft delete.** Tidak ada `softDeletes()`, tidak ada endpoint delete. Gunakan status (`voided`) sesuai `docs/PRD.md` §6. Lihat `docs/DECISIONS.md` dan `AGENTS.md`.
- Timestamp: `timestamps()` default Laravel (`created_at`, `updated_at`) di semua tabel. **Selalu disimpan dalam UTC** (`config('app.timezone')` tetap `UTC`, jangan diubah). Konversi ke zona waktu lokal hanya dilakukan di layer tampilan, berdasarkan `store_settings.timezone` — lihat tabel `store_settings` dan `docs/DECISIONS.md`.

## Entity Relationship (ringkas)
```
store_settings (single row / key-value, lihat catatan di tabelnya)

categories (self-referencing)
  └─ parent_id → categories.id

products
  └─ category_id → categories.id

users
  ├─ shifts.user_id
  └─ transactions.user_id (kasir yang melayani)

shifts
  └─ transactions.shift_id

transactions
  ├─ user_id    → users.id
  ├─ shift_id   → shifts.id
  ├─ voided_by  → users.id (nullable, admin yang void)
  └─ transaction_items (1 transaksi banyak item)
        ├─ transaction_id → transactions.id
        └─ product_id     → products.id

stock_movements
  ├─ product_id → products.id
  └─ reference  → transaction_items.id (polymorphic/nullable, lihat catatan di bawah)
```

## Tabel

### `store_settings`
Pengaturan toko yang bisa diubah lewat aplikasi (bukan hardcode di `.env`/`config`). Single-row table (selalu cuma 1 row) — paling sederhana untuk single-store (lihat PRD §6 & §7).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| store_name | string | |
| timezone | string | Zona waktu IANA, misal `Asia/Jakarta` (WIB), `Asia/Makassar` (WITA), `Asia/Jayapura` (WIT). Dipakai untuk konversi tampilan, lihat Konvensi di atas |
| address | text, nullable | Dipakai di struk |
| phone | string, nullable | Dipakai di struk |
| created_at, updated_at | timestamp | |

**Catatan implementasi:**
- Akses lewat Service/helper (misal `StoreSettings::current()` atau cache singleton), bukan query manual berulang di tiap controller.
- Seeder wajib membuat 1 row default saat `migrate:fresh --seed` (misal `timezone` default `Asia/Jakarta`), supaya aplikasi tidak error karena `store_settings` kosong.
- Perubahan `timezone` di sini TIDAK mengubah data `created_at`/`updated_at` yang sudah tersimpan (tetap UTC) — hanya mengubah cara tampilannya saat dirender.

### `categories`
Struktur kategori & subkategori pakai **1 tabel self-referencing** (lihat `docs/DECISIONS.md`). Subkategori = row `categories` dengan `parent_id` terisi. Mendukung lebih dari 2 level kalau dibutuhkan nanti, meski UI saat ini kemungkinan cuma menampilkan 2 level (kategori → subkategori).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| parent_id | uuid, nullable, FK → categories.id | `null` = kategori utama (root), terisi = subkategori |
| name | string | |
| slug | string, unique | |
| deleted_at | timestamp, nullable | soft delete |
| created_at, updated_at | timestamp | |

**Catatan implementasi:**
- Query ambil kategori root: `Category::whereNull('parent_id')`.
- Query ambil subkategori dari kategori tertentu: `Category::where('parent_id', $id)`.
- Relasi Eloquent: `parent()` (`belongsTo`) dan `children()` (`hasMany`, self).
- Cegah siklus (kategori jadi parent dirinya sendiri secara tidak langsung) — validasi di Form Request, bukan di level database.

### `products`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| category_id | uuid, nullable, FK → categories.id | Boleh merujuk ke kategori root ATAU subkategori |
| name | string | |
| sku | string, unique, nullable | |
| barcode | string, unique, nullable | |
| price | integer | Harga jual, rupiah |
| cost_price | integer, nullable | Harga modal, opsional (lihat PRD §7) |
| stock | integer | **Kolom cache/agregat**, bukan sumber kebenaran — lihat `AGENTS.md` (stok asli dari `stock_movements`) |
| min_stock | integer, default 0 | Untuk alert stok menipis |
| deleted_at | timestamp, nullable | soft delete |
| created_at, updated_at | timestamp | |

### `users`
Role disimpan sebagai kolom `role` (enum/string), bukan tabel `roles` terpisah — cukup untuk 2 role tetap (Admin, Kasir) sesuai PRD §4. Kalau role bertambah dinamis di masa depan, pertimbangkan paket seperti `spatie/laravel-permission`.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| name | string | |
| email | string, unique | |
| password | string | hashed |
| role | string/enum | `admin` atau `kasir` |
| is_active | boolean, default true | Nonaktifkan user tanpa hapus akun (riwayat transaksi tetap utuh) |
| deleted_at | timestamp, nullable | soft delete |
| created_at, updated_at | timestamp | |

### `shifts`
Satu row = satu sesi kerja kasir, dari buka sampai tutup. Lihat PRD §6 (satu kasir hanya boleh satu shift aktif).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| user_id | uuid, FK → users.id | Kasir pemilik shift |
| opening_cash | integer | Modal awal kas saat buka shift |
| closing_cash | integer, nullable | Kas fisik aktual saat tutup shift (diinput kasir) |
| expected_cash | integer, nullable | Kas seharusnya menurut sistem (opening_cash + total transaksi cash), dihitung saat tutup shift |
| status | string/enum | `open`, `closed` |
| opened_at | timestamp | |
| closed_at | timestamp, nullable | |
| created_at, updated_at | timestamp | |

**Catatan implementasi:**
- Constraint "satu kasir satu shift aktif": validasi di Action (`OpenShiftAction`) sebelum insert — cek tidak ada row `status = 'open'` milik `user_id` yang sama. Tidak bisa murni jadi DB constraint karena kondisinya bersyarat (hanya saat status open).
- Selisih kas (`closing_cash - expected_cash`) dihitung di accessor/resource, tidak perlu kolom tersendiri.

### `transactions`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| transaction_number | string, unique | Nomor struk, format disarankan `TRX-YYYYMMDD-XXXX` (human-readable, tetap dibutuhkan terpisah dari `id` UUID karena UUID tidak enak ditulis/dibaca di struk fisik) |
| user_id | uuid, FK → users.id | Kasir yang melayani |
| shift_id | uuid, FK → shifts.id | Shift saat transaksi dibuat |
| subtotal | integer | Jumlah sebelum diskon/pajak |
| total | integer | Total akhir yang harus dibayar |
| paid_amount | integer | Nominal yang dibayar pelanggan |
| change_amount | integer | Kembalian (`paid_amount - total`) |
| payment_method | string/enum | `cash`, `transfer`, `debit` (PRD §3) |
| status | string/enum | `completed`, `voided` — bukan soft delete, lihat Konvensi di atas |
| voided_by | uuid, nullable, FK → users.id | Admin yang melakukan void (PRD §6: hanya admin boleh void) |
| voided_at | timestamp, nullable | |
| void_reason | text, nullable | |
| created_at, updated_at | timestamp | |

### `transaction_items`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| transaction_id | uuid, FK → transactions.id | |
| product_id | uuid, FK → products.id | |
| product_name | string | **Snapshot** nama produk saat transaksi (jaga riwayat kalau nama produk diubah/dihapus kemudian) |
| price | integer | Snapshot harga jual saat transaksi (bukan ambil dari `products.price` saat ini) |
| quantity | integer | |
| subtotal | integer | `price * quantity` |
| created_at, updated_at | timestamp | |

**Catatan implementasi:** kolom `product_name` dan `price` sengaja di-snapshot (duplikasi data), bukan selalu join ke `products`, supaya riwayat transaksi lama tidak berubah kalau harga/nama produk diedit di kemudian hari.

### `stock_movements`
Sumber kebenaran untuk stok (lihat `AGENTS.md`). Kolom `stock` di `products` hanya cache hasil agregat tabel ini.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | uuid, PK | |
| product_id | uuid, FK → products.id | |
| type | string/enum | `in` (barang masuk), `out` (barang keluar/terjual), `adjustment` (koreksi manual), `void_return` (pengembalian stok akibat void transaksi) |
| quantity | integer | Selalu positif; arah perubahan ditentukan oleh `type`, bukan tanda minus |
| reference_type | string, nullable | Polymorphic: nama model sumber, misal `TransactionItem` atau `null` untuk input manual admin |
| reference_id | uuid, nullable | Polymorphic: id dari `reference_type` (pola `morphs` Laravel) |
| note | text, nullable | Catatan manual, misal alasan `adjustment` |
| created_by | uuid, FK → users.id | Siapa yang mencatat (sistem otomatis saat checkout = `user_id` kasir; input manual = admin) |
| created_at, updated_at | timestamp | |

**Catatan implementasi:**
- Stok produk saat ini = `SUM(quantity WHERE type IN ('in','adjustment','void_return')) - SUM(quantity WHERE type = 'out')`, atau cukup simpan signed di query aggregate — detail dibahas saat implementasi.
- Checkout sukses → insert `stock_movements` type `out` per item (dalam `DB::transaction()` yang sama dengan insert transaksi, sesuai `AGENTS.md`).
- Void transaksi → insert `stock_movements` type `void_return` untuk mengembalikan stok (PRD §6), bukan menghapus row `out` yang lama (riwayat tetap utuh).

## Relasi
- `categories.parent_id` → `categories.id` (self-referencing, nullable)
- `products.category_id` → `categories.id`
- `shifts.user_id` → `users.id`
- `transactions.user_id` → `users.id`
- `transactions.shift_id` → `shifts.id`
- `transactions.voided_by` → `users.id` (nullable)
- `transaction_items.transaction_id` → `transactions.id`
- `transaction_items.product_id` → `products.id`
- `stock_movements.product_id` → `products.id`
- `stock_movements.created_by` → `users.id`
- `stock_movements.reference_type` + `reference_id` → polymorphic, biasanya `transaction_items.id`

## Index & Constraint Penting
- Semua primary key `uuid` otomatis ter-index (PK). Pakai tipe kolom `uuid` native PostgreSQL (bukan `char(36)`), dan generate versi UUIDv7 kalau tersedia (`Str::uuid7()` mulai Laravel 11+) — UUIDv7 time-ordered sehingga performa index B-tree tetap bagus, tidak seperti UUIDv4 random yang bikin fragmentasi index pada tabel besar seperti `transactions`.
- `categories.parent_id` — index (query filter by parent sering dipakai).
- `categories.slug` — unique index.
- `products.sku`, `products.barcode` — unique index (nullable unique, boleh banyak produk tanpa barcode).
- `products.category_id` — index.
- `users.email` — unique index.
- `shifts.user_id` + `status` — composite index (cek shift aktif per kasir).
- `transactions.transaction_number` — unique index.
- `transactions.shift_id`, `transactions.user_id`, `transactions.status` — index (filter laporan & riwayat).
- `transaction_items.transaction_id` — index.
- `stock_movements.product_id` + `type` — composite index (hitung stok per produk cepat).
- `stock_movements.reference_type` + `reference_id` — composite index (polymorphic lookup).

## Hal yang Masih Perlu Diputuskan
- Apakah `transaction_number` digenerate sekuensial per hari (`TRX-20261002-0001`)? Ini tetap dibutuhkan terpisah dari `id` (UUID) karena UUID tidak praktis ditulis di struk fisik. Format sekuensial butuh locking saat generate (hindari race condition di `DB::transaction()`).
- Kolom `stock` di `products` — generated/cached kapan? Opsi: dihitung ulang tiap ada `stock_movements` baru (event/observer), atau dihitung on-the-fly tiap request (lebih lambat tapi selalu akurat). Perlu diputuskan saat implementasi Fase 1.
- Diskon & pajak (PRD §6, masih `perlu diisi`) belum punya kolom di `transactions`/`transaction_items` — menyusul setelah aturan bisnisnya final.

