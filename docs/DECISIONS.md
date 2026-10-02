# Decision Log

Catatan keputusan teknis/produk yang sudah final, supaya AI tidak mengubah atau mempertanyakan lagi. Tambahkan entri baru di atas (terbaru dulu).

## Template
### [YYYY-MM-DD] Judul keputusan
- **Keputusan:**
- **Alasan:**
- **Alternatif yang ditolak:**

---

### [2026-10-02] Pesan validasi Form Request dalam Bahasa Indonesia (tanpa `lang/`)
- **Keputusan:** Setiap Form Request menulis pesan validasinya sendiri dalam **Bahasa Indonesia** lewat method `messages()` — satu kunci per rule, mis. `'price.min' => 'Harga jual tidak boleh negatif.'`. Pesan login di `LoginRequest` juga string literal Indonesia (`'Kredensial tidak cocok dengan data kami.'`, pesan rate-limit disusun manual dengan `:seconds` yang sudah diganti). **Tidak** memakai file bahasa `lang/id/` dan **tidak** mengubah `APP_LOCALE` — locale tetap `en` (default Laravel).
- **Alasan:** Permintaan user — client orang Indonesia, cukup tulis pesannya langsung di request; setup `lang/id/` + mengganti locale dinilai berlebihan. Catatan: pendekatan `lang/id/*` + `APP_LOCALE=id` sempat diimplementasikan lalu dibuang seluruhnya atas instruksi user.
- **Alternatif yang ditolak:** File bahasa `lang/id/validation.php` + `APP_LOCALE=id` (sudah dibuat lalu dihapus — user tidak mau); menulis `messages()` sebagian sehingga rule yang terlewat jatuh ke pesan default Laravel berbahasa Inggris.
- **Dampak:** Rule baru di Form Request **wajib** disertai kunci `messages()` yang setara — kalau lupa, pesan tampil bahasa Inggris dari default framework. Validasi inline `$request->validate()` / `Auth::validate()` di controller Breeze (lupa sandi, ganti sandi, confirm password) di luar scope request dan **masih berbahasa Inggris**. Diunci oleh `tests/Feature/ValidationMessageTest.php`.

### [2026-10-02] Kontrol baris/halaman di Pagination, `per_page` dibatasi 1..100
- **Keputusan:** Semua halaman daftar yang punya pagination menampilkan kontrol **Baris/halaman** (opsi 10/15/25/50/100, default 15) di dalam komponen `Pagination`, di samping teks "Menampilkan X–Y dari Z data". Nilai query `per_page` dibatasi 1..100 lewat method base `Controller::perPage()` — **bukan** Form Request.
- **Alasan:** Permintaan user supaya client tidak perlu mengetab banyak saat data banyak, dengan batas atas 100 baris supaya query tetap ringan. `per_page` bukan input form: nilainya dibatasi/dibulatkan (bukan ditolak), sehingga URL yang tidak valid tetap menampilkan data wajar alih-alih error validasi, dan tidak perlu satu Form Request per endpoint index.
- **Alternatif yang ditolak:** Form Request `max:100` per endpoint (UX jelek untuk query param & menambah class hanya untuk satu parameter); tanpa batas atas (query bisa minta jutaan baris).
- **Dampak:** Filter aktif (search/kategori/status) harus tetap ikut terkirim saat user ganti baris/halaman — `Pagination` membangun URL dari query string saat ini lalu mereset `page` ke 1, dan watcher filter di tiap halaman list ikut mengirim `per_page` aktif.

---

### [2026-10-02] Tabel daftar: kolom `No.` pertama, nilai kosong pakai `-`
- **Keputusan:** Semua tabel daftar (data list) punya kolom pertama **No.** dengan nomor urut lanjutan antar halaman (`(current_page - 1) * per_page + index + 1`, bukan mulai dari 1 lagi di tiap halaman). Sel nilai kosong menampilkan `-` (hyphen), **bukan** `—` (em dash).
- **Alasan:** Permintaan user — nomor urut memudahkan menyebut baris saat diskusi/print, dan em dash memberi kesan konten hasil generate otomatis ("AI slop") di data toko yang dipakai harian.
- **Alternatif yang ditolak:** Nomor urut per halaman selalu mulai dari 1 (membingungkan saat baris sedang dibahas lintas halaman); menampilkan `—` (dicap user sebagai kesan auto-generated).
- **Dampak:** Kolom `No.` ikut ditambahkan ke tabel Shift (admin & kasir) yang sudah ada, supaya konsisten. Berlaku juga untuk tabel baru berikutnya (Stok, Transaksi, Laporan).

