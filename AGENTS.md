# AGENTS.md

Instruksi untuk AI coding agent yang bekerja di repo ini. Baca file ini dulu sebelum mengerjakan apa pun.

## Stack
- Framework: Laravel 12
- Bahasa / versi: PHP 8.3.16
- Database: PostgreSQL
- Frontend approach: Inertia.js + Vue 3 (TypeScript)
- Styling: Tailwind CSS
- Testing: Pest

## Prinsip Arsitektur
- Monolith. Backend (Laravel) dan frontend (Vue via Inertia) hidup dalam satu repo, satu deploy. Jangan buat REST API terpisah untuk kebutuhan internal app — Inertia sudah menghubungkan controller ke halaman Vue langsung lewat props, tidak perlu fetch/axios ke endpoint sendiri.
- Buat endpoint JSON murni (`routes/api.php`) hanya kalau memang dibutuhkan konsumen eksternal (misal integrasi ke aplikasi lain, webhook, mobile app). Setiap endpoint di `routes/api.php` WAJIB didokumentasikan dengan anotasi Swagger (pakai `darkaonline/l5-swagger`) dan bisa diakses lewat Swagger UI di `/api/documentation`.
- Logic bisnis ditulis di `app/Actions/` (single-action classes, satu class = satu use case, misal `CreateTransactionAction`) atau `app/Services/` untuk logic yang dipakai berulang di banyak Action. Controller hanya memanggil Action/Service lalu return Inertia response — tidak ada query/logic bisnis langsung di controller.
- Validasi lewat `app/Http/Requests/*Request.php` (Form Request), bukan validasi manual di controller.
- Setiap operasi yang memengaruhi transaksi atau stok, serta setiap proses yang melakukan perubahan pada beberapa tabel sekaligus, WAJIB menggunakan `DB::transaction()`.
- Uang disimpan sebagai integer (rupiah, tanpa desimal) di database — jangan pakai float untuk menghindari rounding error. Format ke "Rp" hanya di layer tampilan (Vue/Accessor), bukan diubah di database.
- Primary key semua tabel pakai **UUID** (trait `HasUuids` di tiap model Eloquent), bukan auto-increment integer. Lihat `docs/DATABASE.md` & `docs/DECISIONS.md`.
- Timestamp selalu disimpan **UTC** di database (`config('app.timezone')` tetap `UTC`, jangan diubah). Zona waktu untuk tampilan diambil dari `store_settings.timezone` (data, bukan config statis) — konversi hanya di layer tampilan/Resource, tidak pernah di query atau saat insert. Lihat `docs/DATABASE.md` tabel `store_settings`.
- Perubahan stok hanya lewat tabel riwayat (misal `stock_movements`), jangan `UPDATE` langsung ke kolom stok di tabel produk. Kolom stok di tabel produk (jika ada) adalah hasil agregat/cache, bukan sumber kebenaran.
- **Data transaksi (`transactions`, `transaction_items`, `stock_movements`) TIDAK BOLEH dihapus — hard delete maupun soft delete.** Tidak ada migration `softDeletes()` di tabel ini, tidak ada method/endpoint `destroy()`/`delete()` untuknya. "Batalkan transaksi" berarti UPDATE kolom `status` jadi `voided` (lihat `docs/DATABASE.md` & `docs/PRD.md` §6), bukan menghapus row. Ini berlaku permanen untuk alasan audit trail — jangan diubah tanpa entri baru di `docs/DECISIONS.md` yang dikonfirmasi user.
- Route dipisah per role ke file sendiri: `routes/admin.php` (prefix `admin/`, middleware `role:admin`) dan `routes/kasir.php` (prefix `kasir/`, middleware `role:kasir`), didaftarkan lewat `bootstrap/app.php`. Route yang dipakai bersama kedua role (profil, logout) tetap di `routes/web.php`. Lihat `docs/DECISIONS.md` untuk alasan & contoh.
- Halaman Vue mengikuti pemisahan yang sama: `resources/js/Pages/Admin/` dan `resources/js/Pages/Kasir/`, satu file per halaman, mapping 1:1 ke `Inertia::render()` di controller masing-masing.

## Konvensi Kode
- Penamaan:
  - PHP: PascalCase untuk class, camelCase untuk method, snake_case untuk kolom database & key request.
  - Vue: PascalCase untuk nama file component (`ProductForm.vue`), kebab-case saat dipakai di template (`<product-form />` atau `<ProductForm />`, konsisten salah satu).
  - TypeScript: interface untuk data dari backend didefinisikan di `resources/js/types/` dan mencerminkan shape dari `Http/Resources` Laravel.
  - Route name: `{role}.{resource}.{action}` (misal `admin.products.index`, `kasir.transactions.store`). Route shared (di `routes/web.php`) tanpa prefix role, cukup `resource.action` (misal `profile.edit`).
