# Progress Snapshot

> File ini untuk ONBOARDING AI/developer baru yang melanjutkan project ini.
> Update manual tiap kali selesai satu fase/sub-fase besar — jangan andalkan
> AI mengingat ini otomatis, karena setiap sesi AI dimulai tanpa memori sesi sebelumnya.

## Status Saat Ini

**Fase aktif:** Fase 1 — Core POS, baru selesai sub-fase **1.2 (Design System & Komponen Dasar)**.
**Belum dikerjakan:** 1.3 (Autentikasi & Role) dan seterusnya — lihat `docs/ROADMAP.md`.

**Commit terakhir:** lihat `git log --oneline -1` — commit terakhir berjudul "feat: Fase 1.2 — design system, komponen dasar, ESLint/Prettier".

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

## Yang BELUM Ada (Jangan Diasumsikan Sudah Jadi)

- **Belum ada halaman fitur POS apa pun** (CRUD produk, checkout, shift, dll) — `resources/js/Pages/Admin/` dan `Pages/Kasir/` (folder sesuai `AGENTS.md`) masih KOSONG, belum dibuat. Yang sudah ada baru komponen & layout generik, belum halaman yang memakainya untuk fitur nyata.
- **Tidak ada middleware role** (`role:admin`, `role:kasir`) — kolom `role` di `users` sudah ada, tapi belum ada yang memvalidasinya di route.
- **Tidak ada `routes/admin.php` / `routes/kasir.php`** — route yang ada sekarang masih `routes/web.php` (login, profile, dashboard placeholder) + `routes/api.php` (health check).
- **Login belum redirect sesuai role** — saat ini login sukses selalu ke `/dashboard` generik (placeholder, lihat `Dashboard.vue`), bukan ke `/admin` atau `/kasir` sesuai `docs/features/auth-login.md`. Dashboard akan diganti total di Fase 1.11 (StatCard, grafik, dll — bukan placeholder ini).
- Komponen chart (`StatCard`, `LineChart`, `BarChart`, dll) di `docs/UI.md` **sengaja belum dibuat** — ditunda ke Fase 1.11, lihat `docs/DECISIONS.md`. Chart.js/`vue-chartjs` juga belum ter-install.
- Tidak ada `CreateTransactionAction`, tidak ada checkout, tidak ada shift, tidak ada CRUD produk/kategori dari sisi UI.

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

Lanjutkan dari Fase 1.3 (Autentikasi & Role) di docs/ROADMAP.md.
Jalankan `php artisan test` setelah tiap perubahan, jangan nyatakan selesai
tanpa verifikasi nyata (migration benar-benar jalan, test benar-benar lulus).
```

## Catatan Lingkungan Lokal

- PHP 8.3.16, ekstensi `pgsql`/`pdo_pgsql` sudah aktif di `php.ini`
- PostgreSQL 18 terinstal, database `db_pos_diposkan` sudah ada
- `laravel/pail` SENGAJA tidak dipakai — butuh ekstensi `pcntl` yang tidak ada di PHP Windows
- `.env` sudah terisi lengkap (lihat `.env.example` untuk strukturnya, tanpa password asli)