---

### [2026-10-02] Fase 1.5: props single Resource wajib `->resolve()` (bug halaman Edit)
- **Keputusan:** Saat mengirim **satu** model Resource ke Inertia, controller WAJIB menulis `(new XResource($model))->resolve()` (bukan `new XResource($model)` mentah). Berlaku juga untuk prop hasil paginasi via `->through()` seperti keputusan sebelumnya.
- **Alasan:** Inertia me-resolve props lewat jalur `Responsable` (`ResourceResponse::toResponse()`), dan `JsonResource::$wrap` default-nya `'data'` — sehingga `new ProductResource($product)` tiba di frontend sebagai `{data: {...}}`, bukan object flat. Ini bikin halaman **Edit Produk & Edit Kategori membaca `props.product.name` = `undefined`** (form kosong, `route(..., props.product.id)` gagal) — ketahuan saat membangun Fase 1.5, sebelumnya tidak pernah ada test yang memeriksa shape props halaman Edit. `->resolve()` mengembalikan array flat hasil `toArray()` tanpa bungkusan.
- **Alternatif yang ditolak:** `JsonResource::withoutWrapping()` global di `AppServiceProvider` — meniadakan bungkusan untuk SEMUA resource, tapi sekaligus membongkar props koleksi yang sudah dikonsumsi frontend dalam bentuk terbungkus (`categories: { data: Category[] }` di halaman Produk/Kategori) sehingga menyentuh halaman yang sedang berjalan; pendekatan eksplisit `->resolve()` konsisten dengan keputusan paginator sebelumnya.
- **Dampak:** Diperbaiki di `ProductController@edit`, `CategoryController@edit`, `Kasir\ShiftController@tutup`. Tambah test `assertInertia()` shape untuk halaman detail/edit baru (lihat `product edit page receives flat product props` di `tests/Feature/Admin/ProductTest.php`).

---

### [2026-10-02] Fase 1.5: `shiftIsActive` & `flash` di-share HandleInertiaRequests
- **Keputusan:**
  1. `HandleInertiaRequests::share()` mengirim `shiftIsActive` (boolean untuk kasir, `null` untuk admin) — dihitung backend, bukan dikirim per halaman.
  2. `HandleInertiaRequests::share()` mengirim `flash: {success, error}` dari session, dan `Components/Toast.vue` me-watch-nya (`immediate`) untuk menampilkan toast.
- **Alasan:** Badge "Shift Aktif/Tidak Aktif" + link Tutup Shift di `KasirLayout` harus benar di semua halaman kasir (Buka/Tutup/Riwayat/Transaksi) tanpa tiap halaman mengirim prop sendiri (placeholder dulu memalsukan nilainya). Controller sudah mengatur `->with('success', ...)` sejak Fase 1.4 tapi tidak pernah tampil karena flash tidak dishare ke Inertia — toast otomatis menghubungkan keduanya untuk semua halaman.
- **Alternatif yang ditolak:** Tiap halaman mengirim `shiftIsActive` sendiri (rawan tidak konsisten, logika duplikat); bridge flash→toast per layout (duplikat di 3 layout — cukup sekali di `Toast.vue` yang memang dirender tiap layout).
- **Dampak:** `KasirLayout` tidak lagi menerima prop `shiftIsActive` (baca dari `page.props`); tipe `PageProps` di `resources/js/types/index.d.ts` bertambah `shiftIsActive` & `flash`.

---