- Struktur folder tambahan (di luar default framework):
  - `app/Actions/` — single-action classes per use case.
  - `app/Services/` — logic bisnis yang dipakai lintas Action.
  - `app/Http/Resources/` — transformasi data ke frontend, dipakai juga agar shape props Inertia konsisten.
  - `resources/js/Pages/Admin/` dan `resources/js/Pages/Kasir/` — halaman Inertia dipisah per role, mapping 1:1 ke `Inertia::render()` di controller masing-masing.
  - `resources/js/Components/` — komponen Vue yang dipakai berulang.
  - `resources/js/types/` — definisi TypeScript untuk model/data dari backend.
- Hal yang HARUS dihindari:
  - Jangan taruh query Eloquent langsung di file `.vue` lewat API call buatan sendiri — semua data masuk lewat Inertia props.
  - Jangan pakai `any` di TypeScript untuk data yang berasal dari backend; definisikan interface-nya di `resources/js/types/`.
  - Jangan hardcode harga/stok di seeder tanpa factory — pakai `database/factories` untuk data dummy yang konsisten.
  - Jangan campur migration skema dengan migration yang mengubah data (data migration) — pisahkan backfill data ke seeder/command sendiri.

## Aturan Penulisan Kode

### Umum

* Tulis kode yang sederhana, mudah dibaca, dan mudah dirawat. Jangan membuat abstraksi sebelum memang dibutuhkan.
* Ikuti struktur dan pola kode yang sudah ada sebelum memperkenalkan pola baru.
* Jangan melakukan refactor pada kode yang tidak berkaitan dengan tugas yang sedang dikerjakan.
* Jangan mengubah nama class, method, variable, route, database column, atau komponen yang sudah ada tanpa alasan yang jelas.
* Hindari duplikasi logic. Jika logic yang sama digunakan di beberapa tempat, pertimbangkan untuk memindahkannya ke `Action`, `Service`, helper, atau composable yang sesuai.
* Hindari fungsi/method yang terlalu panjang. Pecah menjadi method atau class yang memiliki satu tanggung jawab jika logic mulai sulit dibaca.
* Hindari nested conditional yang terlalu dalam. Gunakan early return jika dapat membuat alur kode lebih jelas.
* Jangan menambahkan dependency/library baru jika kebutuhan dapat diselesaikan menggunakan fitur Laravel, Vue, PHP, atau library yang sudah tersedia.
* Jangan membuat file, class, component, helper, atau abstraction hanya untuk logic yang hanya digunakan sekali jika tidak memberikan manfaat yang jelas.

### PHP / Laravel

* Gunakan fitur bawaan Laravel terlebih dahulu sebelum membuat implementasi sendiri.
* Gunakan dependency injection daripada membuat dependency secara manual di dalam class.
* Gunakan Eloquent relationship untuk relasi antar model daripada menulis query manual jika relationship sudah tersedia.
* Hindari `DB::raw()` dan raw SQL kecuali memang diperlukan dan tidak dapat digantikan dengan query builder/Eloquent.
* Gunakan Mass Assignment protection (`$fillable` atau `$guarded`) secara konsisten.
* Gunakan Enum PHP untuk nilai yang memiliki kumpulan nilai tetap jika memang sesuai dengan kebutuhan domain.
* Gunakan `Carbon`/`CarbonImmutable` untuk operasi tanggal dan waktu. Jangan melakukan manipulasi tanggal menggunakan string secara manual.
* Gunakan `config()` atau environment configuration untuk konfigurasi. Jangan hardcode credential, URL, key, atau konfigurasi environment di source code.
* Gunakan route name daripada hardcode URL ketika membuat link atau redirect.
* Gunakan `route()` untuk URL internal Laravel.
* Gunakan eager loading (`with()`) jika relasi diperlukan untuk mencegah N+1 query.
* Jangan menggunakan `Model::all()` untuk data yang berpotensi besar tanpa alasan yang jelas. Gunakan pagination, chunking, atau query yang lebih spesifik sesuai kebutuhan.
* Gunakan pagination untuk halaman list yang datanya dapat bertambah besar.
* Gunakan database constraint/index untuk aturan integritas data yang memang harus dijamin oleh database, bukan hanya mengandalkan validasi aplikasi.

### Action & Service

