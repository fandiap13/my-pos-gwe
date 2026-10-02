# Progress Snapshot

> File ini untuk ONBOARDING AI/developer baru yang melanjutkan project ini.
> Update manual tiap kali selesai satu fase/sub-fase besar — jangan andalkan
> AI mengingat ini otomatis, karena setiap sesi AI dimulai tanpa memori sesi sebelumnya.

## Status Saat Ini

**Fase aktif:** Fase 1 — Core POS, baru selesai sub-fase **1.4 (Manajemen Produk & Kategori)**.
**Belum dikerjakan:** 1.5 (Shift Kasir) dan seterusnya — lihat `docs/ROADMAP.md`.

**Commit terakhir:** lihat `git log --oneline -1` — commit terakhir berjudul "feat: Fase 1.4 — CRUD Kategori & Produk (Admin)".

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
- `routes/admin.php` & `routes/kasir.php` terdaftar via `bootstrap/app.php`, berisi route placeholder: `admin.dashboard`, `kasir.transaksi`, `kasir.shift.buka`
- Redirect setelah login terpusat di `app/Actions/Auth/DetermineLoginRedirectAction.php` — admin ke dashboard, kasir dengan shift ke transaksi, kasir tanpa shift ke buka shift. Dipakai juga oleh 4 controller Breeze (password confirm, email verification) yang semula hardcode `route('dashboard')` (route itu sudah dihapus)
- Validasi `is_active` di `LoginRequest` — akun nonaktif ditolak login meski kredensial benar
- Pages placeholder: `Pages/Admin/Dashboard.vue`, `Pages/Kasir/Transaksi.vue`, `Pages/Kasir/Shift/Buka.vue` (isi sungguhan menyusul di Fase 1.5/1.6/1.11)
- CRUD Kategori penuh (`Admin/Categories/{Index,Create,Edit}.vue`): self-referencing parent, validasi anti-siklus, blokir hapus kalau masih ada produk/subkategori — lihat `docs/DECISIONS.md`
- CRUD Produk penuh (`Admin/Products/{Index,Create,Edit}.vue`): search + filter kategori + pagination di Index, validasi SKU/barcode unik, badge status stok (aman/menipis/habis)
- Stok awal produk dicatat via `stock_movements` (bukan isi kolom `stock` langsung) lewat `CreateProductAction` — lihat `docs/DECISIONS.md`. Form Edit Produk TIDAK punya field stok sama sekali (perubahan stok nanti lewat Penyesuaian Manual, Fase 1.9)
- Bug fix `Input.vue` (`type="number"` dulu diam-diam emit string, sekarang emit number) dan `Select.vue` (placeholder dulu tidak bisa dipilih balik untuk reset ke `null`) — lihat `docs/DECISIONS.md`, berdampak ke semua pemakaian komponen ini di seluruh app
- `AdminLayout` menu sidebar: "Dashboard", "Daftar Produk", "Kategori" sekarang route nyata; sisanya (Stok, Transaksi, Laporan, Pengguna, Pengaturan) masih `href="#"`
- 57 test lulus (12 CategoryTest + 13 ProductTest baru), Pint & ESLint clean

## Yang BELUM Ada (Jangan Diasumsikan Sudah Jadi)

- **Belum ada checkout/shift sungguhan** — `Pages/Kasir/` masih 2 halaman placeholder (Transaksi, Shift/Buka), isi sungguhan menyusul Fase 1.5 (Shift) dan 1.6 (Checkout).
- Komponen chart (`StatCard`, `LineChart`, `BarChart`, dll) di `docs/UI.md` **sengaja belum dibuat** — ditunda ke Fase 1.11, lihat `docs/DECISIONS.md`. Chart.js/`vue-chartjs` juga belum ter-install.
- Tidak ada `CreateTransactionAction`, tidak ada checkout sungguhan, tidak ada form shift sungguhan.
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

Lanjutkan dari Fase 1.5 (Shift Kasir) di docs/ROADMAP.md.
Jalankan `php artisan test` setelah tiap perubahan, jangan nyatakan selesai
tanpa verifikasi nyata (migration benar-benar jalan, test benar-benar lulus).
```

## Catatan Lingkungan Lokal

- PHP 8.3.16, ekstensi `pgsql`/`pdo_pgsql` sudah aktif di `php.ini`
- PostgreSQL 18 terinstal, database `db_pos_diposkan` sudah ada
- `laravel/pail` SENGAJA tidak dipakai — butuh ekstensi `pcntl` yang tidak ada di PHP Windows
- `.env` sudah terisi lengkap (lihat `.env.example` untuk strukturnya, tanpa password asli)
