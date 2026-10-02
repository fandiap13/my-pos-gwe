# Progress Snapshot

> File ini untuk ONBOARDING AI/developer baru yang melanjutkan project ini.
> Update manual tiap kali selesai satu fase/sub-fase besar — jangan andalkan
> AI mengingat ini otomatis, karena setiap sesi AI dimulai tanpa memori sesi sebelumnya.

## Status Saat Ini

**Fase aktif:** Fase 1 — Core POS, sub-fase **1.6 (Transaksi/Checkout) selesai dikerjakan & teruji (93 test lulus)** — masih di **working tree, BELUM di-commit**, menunggu review user sebelum commit/push.
**Belum dikerjakan:** 1.7 (Struk) dan seterusnya — lihat `docs/ROADMAP.md`.

**Commit terakhir:** `f45e4a8` "feat: kolom No., nilai kosong '-', kontrol baris/halaman (maks 100)".
**PENTING — working tree saat ini BELUM di-commit. Jalankan `git status` & `git diff` sebelum lanjut, jangan menganggap repo bersih:**
- Perubahan **Fase 1.6 (checkout)**: `app/Actions/Transaction/CreateTransactionAction.php`, `app/Http/Controllers/Kasir/TransactionController.php`, `app/Http/Requests/Kasir/StoreTransactionRequest.php`, route `kasir.transaksi` / `kasir.transaksi.store` / `kasir.transaksi.selesai` (`routes/kasir.php`), halaman `resources/js/Pages/Kasir/Transaksi/{Index,Selesai}.vue` (menggantikan placeholder `Transaksi.vue`), `tests/Feature/Kasir/TransactionTest.php`, seed stok awal (`DatabaseSeeder`, `ProductSeeder`), penyesuaian review (`PaymentMethodSelector` prop `status`, tombol **Kembali** di `KasirLayout`, demo di `ComponentShowcase`), update docs (ROADMAP 1.6 dicentang, DECISIONS 5 entri Fase 1.6, DATABASE, UI).
- Perubahan dari **luar sesi ini** (milik user/sesi lain — jangan dihapus, jangan dicampur ke satu commit tanpa konfirmasi): `docs/ROADMAP.md` bagian 1.12 + `docs/features/admin-global-search.md` (requirement Admin Global Search), **pesan validasi Bahasa Indonesia via `messages()` per Form Request** (8 file request termasuk `StoreTransactionRequest`, `tests/Feature/ValidationMessageTest.php`, entri DECISIONS teratas — `lang/id/` sempat ada lalu dihapus, locale tetap `en`).

## Yang Sudah Jadi (Verified, Bukan Asumsi)