* Setiap `Action` harus memiliki satu tanggung jawab/use case yang jelas.
* Nama `Action` harus menggambarkan tindakan yang dilakukan, misalnya `CreateTransactionAction`, `VoidTransactionAction`, atau `AdjustStockAction`.
* `Service` digunakan untuk logic yang benar-benar dipakai oleh beberapa Action atau membutuhkan koordinasi logic yang kompleks.
* Jangan membuat Service hanya sebagai wrapper untuk satu pemanggilan method tanpa alasan yang jelas.
* Controller hanya bertugas menerima request, memanggil Action/Service, dan menentukan response/redirect.
* Jangan memindahkan seluruh logic aplikasi ke Service hanya untuk membuat controller terlihat pendek.

### Database & Transaction

* Setiap operasi yang memengaruhi transaksi atau stok, serta setiap proses yang melakukan perubahan pada beberapa tabel sekaligus, WAJIB menggunakan `DB::transaction()`.
* Operasi yang terdiri dari beberapa perubahan database harus bersifat atomic: seluruh perubahan berhasil atau seluruh perubahan dibatalkan.
* Jangan melakukan perubahan stok tanpa membuat `stock_movements`.
* Jangan mengandalkan nilai stok cache/agregat sebagai sumber kebenaran jika riwayat `stock_movements` tersedia.
* Jangan mengubah data transaksi yang sudah selesai dengan cara yang menghilangkan histori. Gunakan mekanisme status seperti `voided` sesuai aturan bisnis.
* Hindari query database di dalam loop jika dapat digantikan dengan eager loading, bulk query, atau pendekatan lain yang lebih efisien.
* Jangan melakukan operasi database yang tidak diperlukan dalam sebuah request.

### Form Request & Validation

* Semua validasi input HTTP menggunakan Form Request.
* Jangan melakukan validasi request secara manual di Controller jika dapat menggunakan Form Request.
* Gunakan rule validasi yang sesuai dengan tipe dan constraint database.
* Validasi bisnis yang kompleks dapat dilakukan di Action/Service setelah validasi request dasar selesai.
* Jangan hanya mengandalkan validasi frontend untuk aturan yang penting. Validasi backend tetap wajib dilakukan.

### Vue / TypeScript

* Gunakan `<script setup lang="ts">`.
* Gunakan TypeScript secara ketat. Jangan menggunakan `any` untuk menghindari masalah typing.
* Hindari `as any`, `@ts-ignore`, dan `@ts-expect-error` kecuali benar-benar diperlukan dan diberikan komentar mengenai alasannya.
* Props harus memiliki tipe yang jelas.
* Data dari backend harus menggunakan interface/type yang didefinisikan di `resources/js/types/`.
* Gunakan computed property untuk nilai turunan dari state, bukan menyimpan nilai turunan sebagai state terpisah jika tidak diperlukan.
* Gunakan composable hanya ketika logic memang digunakan kembali atau cukup kompleks untuk dipisahkan.
* Jangan membuat composable untuk logic sederhana yang hanya digunakan satu kali.
* Hindari state global untuk data yang hanya dibutuhkan oleh satu halaman/component.
* Jangan melakukan API call manual untuk mengambil data yang seharusnya dapat diberikan melalui Inertia props.
* Gunakan Inertia navigation/form utilities untuk interaksi dengan endpoint internal aplikasi.
* Pisahkan component berdasarkan tanggung jawab. Jangan membuat satu component yang menangani terlalu banyak domain sekaligus.
* Jangan memasukkan business logic backend ke Vue. Vue bertanggung jawab atas presentation dan interaksi UI; aturan bisnis tetap berada di backend.

### Naming & Readability

* Gunakan nama yang menjelaskan maksud kode, bukan nama generik seperti `data`, `item`, `value`, atau `result` jika konteksnya dapat dibuat lebih spesifik.
* Gunakan nama boolean yang menunjukkan kondisi, misalnya `isActive`, `hasStock`, `canVoid`, atau `isPaid`.
* Hindari singkatan yang tidak umum.
* Gunakan komentar hanya untuk menjelaskan **mengapa** sesuatu dilakukan, bukan mengulang apa yang sudah jelas dari kode.
* Jangan meninggalkan komentar yang sudah tidak sesuai dengan implementasi.
* Jangan meninggalkan kode yang di-comment-out setelah perubahan selesai.
* Prioritaskan keterbacaan kode daripada membuat kode sesingkat mungkin.

### Error Handling

* Jangan menelan exception secara diam-diam.
* Jangan menggunakan `catch` hanya untuk mengabaikan error.
* Error yang memang dapat ditangani harus diberikan response yang sesuai kepada pengguna.
* Detail exception internal seperti stack trace, SQL query, credential, atau informasi sensitif tidak boleh ditampilkan kepada pengguna.
* Gunakan logging untuk error yang membutuhkan investigasi lebih lanjut.
* Gunakan HTTP status code yang sesuai untuk response HTTP.