### [2026-10-02] Fase 1.5: aturan hitung kas shift (expected_cash, satu shift aktif, riwayat)
- **Keputusan:**
  1. `expected_cash` = `opening_cash` + total transaksi **`payment_method = cash` DAN `status = completed`** selama shift berjalan. Dihitung backend saat tutup shift (`CloseShiftAction`) DAN saat render halaman Tutup Shift sebagai preview (`CalculateExpectedCashAction`) — nilai preview dari frontend tidak pernah dipercaya.
  2. Constraint satu shift aktif per kasir divalidasi di `OpenShiftAction` **di dalam `DB::transaction()` + `lockForUpdate()` pada baris `users`** — supaya dua request buka shift bersamaan tidak lolos cek check-then-insert.
  3. Riwayat shift kasir (`kasir.shift.riwayat`) TIDAK dipasangi middleware `shift.active` — kasir harus tetap bisa melihat riwayatnya setelah shift ditutup; halaman Tutup Shift tetap dijaga `shift.active`.
- **Alasan:** Transaksi voided = uang sudah kembali ke pelanggan, jadi tidak boleh dihitung sebagai kas. Lock diperlukan karena DB constraint biasa tidak bisa menangani kondisi "hanya saat status open". Riwayat adalah data read-only milik sendiri, mengharuskan shift aktif justru memblokir kasir yang baru saja menutup shift.
- **Alternatif yang ditolak:** Hitung `expected_cash` hanya di frontend (bisa dimanipulasi & race dengan transaksi baru); partial unique index di DB (tidak bisa mengecualikan status non-open tanpa partial index yang rumit — cukup validasi aplikasi sesuai catatan `docs/DATABASE.md`); menaruh riwayat di balik `shift.active` (menghalangi akses riwayat setelah tutup shift).
- **Dampak:** Selisih kas (`closing_cash - expected_cash`) tetap dihitung di accessor model `Shift::cashDifference()`, tidak ada kolom baru. Transaksi non-cash (transfer/debit) tidak memengaruhi rekap kas. Lihat `app/Actions/Shift/`.

---

### [2026-10-02] Bug fix: halaman Produk blank karena shape paginator ganda-bungkus
- **Keputusan:** Untuk halaman list yang dipaginasi, controller WAJIB kirim paginator flat ke Inertia (`$query->paginate()->through(fn ($item) => (new XResource($item))->resolve())`), BUKAN `XResource::collection($paginator)`. Frontend (`Paginated<T>` di `types/pagination.ts`, dipakai `Pagination.vue`) mengasumsikan `current_page`, `data`, `last_page`, `links`, dll ada di level yang sama — bukan nested di `meta`.
- **Alasan:** `ProductController@index` awalnya pakai `ProductResource::collection($products)` pada hasil `paginate()`. Laravel Resource Collection memisahkan pagination meta ke key `meta`/`links` terpisah dari `data`, BUKAN shape flat seperti `LengthAwarePaginator::toArray()` biasa. Akibatnya props `products` di Inertia punya shape `{ data: [...], links: {...}, meta: {...} }`, tapi `Index.vue` ditulis mengasumsikan `{ data: { data: [...], links: [...], current_page, ... } }` (mengira seluruh resource collection ada di `products.data`). Mismatch ini membuat `products.data.data` jadi `undefined`, `.length` throw TypeError di tengah render, dan Vue gagal mount — hasilnya halaman benar-benar blank tanpa error di server log (request tetap 200, errornya murni di JS browser).
- **Kenapa tidak ketangkap test sebelumnya:** Test awal cuma `assertOk()` (cek status HTTP), tidak pernah memverifikasi struktur data Inertia props. Diperbaiki dengan menambah test `assertInertia()` yang cek shape eksplisit (`has('products.data', N)`, `missing('products.data.data')`) — lihat `tests/Feature/Admin/ProductTest.php`.
- **Alternatif yang ditolak:** Ubah `Paginated<T>` & `Pagination.vue` supaya cocok dengan shape `ResourceCollection` (`meta`/`links` terpisah) — ditolak karena `Pagination.vue` sudah dipakai dengan asumsi shape flat, mengubahnya butuh audit ulang semua pemakaian; `through()` lebih simpel dan tetap dapat transformasi field via Resource.
- **Dampak — WAJIB diikuti untuk semua halaman list baru (Kategori di Fase 1.4 TIDAK terpaginasi jadi tidak kena bug ini, tapi Fase 1.9 Stok, Fase 1.8 Riwayat Transaksi, dll SEMUA perlu pola ini):**
  ```php
  $items = Model::query()->paginate(15)->withQueryString();
  return Inertia::render('Admin/Xxx/Index', [
      'items' => $items->through(fn ($item) => (new XResource($item))->resolve()),
  ]);
  ```
  Di frontend: `defineProps<{ items: Paginated<X> }>()`, akses `items.data` (BUKAN `items.data.data`), `<Pagination :paginated="items" />` (BUKAN `items.data`).