- Laravel 12 + Inertia.js + Vue 3 (TypeScript) + Tailwind + Pest — semua jalan (`composer run dev`)
- PostgreSQL terkoneksi, 10 migration jalan bersih (`php artisan migrate:fresh --seed`)
- 7 tabel inti POS + `users` (UUID semua) sesuai `docs/DATABASE.md`: `store_settings`, `categories`, `products`, `users`, `shifts`, `transactions`, `transaction_items`, `stock_movements`
- Model Eloquent lengkap dengan relasi, trait `HasUuids`
- `StockMovementObserver` — cache `products.stock` dihitung ulang otomatis, **sudah diuji manual** (lihat `docs/DECISIONS.md`)
- Swagger (`l5-swagger`) terpasang, `/api/documentation` jalan, 1 endpoint contoh `/api/health`
- Seeder: 2 user (`admin@example.com`, `kasir@example.com`, password `password` dari factory default), 7 kategori, 14 produk termasuk skenario stok habis/menipis
- 21 test lulus (`php artisan test`), Pint clean
- Design system lengkap sesuai `docs/UI.md`: design tokens di `tailwind.config.js` (warna, radius, font Inter via Google Fonts), 26 komponen dasar + 7 komponen POS di `resources/js/Components/`, 3 Layout (`AdminLayout`, `KasirLayout`, `AuthLayout`), composable `useToast`
- ESLint + Prettier ter-setup (`npm run lint`, `npm run format`) mengikuti stub resmi Breeze Vue+TS — lihat `docs/DECISIONS.md`
- Component showcase di `/dev/components` (route local-only) untuk verifikasi visual semua komponen
- Halaman Login & seluruh halaman Auth/Profile sudah pakai komponen & `AuthLayout` baru; komponen Breeze lama (PrimaryButton, TextInput, GuestLayout, AuthenticatedLayout, dll) sudah dihapus total
- `@tailwindcss/vite` (v4, tidak terpakai) dihapus — project tetap di Tailwind v3
- Middleware `role:admin`/`role:kasir` (`EnsureUserHasRole`) dan `shift.active` (`EnsureShiftActive`) — lihat `docs/DECISIONS.md`
- `routes/admin.php` & `routes/kasir.php` terdaftar via `bootstrap/app.php` — route nyata: `admin.dashboard`, `admin.*` (produk/kategori/shifts), `kasir.transaksi` (+ `.store`/`.selesai`), `kasir.shift.*`
- Redirect setelah login terpusat di `app/Actions/Auth/DetermineLoginRedirectAction.php` — admin ke dashboard, kasir dengan shift ke transaksi, kasir tanpa shift ke buka shift. Dipakai juga oleh 4 controller Breeze (password confirm, email verification) yang semula hardcode `route('dashboard')` (route itu sudah dihapus)
- Validasi `is_active` di `LoginRequest` — akun nonaktif ditolak login meski kredensial benar
- Pages placeholder tersisa: `Pages/Admin/Dashboard.vue` (isi menyusul Fase 1.11). `Pages/Kasir/Transaksi.vue` sudah DIGANTI halaman sungguhan `Pages/Kasir/Transaksi/{Index,Selesai}.vue` (Fase 1.6)
- CRUD Kategori penuh (`Admin/Categories/{Index,Create,Edit}.vue`): self-referencing parent, validasi anti-siklus, blokir hapus kalau masih ada produk/subkategori — lihat `docs/DECISIONS.md`
- CRUD Produk penuh (`Admin/Products/{Index,Create,Edit}.vue`): search + filter kategori + pagination di Index, validasi SKU/barcode unik, badge status stok (aman/menipis/habis)
- Stok awal produk dicatat via `stock_movements` (bukan isi kolom `stock` langsung) lewat `CreateProductAction` — lihat `docs/DECISIONS.md`. Form Edit Produk TIDAK punya field stok sama sekali (perubahan stok nanti lewat Penyesuaian Manual, Fase 1.9)
- Bug fix `Input.vue` (`type="number"` dulu diam-diam emit string, sekarang emit number) dan `Select.vue` (placeholder dulu tidak bisa dipilih balik untuk reset ke `null`) — lihat `docs/DECISIONS.md`, berdampak ke semua pemakaian komponen ini di seluruh app
- `AdminLayout` menu sidebar: "Dashboard", "Daftar Produk", "Kategori", "Riwayat Shift" sekarang route nyata; sisanya (Stok, Transaksi, Laporan, Pengguna, Pengaturan) masih `href="#"`
- Fase 1.5 Shift Kasir selesai & teruji (76 test lulus)
- **Buka/Tutup Shift (kasir)**: `kasir.shift.buka`/`kasir.shift.store`, `kasir.shift.tutup`/`kasir.shift.close` — Action di `app/Actions/Shift/` (`OpenShiftAction`, `CloseShiftAction`, `CalculateExpectedCashAction`), Form Request di `app/Http/Requests/Kasir/`
- **Constraint satu shift aktif per kasir** divalidasi di `OpenShiftAction` dalam `DB::transaction()` + `lockForUpdate()` baris `users` (cegah race check-then-insert)
- **`expected_cash` dihitung backend saat tutup**: `opening_cash` + transaksi cash berstatus `completed` (transfer & voided tidak dihitung); preview rekap di halaman Tutup Shift memakai Action yang sama
- **Riwayat shift**: halaman `Kasir/Shift/Riwayat.vue` (milik sendiri, di luar middleware `shift.active` supaya bisa dilihat setelah shift ditutup) & `Admin/Shifts/Index.vue` (semua kasir, filter status + nama kasir, route `admin.shifts.index`)
- **`ShiftResource`** (konversi UTC → timezone `store_settings` di layer Resource) + `ShiftFactory`; `tests/TestCase.php` kini men-seed `store_settings` karena Resource butuh row itu
- **`shiftIsActive` & `flash` di-share `HandleInertiaRequests`** → KasirLayout baca dari shared props (badge shift + link "Tutup Shift"), `Toast.vue` menampilkan flash `success`/`error` dari redirect backend sebagai toast
- **Bug fix penting:** props **single Resource** yang dilempar mentah (`new ProductResource($p)`) dibungkus Inertia jadi `{data: ...}` lewat jalur `Responsable` → halaman Edit Produk/Kategori sebelumnya membaca `undefined`. Sekarang semua pakai `->resolve()` — lihat `docs/DECISIONS.md`
- 76 test lulus (16 shift kasir + 4 riwayat shift admin + 2 regression Edit + 54 lainnya), Pint, ESLint, `vue-tsc`/`npm run build` clean
- **Fase 1.6 Transaksi/Checkout selesai & teruji (total 93 test lulus)**:
  - **Backend:** `StoreTransactionRequest` (struktur items + `distinct` + exists anti-soft-delete), `CreateTransactionAction` — semua dalam satu `DB::transaction()`: `lockForUpdate` shift aktif → row `store_settings` → baris produk; validasi ulang stok/harga/harga-bayar dengan pesan key `checkout` menyebut produk bermasalah; insert `transactions` (`status=completed`) + `transaction_items` (snapshot nama/harga) + `stock_movements` type `out` (reference ke `transaction_items`); cache `products.stock` ikut ter-update via `StockMovementObserver`
  - **`transaction_number` `TRX-YYYYMMDD-XXXX`** sekuensial per hari **zona waktu toko**, di-generate aman race via lock row `store_settings` (tanpa tabel counter) — lihat `docs/DECISIONS.md`
  - **Route:** `kasir.transaksi` (GET `/kasir`, grup `shift.active`), `kasir.transaksi.store` (POST), `kasir.transaksi.selesai` (GET struk — **di luar** `shift.active`, cek kepemilikan 403)
  - **Halaman:** `Kasir/Transaksi/Index.vue` (katalog semua produk sebagai props + filter client-side — `cost_price` TIDAK dikirim ke kasir; Enter = konfirmasi scan barcode; keranjang + `CartItem`/`QuantityInput`; modal pembayaran `PaymentMethodSelector` + `MoneyInput` + `PaymentSummary` hitung kembalian real-time; banner error backend) dan `Kasir/Transaksi/Selesai.vue` (props `receipt` shape `Receipt` + `ReceiptPreview`, tombol "Cetak Struk" `window.print()` & "Transaksi Baru"; header `KasirLayout` `print:hidden`)
  - **Test:** 11 test checkout di `tests/Feature/Kasir/TransactionTest.php` (sukses penuh, stok kurang→rollback, harga berubah, bayar kurang, tanpa shift, non-cash pakai total, nomor urut, snapshot awet, struk milik sendiri/403, produk terhapus)
  - **Seed konsisten:** `ProductSeeder` menambah movement `in` stok awal (tanpa ini checkout produk seed bikin stok negatif — observer hitung dari movements saja); `DatabaseSeeder` buat user sebelum product — lihat `docs/DECISIONS.md`
  - **Penyesuaian review:** `PaymentMethodSelector` kini punya prop `status` (default `true`; `false` → semua tombol metode nonaktif + notice "Fitur belum siap"; demo status false ada di `ComponentShowcase`). `KasirLayout` kini punya tombol **Kembali** (ikon panah) ke `/kasir` di header — tersembunyi otomatis saat sedang di halaman `Kasir/Transaksi/Index` atau `Kasir/Shift/Buka` — lihat `docs/UI.md`