### Security

* Jangan pernah hardcode password, token, API key, atau credential.
* Jangan mempercayai data dari frontend untuk authorization atau aturan bisnis.
* Authorization harus selalu diverifikasi di backend.
* Gunakan Laravel validation dan authorization mechanism yang sesuai.
* Jangan expose data sensitif melalui Inertia props atau response JSON jika tidak diperlukan.
* Jangan menggunakan `v-html` kecuali sumber HTML benar-benar terpercaya dan sanitasi sudah dipastikan.
* Jangan menonaktifkan CSRF protection untuk mengatasi masalah request.

### Performance

* Hindari N+1 query.
* Gunakan pagination untuk data list yang dapat berkembang besar.
* Hindari query berulang dalam loop.
* Pilih hanya kolom yang dibutuhkan jika query mengambil data dalam jumlah besar.
* Jangan melakukan optimasi prematur. Optimasi dilakukan berdasarkan kebutuhan atau bottleneck yang jelas.

## Workflow Vibecode
- Urutan kerja AI saat mengerjakan tugas:
  1. Baca `docs/ROADMAP.md`, pilih/ambil item yang diminta user.
  2. Cek `docs/PRD.md`, `docs/DATABASE.md`, `docs/UI.md`, dan `docs/features/` relevan untuk konteks sebelum menulis kode.
  3. Kerjakan berurutan dari data ke tampilan: migration → model → Action/Service → Form Request → controller → halaman Vue.
  4. Jalankan `php artisan test` (atau test spesifik yang relevan) sebelum menyatakan tugas selesai.
  5. Jalankan `vendor/bin/pint` sebelum commit.
- Kapan harus update `docs/ROADMAP.md`:
  - Centang item setelah kode untuk item tersebut selesai DAN test terkait lulus.
  - Kalau menemukan scope baru yang belum tercatat, tambahkan ke bagian Backlog — jangan dikerjakan diam-diam di luar scope yang diminta.
- Kapan harus berhenti dan bertanya ke user:
  - Keputusan yang mengubah skema database secara besar (menambah tabel baru, mengubah relasi inti).
  - Aturan bisnis yang belum jelas di `docs/PRD.md` (misal: cara hitung diskon, siapa yang boleh void transaksi).
  - Kalau ada keputusan teknis baru yang diambil saat mengerjakan tugas, catat di `docs/DECISIONS.md` alih-alih diam-diam menerapkannya.

## Aturan Akses File

* Jangan membaca, membuka, menganalisis, atau memodifikasi file yang berisi credential, secret, token, atau konfigurasi environment sensitif.
* File `.env`, `.env.*`, dan file credential lainnya dianggap **PRIVATE** dan tidak boleh dibaca atau ditampilkan isinya.
* Jangan membaca atau memodifikasi file di luar scope project yang sedang dikerjakan, kecuali saya menyetujuinya.
* Jangan mengakses folder atau file sistem, credential manager, SSH key, konfigurasi Git global, atau credential aplikasi lain di luar repository.
* Jangan mencari atau mencoba mengambil password, API key, access token, private key, database credential, atau secret lainnya.
* Jangan menampilkan nilai secret atau credential ke output, log, commit, atau dokumentasi.
* Jika informasi dari file yang dilindungi diperlukan untuk menyelesaikan tugas, **berhenti dan minta user memberikan informasi yang diperlukan secara eksplisit**. Jangan mencoba membaca file tersebut secara langsung.
* Jangan mengubah file konfigurasi, dependency, atau environment hanya untuk mengatasi masalah tanpa menjelaskan perubahan tersebut dan memastikan perubahan masih berada dalam scope tugas.


## Perintah Penting
- Jalankan dev server: `composer run dev` (menjalankan `php artisan serve`, queue listener, dan Vite secara bersamaan)
- Jalankan test: `php artisan test` (atau `./vendor/bin/pest`)
- Format/lint: `./vendor/bin/pint` (PHP), `npm run lint` + `npm run format` (Vue/TS)
- Migration/seed: `php artisan migrate`, `php artisan migrate:fresh --seed`
- Generate dokumentasi Swagger: `php artisan l5-swagger:generate`

## Referensi
- Lihat docs/PRD.md untuk fitur & aturan bisnis
- Lihat docs/DATABASE.md untuk skema data
- Lihat docs/UI.md untuk alur layar
- Lihat docs/ROADMAP.md untuk progres
- Lihat docs/DECISIONS.md untuk keputusan yang sudah final