---

### [2026-10-02] Bug fix: Input.vue type="number" diam-diam emit string
- **Keputusan:** `Input.vue` sekarang convert nilai ke `Number()` sebelum emit saat `type="number"`; tipe `update:modelValue` diubah jadi `string | number`.
- **Alasan:** Ditemukan saat membangun form Produk (stok awal, stok minimum) — `v-model` ke `ref<number>` diam-diam menerima string runtime (`"5"` bukan `5`) karena `handleInput` lama selalu emit `event.target.value` (selalu string), meski TypeScript tidak menangkap ini (prop `modelValue?: string | number` terlalu longgar). Komponen lain yang hanya pakai `type="search"`/`text` (`ProductSearch.vue`) tidak terpengaruh — perbaikan hanya menyesuaikan cast di situ.
- **Alternatif yang ditolak:** Biarkan backend yang menangani coercion string→int (Laravel validation `integer` cukup toleran ke numeric string) — ditolak karena membiarkan state frontend tidak konsisten dengan tipenya sendiri, berisiko bug di tempat lain yang mengasumsikan `number` murni (kalkulasi, format).
- **Dampak:** Semua pemakaian `<Input type="number">` sekarang `v-model` ke `number` secara aman. `ProductSearch.vue` ditambah cast eksplisit `String(value)` karena konsumsinya murni `type="search"`.

---

### [2026-10-02] Bug fix: Select.vue placeholder tidak bisa dipilih balik
- **Keputusan:** `Select.vue` prop `modelValue` diubah jadi `string | null`, placeholder option tidak lagi `disabled` (bisa dipilih balik untuk reset ke `null`).
- **Alasan:** Ditemukan saat membangun field "Parent (opsional)" di form Kategori/Produk — placeholder lama (`<option value="" disabled>`) membuat user tidak bisa mengosongkan pilihan setelah memilih sesuatu (misal: salah pilih kategori, tidak bisa kembali ke "Tanpa kategori" tanpa reload). Opsi kosong (`value=""`) di-translate jadi `null` saat emit, konsisten dengan kolom nullable di database (`category_id`, `parent_id`).
- **Alternatif yang ditolak:** Tambah tombol "X" terpisah untuk clear selection — ditolak, kurang idiomatis untuk native `<select>` dan menambah kompleksitas UI yang tidak perlu.
- **Dampak:** Semua pemakaian `Select` dengan field nullable (`category_id`, `parent_id`) sekarang `v-model` ke `ref<string | null>`, bukan `ref<string>` dengan workaround string kosong.

---

### [2026-10-02] Stok awal produk via stock_movements, bukan kolom stock langsung
- **Keputusan:** Form "Tambah Produk" punya field `initial_stock` terpisah dari kolom `stock`. `CreateProductAction` insert `stock_movements` type `in` (kalau `initial_stock > 0`) di dalam `DB::transaction()` yang sama dengan `Product::create()`. Form "Edit Produk" TIDAK punya field stok sama sekali — perubahan stok produk yang sudah ada hanya lewat Stok > Penyesuaian Manual (Fase 1.9).
- **Alasan:** Sesuai `AGENTS.md` & `docs/DATABASE.md`: `stock_movements` adalah sumber kebenaran tunggal, `products.stock` murni cache. Kalau form Produk mengisi `stock` langsung, akan ada dua jalur penulisan stok (form produk vs `stock_movements`) yang bisa saling tidak sinkron.
- **Alternatif yang ditolak:** Isi `stock` langsung saat create (lebih simpel tapi melanggar aturan arsitektur yang sudah final); izinkan edit stok dari form Produk (ditolak — stok yang sudah berjalan harus melalui jalur `adjustment` yang tercatat alasannya, bukan overwrite diam-diam).
- **Dampak:** `StockMovementObserver` (Fase 1.1) otomatis menghitung ulang `products.stock` setelah insert movement — tidak ada kode tambahan untuk update cache secara manual. Lihat `app/Actions/Product/CreateProductAction.php`.