## Yang BELUM Ada (Jangan Diasumsikan Sudah Jadi)

- **Cetak struk belum sempurna (Fase 1.7):** `window.print()` sudah tersambung dari halaman Selesai, tapi `@media print` untuk ukuran kertas thermal 58mm/80mm di `app.css` belum dibuat, dan **belum ada cetak ulang dari Riwayat Transaksi**. `ReceiptPreview` sudah ada & dipakai.
- **Belum ada Riwayat Transaksi** (kasir & admin) dan **belum ada Void transaksi** (Fase 1.8).
- Komponen chart (`StatCard`, `LineChart`, `BarChart`, dll) di `docs/UI.md` **sengaja belum dibuat** — ditunda ke Fase 1.11, lihat `docs/DECISIONS.md`. Chart.js/`vue-chartjs` juga belum ter-install.
- Tidak ada halaman **Stok — Riwayat Pergerakan** / **Penyesuaian Manual** (Fase 1.9) — `stock_movements` sudah bisa diisi lewat `CreateProductAction`, tapi belum ada UI untuk melihat riwayatnya atau input manual (barang masuk/koreksi) di luar saat create produk.
- Modal inline untuk tambah kategori cepat dari form Produk **sengaja tidak dibuat** (halaman terpisah dipilih) — lihat `docs/DECISIONS.md`.

