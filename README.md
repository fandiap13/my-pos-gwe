# POS App

Aplikasi Point of Sale (POS) — monolith Laravel + Inertia.js + Vue 3 (TypeScript).

Lihat `docs/PRD.md` untuk detail produk, `docs/DATABASE.md` untuk skema data, dan `AGENTS.md` untuk aturan kerja AI coding agent di repo ini.

## Requirement

Software yang harus sudah terinstal di komputer sebelum mulai:

| Software | Versi | Cek dengan |
|---|---|---|
| PHP | 8.3+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 20+ | `node -v` |
| npm | 10+ | `npm -v` |
| PostgreSQL | 17+ | `psql --version` |

Ekstensi PHP yang wajib aktif (cek di `php.ini`): `pdo_pgsql`, `pgsql`. Cek dengan:
```bash
php -m | grep -i pgsql
```
Kalau tidak muncul, buka `php.ini` (lokasinya: `php --ini`), hapus tanda `;` di depan baris `extension=pdo_pgsql` dan `extension=pgsql`, lalu restart terminal.

## Instalasi dari Awal

Langkah ini untuk setup project dari nol di komputer baru (clone repo kosong atau pertama kali setup).

### 1. Clone / masuk ke folder project
```bash
cd pos
```

### 2. Install dependency PHP
```bash
composer install
```

### 3. Install dependency JavaScript
```bash
npm install
```

### 4. Siapkan file environment
```bash
cp .env.example .env
```
> Kalau `.env.example` belum ada/belum sesuai, copy dari `.env` yang sudah berjalan di komputer lain (minus password asli), atau isi manual mengikuti bagian **Konfigurasi `.env`** di bawah.

### 5. Generate application key
```bash
php artisan key:generate
```

### 6. Buat database PostgreSQL
Buat database kosong dengan nama sesuai `DB_DATABASE` di `.env` (default: `db_pos_diposkan`). Lewat `psql`:
```bash
psql -U postgres -c "CREATE DATABASE db_pos_diposkan;"
```
Atau lewat pgAdmin / tool GUI lain — buat database baru dengan nama yang sama.

### 7. Konfigurasi `.env`
Buka `.env`, sesuaikan bagian database dengan kredensial PostgreSQL di komputermu:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_pos_diposkan
DB_USERNAME=postgres
DB_PASSWORD=isi_password_postgres_kamu
```
> **Jangan commit `.env`** — file ini berisi credential dan sudah masuk `.gitignore` secara default.

### 8. Jalankan migration + seeder
```bash
php artisan migrate:fresh --seed
```

### 9. Jalankan aplikasi
```bash
composer run dev
```
Perintah ini menjalankan `php artisan serve`, queue listener, dan Vite secara bersamaan. Buka `http://localhost:8000` di browser.

## Menjalankan (Setelah Setup Awal Selesai)

Untuk hari-hari biasa setelah instalasi awal sudah dilakukan sekali:
```bash
composer run dev
```

Kalau hanya butuh backend tanpa asset watcher:
```bash
php artisan serve
```

## Testing
```bash
php artisan test
```
atau
```bash
./vendor/bin/pest
```

## Format & Lint
```bash
./vendor/bin/pint       # PHP
npm run lint             # Vue/TypeScript (kalau ESLint sudah dikonfigurasi)
```

---

## Status Project Saat Ini

> Bagian ini diperbarui manual mengikuti progres nyata — bukan otomatis. Cek juga `docs/ROADMAP.md` untuk checklist detail per fase.

**Sudah selesai:**
- [x] Laravel 12 terinstal
- [x] Breeze (Inertia + Vue 3 + TypeScript + Pest) terinstal
- [x] `npm install` selesai
- [x] Ekstensi `pdo_pgsql`/`pgsql` aktif di PHP
- [x] `.env` dikonfigurasi untuk PostgreSQL (host, port, database, username, password)

**Langkah berikutnya yang perlu dijalankan manual (belum dilakukan AI secara otomatis — sesuai aturan di `AGENTS.md`, operasi database/credential tidak dieksekusi tanpa konfirmasi eksplisit):**
1. Pastikan database `db_pos_diposkan` sudah ada di PostgreSQL (lihat langkah 6 di atas).
2. Generate `APP_KEY` kalau belum: `php artisan key:generate`.
3. Jalankan migration: `php artisan migrate` (masih migration bawaan Laravel/Breeze — migration tabel POS seperti `products`, `transactions`, dll belum dibuat, menyusul di Fase 1.1 `docs/ROADMAP.md`).
4. Jalankan `composer run dev` dan cek halaman login Breeze tampil normal di `http://localhost:8000`.

Setelah 4 langkah itu berhasil tanpa error, **Fase 0 di `docs/ROADMAP.md` selesai** dan bisa lanjut ke Fase 1.1 (migration skema POS sesuai `docs/DATABASE.md`).

## Struktur Dokumen
- `AGENTS.md` — aturan untuk AI coding agent
- `docs/PRD.md` — tujuan produk, fitur, aturan bisnis
- `docs/DATABASE.md` — skema database
- `docs/UI.md` — alur layar & tampilan
- `docs/ROADMAP.md` — rencana & progres
- `docs/DECISIONS.md` — catatan keputusan teknis
- `docs/features/` — spesifikasi per fitur