---

### [2026-10-02] Kategori: blokir hapus kalau masih ada produk/subkategori
- **Keputusan:** `DeleteCategoryAction` menolak soft-delete kategori (lempar `ValidationException`) kalau `category->products()->exists()` atau `category->children()->exists()`.
- **Alasan:** FK `nullOnDelete` di migration (Fase 1.1) hanya berlaku untuk hard delete, TIDAK terpicu untuk soft delete — kalau tidak divalidasi di Action, kategori yang masih dipakai produk bisa "hilang" dari tampilan (soft-deleted) padahal produk & subkategorinya masih menunjuk ke kategori itu, membingungkan admin.
- **Alternatif yang ditolak:** Cascade — hapus/pindahkan produk & subkategori otomatis saat kategori dihapus (terlalu destruktif untuk aksi yang seharusnya eksplisit); biarkan tanpa validasi (ditolak, sesuai keputusan user eksplisit).
- **Dampak:** Pesan error ditampilkan lewat `session('errors')` key `category` (bukan field form biasa), ditangkap di `Admin/Categories/Index.vue` lewat flash message. Lihat `app/Actions/Category/DeleteCategoryAction.php`.

---

### [2026-10-02] Tambah kategori dari form Produk: halaman terpisah, bukan modal inline
- **Keputusan:** Item terbuka di `docs/UI.md` ("modal inline vs halaman terpisah") diputuskan: halaman terpisah. Kalau kategori yang diinginkan belum ada, admin buka `/admin/categories/create` di tab/halaman lain, lalu kembali ke form Produk (kategori baru otomatis muncul di dropdown setelah reload/kembali).
- **Alasan:** Lebih sederhana diimplementasi sekarang. Modal inline (dengan refresh list kategori di form produk tanpa reload) bisa ditambah nanti sebagai polish kalau terasa perlu, tidak menghalangi fungsi inti CRUD Produk/Kategori selesai dulu.
- **Alternatif yang ditolak:** Modal inline sekarang — ditolak untuk sekarang, kompleksitas (state sinkronisasi antar form) tidak sepadan di tahap ini.
- **Dampak:** Tidak ada kode modal/inline-create di form Produk. `docs/UI.md` bagian "Hal yang Masih Perlu Diputuskan" dikosongkan dari item ini.

---