## Keputusan Penting yang HARUS Dibaca Sebelum Lanjut

Baca `docs/DECISIONS.md` secara lengkap — berisi keputusan arsitektur yang sudah final dan TIDAK BOLEH diubah tanpa diskusi ulang dengan user:

1. Primary key semua tabel = UUID (bukan auto-increment)
2. Timestamp disimpan UTC, timezone tampilan dari `store_settings` (data, bukan `.env`)
3. Data transaksi (`transactions`, `transaction_items`, `stock_movements`) **tidak boleh dihapus** sama sekali (hard maupun soft delete)
4. Route dipisah file per role: `routes/admin.php` + `routes/kasir.php`
5. Kategori pakai 1 tabel self-referencing (`parent_id`), bukan 2 tabel terpisah
6. Cache `products.stock` dihitung via Model Observer (`StockMovementObserver`)
7. Cetak struk pakai `window.print()`, bukan ESC/POS (untuk sekarang)
8. Fitur "Delete Account" & halaman register publik bawaan Breeze **sengaja dihapus** — jangan dikembalikan
9. Design tokens (warna, font Inter, dll) di `tailwind.config.js` & `docs/UI.md` — jangan ubah salah satu tanpa mengubah yang lain (harus tetap sinkron)
10. Komponen chart ditunda ke Fase 1.11, chart library = Chart.js/`vue-chartjs` (bukan ApexCharts)
11. Icon = `@lucide/vue` (bukan `lucide-vue-next`, versi lama sudah deprecated — jangan install ulang package lama ini)
12. Redirect setelah login SELALU terpusat di `DetermineLoginRedirectAction` — jangan pakai `redirect()->intended()` atau hardcode `route('dashboard')` di controller manapun (route itu sudah tidak ada)
13. `shift.active` middleware TIDAK BOLEH dipasang di route `kasir.shift.buka` — akan menyebabkan redirect loop (kasir tanpa shift tidak bisa membuka shift-nya sendiri)
14. Stok produk HANYA boleh berubah lewat `stock_movements` (`StockMovement::create()`), tidak pernah lewat `Product::update(['stock' => ...])` langsung — form Edit Produk sengaja tidak punya field stok
15. Kategori yang masih punya produk/subkategori anak tidak bisa dihapus — lihat `DeleteCategoryAction`, jangan hapus validasi ini
16. Tambah kategori dari form Produk pakai halaman terpisah (`/admin/categories/create`), bukan modal inline — ini keputusan final, bukan sementara
17. **Halaman list yang dipaginasi WAJIB kirim paginator flat ke Inertia** (`$query->paginate()->through(fn ($item) => (new XResource($item))->resolve())`), JANGAN `XResource::collection($paginator)` — yang kedua membungkus data jadi `{data, links, meta}` nested, tidak cocok dengan `Paginated<T>`/`Pagination.vue` yang mengasumsikan shape flat. Ini pernah bikin halaman Produk blank total (lihat `docs/DECISIONS.md`) — selalu tambahkan test `assertInertia()` yang cek shape untuk halaman list baru, jangan cuma `assertOk()`.
18. **Props single Resource juga wajib `->resolve()`** — `new XResource($model)` mentah dibungkus Inertia jadi `{data: {...}}` lewat jalur `Responsable` (`JsonResource::$wrap = 'data'`), yang dulu bikin form Edit Produk/Kategori baca `undefined`. Berlaku untuk semua halaman detail/edit.
19. **Shift (Fase 1.5):** `expected_cash` dihitung backend (modal awal + transaksi **cash `completed`** — transfer & voided tidak dihitung), bukan dari frontend; satu shift aktif per kasir dijamin `lockForUpdate()` di `OpenShiftAction`; riwayat shift kasir (`kasir.shift.riwayat`) TIDAK dijaga middleware `shift.active`.
20. **`shiftIsActive` & `flash` dishare lewat `HandleInertiaRequests`** — jangan kirim prop shift per halaman; `Toast.vue` sudah jadi jembatan flash→toast, cukup `->with('success', ...)` di controller.
21. **Checkout (Fase 1.6):** katalog produk dimuat penuh ke props & difilter client-side (scan barcode harus instan, tanpa request); `cost_price` tidak dipilih untuk kasir; submit SELALU divalidasi ulang server (stok, harga == harga harapan client, shift open, bayar cukup) dengan error key `checkout`; `transaction_number` di-generate dengan `lockForUpdate()` row `store_settings` (hari = zona waktu toko) — lihat `docs/DECISIONS.md`.
22. **Halaman struk `kasir.transaksi.selesai` berada DI LUAR middleware `shift.active`** (dijaga cek kepemilikan, 403 kalau bukan transaksi sendiri) — transaksi sudah tersimpan, struk harus tetap terbuka walau shift ditutup. Jangan memindahkannya ke dalam grup `shift.active`.

## Cara Melanjutkan (Prompt Starter untuk AI Baru)

Paste ini ke AI/sesi baru untuk memulai dengan konteks yang benar:

```
Baca file-file ini secara berurutan sebelum mulai kerja apa pun:
1. AGENTS.md — aturan wajib, jangan dilanggar
2. PROGRESS.md (file ini) — status project saat ini
3. docs/DECISIONS.md — keputusan final, jangan diubah tanpa tanya dulu
4. docs/ROADMAP.md — cari item yang belum dicentang [ ], itu yang dikerjakan
5. docs/DATABASE.md, docs/PRD.md, docs/UI.md — konteks produk sesuai kebutuhan
6. docs/features/*.md — spec detail kalau mengerjakan fitur yang sudah ada filenya

Lanjutkan dari Fase 1.7 (Struk) di docs/ROADMAP.md —
baca docs/features/checkout.md (langkah 10) & docs/DECISIONS.md
keputusan Fase 1.6 dulu. CATATAN: perubahan Fase 1.6 masih di working tree
(belum commit) sampai user menyetujui — cek `git status` dulu.
Jalankan `php artisan test` setelah tiap perubahan, jangan nyatakan selesai
tanpa verifikasi nyata (migration benar-benar jalan, test benar-benar lulus).
```

## Catatan Lingkungan Lokal

- PHP 8.3.16, ekstensi `pgsql`/`pdo_pgsql` sudah aktif di `php.ini`
- PostgreSQL 18 terinstal, database `db_pos_diposkan` sudah ada
- `laravel/pail` SENGAJA tidak dipakai — butuh ekstensi `pcntl` yang tidak ada di PHP Windows
- `.env` sudah terisi lengkap (lihat `.env.example` untuk strukturnya, tanpa password asli)