### [2026-10-02] Redirect login terpusat di DetermineLoginRedirectAction
- **Keputusan:** Logic "ke mana user diarahkan setelah login/verifikasi" (admin → dashboard, kasir dengan shift → transaksi, kasir tanpa shift → buka shift) dipusatkan di satu class `app/Actions/Auth/DetermineLoginRedirectAction.php`, dipanggil dari `AuthenticatedSessionController` DAN 4 controller Breeze bawaan lain yang semula hardcode `route('dashboard')` (`ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `VerifyEmailController`).
- **Alasan:** `docs/features/auth-login.md` "Catatan Teknis" menyarankan logic ini di satu tempat. `redirect()->intended()` bawaan Breeze tidak dipakai lagi — tujuan redirect SELALU ditentukan oleh role + status shift, bukan URL yang sempat dicoba diakses sebelum login, karena untuk POS, kasir/admin yang login seharusnya selalu masuk ke area kerjanya, bukan ke halaman acak yang kebetulan coba diakses saat belum login.
- **Alternatif yang ditolak:** Custom `LoginResponse` via Laravel Fortify contract — tidak dipakai karena Fortify tidak terinstal (Breeze pakai pendekatan controller langsung, bukan Fortify actions).
- **Dampak:** Route `/dashboard` lama (generik, Fase 1.2) dihapus total, diganti `admin.dashboard` (routes/admin.php) dan `kasir.transaksi`/`kasir.shift.buka` (routes/kasir.php). `Pages/Dashboard.vue` lama dihapus, diganti `Pages/Admin/Dashboard.vue`, `Pages/Kasir/Transaksi.vue`, `Pages/Kasir/Shift/Buka.vue` (semua masih placeholder, lihat `docs/ROADMAP.md` Fase 1.5/1.6/1.11).

---

### [2026-10-02] Middleware role & shift: alias 'role' dan 'shift.active'
- **Keputusan:** Dua middleware baru: `EnsureUserHasRole` (alias `role:admin`/`role:kasir`, dipasang di `routes/admin.php`/`routes/kasir.php`) dan `EnsureShiftActive` (alias `shift.active`, HANYA dipasang di route `kasir.transaksi`, BUKAN di `kasir.shift.buka` — kalau dipasang di situ juga, terjadi redirect loop karena kasir tanpa shift tidak akan pernah bisa membuka shift-nya sendiri).
- **Alasan:** Sesuai `AGENTS.md` (pemisahan route per role) dan `docs/features/auth-login.md` (shift dicek ulang di tiap request, bukan cuma saat login, karena kasir bisa logout di tengah shift lalu login lagi).
- **Alternatif yang ditolak:** Cek role/shift manual di tiap controller — ditolak, middleware lebih DRY dan konsisten dengan prinsip "authorization selalu diverifikasi di backend" di `AGENTS.md`.
- **Dampak:** `bootstrap/app.php` mendaftarkan alias `role` dan `shift.active`, juga registrasi `routes/admin.php`/`routes/kasir.php` lewat `withRouting(then: ...)`. `is_active` user dicek di `LoginRequest::authenticate()` SETELAH `Auth::attempt()` berhasil (bukan sebelum), supaya pesan "akun tidak aktif" hanya muncul untuk kredensial yang benar.

---

### [2026-10-02] ESLint + Prettier di-setup mengikuti stub Breeze Vue+TS
- **Keputusan:** ESLint & Prettier di-setup manual (bukan re-run `breeze:install`) mengikuti versi package & config persis yang dipakai Breeze untuk stack Inertia+Vue+TypeScript (`eslint@^8.57.0`, `eslint-plugin-vue@^9.23.0`, `@vue/eslint-config-typescript@^13.0.0`, dll — lihat `vendor/laravel/breeze/src/Console/InstallsInertiaStacks.php`). Config disalin dari `vendor/laravel/breeze/stubs/inertia-vue-ts/.eslintrc.cjs` dan `stubs/inertia-common/.prettierrc`.
- **Alasan:** `AGENTS.md` sudah menjanjikan `npm run lint`/`npm run format` sejak awal, tapi baru ketahuan belum pernah benar-benar di-setup saat Fase 1.2 berjalan (Breeze diinstal tanpa flag `--eslint` di Fase 0). Mengikuti versi/config resmi Breeze lebih aman daripada menebak kombinasi versi sendiri, dan tetap konsisten dengan ekosistem Inertia+Vue+TS yang dipakai.
- **Alternatif yang ditolak:** Re-run `php artisan breeze:install vue --typescript --eslint` — ditolak karena berisiko menimpa file yang sudah banyak dikustomisasi di Fase 1.2 (komponen, layout, halaman Auth/Profile yang sudah ditulis ulang).
- **Dampak:** `.eslintrc.cjs`, `.prettierrc` baru di root. `package.json` dapat script `lint` & `format`. Satu bug nyata ketemu & diperbaiki saat setup ini: `v-html` dipakai langsung di komponen `<Link>` Inertia (`Pagination.vue`) — dipindah ke `<span>` di dalamnya sesuai aturan `vue/no-v-text-v-html-on-component`.

---

### [2026-10-02] Komponen chart (StatCard, LineChart, dll) ditunda ke Fase 1.11
- **Keputusan:** Komponen khusus Dashboard & Laporan (`StatCard`, `DeltaBadge`, `LineChart`, `BarChart`, `SubMetricBar`, `DataTable` ringkas) yang terdaftar di `docs/UI.md` TIDAK dibuat di Fase 1.2 (Design System), meski urutan aslinya di dokumen itu ada di sana. Dibuat nanti saat Fase 1.11 (Laporan Dasar) benar-benar dikerjakan.
- **Alasan:** Fase 1.2 fokus ke komponen yang dipakai lebih dulu di Fase 1.3–1.10 (Button, Input, Table, CartItem, dll). Dashboard/Laporan baru di Fase 1.11 — kalau chart dibuat sekarang, komponennya akan "nganggur" lama sebelum ada halaman yang memakainya, dan desainnya bisa jadi perlu revisi setelah melihat data/kasus nyata dari fase-fase yang berjalan duluan.
- **Alternatif yang ditolak:** Buat semua komponen sekaligus di Fase 1.2 sesuai urutan asli `docs/UI.md` — ditolak karena menunda fase inti POS (checkout dll) demi komponen yang belum dibutuhkan.
- **Dampak:** `docs/ROADMAP.md` Fase 1.11 perlu ditambah checklist pembuatan komponen chart ini saat waktunya tiba — belum ditambahkan sekarang, tambahkan saat mulai Fase 1.11.

---

### [2026-10-02] Chart library: Chart.js via vue-chartjs
- **Keputusan:** Dashboard & Laporan (Fase 1.11) memakai Chart.js lewat wrapper `vue-chartjs`.
- **Alasan:** Ringan, battle-tested, styling (warna/gradasi/tooltip) mudah dikontrol sesuai Design Tokens di `docs/UI.md`. Cukup untuk kebutuhan line/bar chart sederhana POS ini, tidak perlu library yang lebih berat.
- **Alternatif yang ditolak:** ApexCharts via `vue3-apexcharts` — visual lebih "polished" out-of-the-box dan lebih dekat gaya referensi Shopeers, tapi bundle size lebih besar; tidak sepadan untuk kebutuhan chart yang relatif sederhana di sini.
- **Dampak:** `npm install chart.js vue-chartjs` dijalankan saat mulai Fase 1.11, bukan sekarang (lihat keputusan di atas).

---

### [2026-10-02] Font Inter via Google Fonts, icon via @lucide/vue
- **Keputusan:** Font `Inter` (sesuai `docs/UI.md` Design Tokens) dimuat lewat `<link>` Google Fonts di root layout Blade (`resources/views/app.blade.php`), bukan self-host via `@fontsource/inter`. Icon set memakai `@lucide/vue`.
- **Alasan:** Google Fonts `<link>` paling simpel, tidak nambah dependency npm/bundle size, auto-update ke versi font terbaru. Lucide dipilih karena gaya ikon stroke-based minimalis cocok dengan referensi visual Shopeers yang sudah diadopsi di `docs/UI.md`, tree-shakeable dan populer di ekosistem Vue/Tailwind.
- **Alternatif yang ditolak:** `@fontsource/inter` (self-host, lebih baik untuk privacy/offline tapi nambah bundle size & dependency — tidak krusial untuk aplikasi internal POS); Heroicons (konsisten dengan ekosistem Tailwind, tapi gaya outline/solid-nya tidak seselaras Lucide dengan referensi Shopeers).
- **Dampak:** `npm install @lucide/vue` di awal Fase 1.2. `<link>` Google Fonts ditambahkan ke `resources/views/app.blade.php`.

---

### [2026-10-02] Halaman register publik & Welcome landing page dihapus
- **Keputusan:** Route `/register` (`RegisteredUserController`, `Register.vue`), halaman landing `/` (`Welcome.vue`), dan test `RegistrationTest` dihapus. `/` sekarang redirect langsung ke `/login`.
- **Alasan:** `docs/features/auth-login.md` sudah menetapkan "tidak ada self-registration publik, hanya Admin yang bisa membuat user baru" — tapi route register bawaan Breeze masih aktif dan baru ketahuan saat test gagal karena kolom `role` (wajib, `NOT NULL`) tidak diisi oleh `RegisteredUserController`. Sekalian dibersihkan karena memang bertentangan dengan arsitektur yang sudah diputuskan. `Welcome.vue` juga dihapus karena bukan bagian dari `docs/UI.md` manapun (aplikasi internal POS, bukan produk dengan landing page publik).
- **Alternatif yang ditolak:** Isi default `role` di `RegisteredUserController` supaya test lulus, biarkan halaman register tetap ada — ditolak karena hanya menutupi gejala, bukan memperbaiki pertentangan arsitektur yang sudah didokumentasikan sebelumnya.
- **Dampak:** Pembuatan user sepenuhnya jadi tanggung jawab fitur **Manajemen User (Admin)** di `docs/ROADMAP.md` Fase 1.9. Route `/dashboard` & `Dashboard.vue` bawaan Breeze untuk sementara dibiarkan apa adanya — akan disesuaikan ke `Pages/Admin/` & `Pages/Kasir/` saat Fase 1.2 (Autentikasi & Role).

---

### [2026-10-02] Fitur "Delete Account" bawaan Breeze dihapus dari halaman Profile
- **Keputusan:** Route `DELETE /profile`, method `ProfileController::destroy()`, komponen `DeleteUserForm.vue`, dan test terkait dihapus. Halaman Profile hanya menyisakan update info profil & password.
- **Alasan:** Setelah `users` dibuat `SoftDeletes` (lihat `AGENTS.md`), `$user->delete()` bawaan Breeze menjadi soft-delete, bukan hard-delete — berbenturan dengan test bawaan yang mengharapkan `$user->fresh()` jadi `null`. Lebih penting: user menghapus akunnya sendiri tidak sesuai `docs/PRD.md` §4 — admin/kasir seharusnya **dinonaktifkan** oleh admin (`is_active = false`), bukan dihapus sendiri, supaya riwayat transaksi/shift yang terkait tetap bisa ditelusuri.
- **Alternatif yang ditolak:** Pertahankan fitur, update test supaya sesuai perilaku soft-delete — ditolak karena user POS (kasir) seharusnya memang tidak pernah bisa menghapus akunnya sendiri; ini bukan masalah teknis yang perlu "diperbaiki", tapi fitur yang memang tidak sesuai kebutuhan aplikasi.
- **Dampak:** Manajemen nonaktif/aktif user jadi tanggung jawab fitur **Manajemen User (Admin)** di `docs/ROADMAP.md` Fase 1.9, bukan halaman Profile.

---

### [2026-10-02] Cache products.stock dihitung ulang via Model Observer
- **Keputusan:** Kolom cache `products.stock` dihitung ulang otomatis lewat `StockMovementObserver` setiap kali ada row `stock_movements` baru (event `created`). Hasilnya: `SUM(quantity WHERE type IN (in, adjustment, void_return)) - SUM(quantity WHERE type = out)`.
- **Alasan:** `docs/DATABASE.md` menandai ini "perlu diputuskan saat implementasi Fase 1". Observer dipilih karena konsisten di manapun `StockMovement::create()` dipanggil (checkout, void, adjustment manual) tanpa perlu tiap Action menghitung ulang stock secara manual, dan lebih cepat dibanding hitung on-the-fly tiap request (penting untuk listing/laporan produk).
- **Alternatif yang ditolak:** Hitung on-the-fly tiap request (akurat tapi lambat untuk listing besar); database trigger (tidak terlihat dari kode PHP, menyulitkan debugging dan tidak sejalan dengan prinsip "logic bisnis di app/Actions" di `AGENTS.md`).
- **Dampak:**
  - `app/Observers/StockMovementObserver.php`, didaftarkan di `AppServiceProvider::boot()`.
  - Seeder (`ProductFactory`) sengaja BYPASS observer ini (isi `stock` langsung) untuk data dummy — dikomentari eksplisit di factory. Action sungguhan (checkout, dll) WAJIB tetap lewat `StockMovement::create()`.
  - Lihat `docs/DATABASE.md` tabel `stock_movements` & `products`.

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
