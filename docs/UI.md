# UI / UX Design

## Prinsip Desain
- **Kasir dulu, admin kedua.** Layar kasir adalah layar yang paling sering dipakai sepanjang hari — harus cepat, minim klik, besar (ramah sentuh/mouse cepat), dan tidak butuh scroll untuk aksi utama (cari produk → bayar).
- **Angka besar & jelas.** Harga, total, kembalian ditulis dengan font besar — ini yang paling sering dilihat kasir & pelanggan.
- **Konfirmasi untuk aksi berisiko.** Void transaksi, hapus produk, tutup shift dengan selisih kas — selalu ada dialog konfirmasi, tidak langsung eksekusi.
- **Responsive, prioritas desktop/tablet.** Kasir kemungkinan pakai PC/tablet di meja kasir (bukan HP), tapi halaman admin/laporan tetap harus bisa dibuka dari HP untuk pemilik toko yang mobile.
- **State stok & shift selalu terlihat.** Kasir harus selalu tahu: shift sedang aktif atau tidak, dan kalau stok produk yang dicari menipis/habis.
- **Segar & bersih.** Brand hijau dipilih karena toko mengutamakan kesegaran produk. Tampilan terang, lega, dan tidak ramai — warna hijau dipakai untuk aksi utama dan penanda aktif, bukan untuk dekorasi.
- **Ramah segala usia.** Target pengguna termasuk kasir lanjut usia (ada yang rabun dekat) — teks isi (input, label, tombol, isi tabel, teks transaksi) minimal `text-base` (16px), bukan `text-sm` (14px). `text-sm`/`text-xs` hanya untuk metadata sekunder (helper text, header kolom tabel, timestamp, badge).

## Daftar Halaman

### Mode Kasir (`resources/js/Pages/Kasir/`)
| Halaman | Role Akses | Deskripsi Singkat |
|---|---|---|
| Buka Shift | Kasir | Form input modal awal kas, muncul otomatis kalau kasir belum punya shift aktif (gate sebelum bisa akses halaman kasir lain) |
| Transaksi / Checkout | Kasir | Halaman utama: cari produk, keranjang, input pembayaran, cetak struk |
| Riwayat Transaksi (saya) | Kasir | Daftar transaksi milik kasir yang login hari ini/filter tanggal, bisa cetak ulang struk |
| Tutup Shift | Kasir | Rekap kas sistem vs input kas fisik, selisih ditampilkan sebelum konfirmasi tutup |

### Mode Admin (`resources/js/Pages/Admin/`)
| Halaman | Role Akses | Deskripsi Singkat |
|---|---|---|
| Dashboard | Admin | Ringkasan: penjualan hari ini, shift aktif, stok menipis (lihat 5.2 PRD — nice to have, bisa versi sederhana dulu). Pola layout mengacu ke "Referensi Visual" di bawah |
| Produk — Daftar | Admin | Tabel produk, filter kategori, search, indikator stok menipis |
| Produk — Tambah/Edit | Admin | Form produk: nama, SKU/barcode, kategori, harga jual, harga modal, stok awal, stok minimum |
| Kategori — Daftar | Admin | Tree/list kategori & subkategori (self-referencing, lihat `docs/DATABASE.md`) |
| Kategori — Tambah/Edit | Admin | Form kategori, pilih parent (opsional) |
| Stok — Riwayat Pergerakan | Admin | Daftar `stock_movements`, filter produk/tipe/tanggal |
| Stok — Penyesuaian Manual | Admin | Form input `adjustment` manual (barang masuk/koreksi), wajib isi catatan |
| User — Daftar | Admin | Tabel user (admin & kasir), status aktif/nonaktif |
| User — Tambah/Edit | Admin | Form user, set role, set `is_active` |
| Riwayat Transaksi (semua) | Admin | Semua transaksi semua kasir, filter tanggal/kasir/status, aksi void |
| Detail Transaksi | Admin, Kasir (miliknya) | Rincian item, info pembayaran, histori void (kalau ada) |
| Laporan Penjualan | Admin | Filter rentang tanggal, total penjualan, grafik ringkas, produk terlaris |
| Laporan per Shift/Kasir | Admin | Rekap per shift: modal awal, total transaksi, selisih kas |
| Pengaturan Toko | Admin | Nama toko, alamat, telepon, **zona waktu** (`store_settings`, lihat `docs/DATABASE.md`) |

### Shared
| Halaman | Role Akses | Deskripsi Singkat |
|---|---|---|
| Login | Semua | Redirect ke `/kasir` atau `/admin` sesuai role setelah login |
| Profil | Semua | Ubah nama, password |

## Alur Utama (User Flow)

### Alur Buka Shift → Transaksi → Tutup Shift
1. Kasir login → sistem cek apakah ada shift `status = open` milik user ini.
2. Kalau tidak ada → arahkan ke halaman **Buka Shift**, input modal awal kas → submit → shift dibuat.
3. Kasir masuk ke halaman **Transaksi**: cari produk (nama/barcode/scan), klik/scan untuk tambah ke keranjang.
4. Atur quantity per item di keranjang, sistem hitung subtotal & total otomatis.
5. Klik "Bayar" → pilih metode pembayaran (cash/transfer/debit) → kalau cash, input nominal dibayar → sistem hitung kembalian otomatis.
6. Konfirmasi → sistem simpan transaksi (`DB::transaction()`: insert `transactions`, `transaction_items`, `stock_movements` type `out`, update cache `products.stock`).
7. Tampilkan halaman/preview struk → kasir klik "Cetak" (`window.print()`) atau "Lewati".
8. Keranjang kosong, siap transaksi berikutnya.
9. Di akhir sesi kerja, kasir klik **Tutup Shift** → sistem tampilkan kas seharusnya (`expected_cash`) vs input kas fisik aktual → tampilkan selisih → konfirmasi tutup.

### Alur Void Transaksi (Admin)
1. Admin buka **Riwayat Transaksi** → pilih transaksi berstatus `completed`.
2. Klik "Void" → dialog konfirmasi, wajib isi alasan (`void_reason`).
3. Konfirmasi → sistem update `status = voided`, `voided_by`, `voided_at`, DAN insert `stock_movements` type `void_return` untuk tiap item (dalam satu `DB::transaction()`).
4. Transaksi tetap muncul di riwayat dengan badge "Voided" + alasan, tidak hilang dari daftar.

### Alur Tambah Produk dengan Kategori Baru
1. Admin buka **Produk — Tambah** → isi field dasar.
2. Di dropdown kategori, kalau kategori yang diinginkan belum ada → admin buka **Kategori — Tambah** (bisa di tab/modal terpisah agar tidak kehilangan form produk yang sedang diisi — *keputusan UX ini perlu dikonfirmasi: modal inline atau pindah halaman*).
3. Simpan kategori → kembali ke form produk, kategori baru otomatis muncul di dropdown.

### Alur Stok Menipis & Stok Habis
1. Sistem bandingkan `products.stock` (cache) terhadap `products.min_stock` di halaman **Produk — Daftar** dan **Dashboard**.
2. Produk dengan stok ≤ minimum (tapi masih > 0) ditandai visual (badge/warna kuning "stok menipis"), transaksi tetap boleh jalan normal.
3. Produk dengan stok = 0 **diblokir dari checkout**: tidak bisa ditambah ke keranjang (search/scan menampilkan badge "Stok Habis", tombol tambah nonaktif). Kalau produk sudah telanjur ada di keranjang lalu stok berubah jadi 0 (race condition, misal 2 kasir checkout bersamaan), validasi ulang stok dilakukan di server saat submit — tampilkan error dan keluarkan item itu dari keranjang.
4. Stok tidak pernah minus. Koreksi stok (misal admin temukan barang fisik tambahan) dilakukan lewat halaman **Stok — Penyesuaian Manual** (`stock_movements` type `adjustment`), bukan lewat transaksi.

## Komponen UI

Dibangun di Fase 1.2 (`docs/ROADMAP.md`), sebelum halaman fitur dibuat. Semua di `resources/js/Components/`, kecuali Layout.

### Dasar (generik, dipakai di Admin maupun Kasir)
`Button`, `Input`, `MoneyInput` (format Rupiah, tanpa desimal), `Textarea`, `Select`, `Checkbox`, `Radio`, `FormField` (label + input + error), `Badge`, `StatusBadge` (status `completed`/`voided`, `open`/`closed`, stok menipis/habis), `Card`, `Modal`, `ConfirmDialog` (aksi berisiko: void, tutup shift selisih), `Dropdown`, `Tooltip`, `Alert`, `Toast`, `Spinner`, `Skeleton`, `EmptyState`, `Pagination`, `Table`, `TableActions`, `Tabs`, `Breadcrumb`, `DatePicker`, `DateRangePicker` (filter Laporan & Riwayat Transaksi).

### Khusus POS (konteks checkout/kasir)
`ProductSearch` (cari produk nama/SKU/barcode), `ProductCard`, `CartItem`, `QuantityInput` (+/−), `PaymentMethodSelector` (cash/transfer/debit), `PaymentSummary` (subtotal/total/kembalian), `ReceiptPreview` (struk 58mm/80mm, dipakai di Transaksi & cetak ulang Riwayat).

### Khusus Dashboard & Laporan (mengacu ke Referensi Visual)
`StatCard` (label + ikon kecil, angka besar, `DeltaBadge`, teks muted "vs. periode lalu"), `DeltaBadge` (naik = hijau, turun = merah), `LineChart` (garis + area gradasi tint, garis putus-putus periode pembanding, tooltip hover), `BarChart` (satu bar disorot warna primer, mis. penjualan per hari/jam), `SubMetricBar` (angka + garis warna di bawahnya, untuk rincian di dalam kartu), `DataTable` ringkas (kolom angka rata kanan, thumbnail produk, ikon naik/turun) untuk "Produk Terlaris".

### Layout
`AdminLayout` (sidebar + header, `Pages/Admin/`), `KasirLayout` (minim distraksi, `Pages/Kasir/`), `AuthLayout` (halaman login).

- **AdminLayout:** sidebar kiri lebar tetap (±200px) dengan logo + tombol collapse, menu beribu ikon, item aktif berlatar tint primer + garis penanda di kiri, badge hitungan kecil di menu (mis. jumlah stok menipis), grup menu yang bisa dilipat (mis. Laporan → Penjualan, Per Shift), serta Pengaturan & Bantuan di dasar sidebar. Header atas: search dengan shortcut `⌘K`, notifikasi, avatar. Di mobile sidebar menjadi drawer.
- **KasirLayout:** sengaja lebih ringan dari AdminLayout — tanpa sidebar penuh, hanya header tipis (nama toko, indikator shift aktif, nama kasir, akses ke Riwayat/Tutup Shift) agar area kerja checkout maksimal.

**Aturan:** setiap komponen punya state default/hover/focus/active/disabled/loading/error (+ empty state kalau relevan). Cek role & shift aktif kasir ditangani middleware Laravel (`role:admin`, `EnsureShiftActive` — lihat `AGENTS.md`), bukan komponen Vue terpisah.

## Hal yang Masih Perlu Diputuskan
- Modal inline vs halaman terpisah untuk tambah kategori cepat dari form produk.
- Logo toko (untuk header & struk).
- Dark mode — belum dijadwalkan; token dibuat sebagai CSS variable/Tailwind theme supaya bisa ditambah nanti tanpa restyle.

## Design Tokens
> Diterapkan ke konfigurasi Tailwind sebelum mengerjakan `docs/ROADMAP.md` Fase 1.2 (sebelum halaman Login dibuat). Kalau project memakai Tailwind v4, definisikan sebagai `@theme` di CSS utama; kalau v3, di `tailwind.config.js`.

### Warna

**Brand**
| Token | Hex | Pemakaian |
|---|---|---|
| `primary` | `#16A34A` | Highlight, link aktif, ikon aksen, bar/grafik utama |
| `primary-dark` | `#15803D` | Tombol utama solid (teks putih), hover/pressed |
| `primary-light` | `#DCFCE7` | Latar item sidebar aktif, tint badge sukses, area gradasi grafik |

**Neutral**
| Token | Hex | Pemakaian |
|---|---|---|
| `background` | `#F8FAF9` | Latar halaman |
| `surface` | `#FFFFFF` | Kartu, tabel, modal, sidebar, header |
| `border` | `#E2E8E4` | Border kartu, tabel, input, divider |

**Teks**
| Token | Hex | Pemakaian |
|---|---|---|
| `text` | `#17221B` | Teks utama, angka besar |
| `text-muted` | `#66736B` | Label, deskripsi, "vs. periode lalu" |
| `text-faint` | `#94A39A` | Placeholder, header kolom tabel, teks nonaktif |

**Status**
| Token | Hex | Pemakaian |
|---|---|---|
| `success` | `#16A34A` | Sukses / stok aman, delta naik |
| `warning` | `#F59E0B` | Stok menipis |
| `danger` | `#DC2626` | Stok habis, voided, delta turun, aksi destruktif |
| `info` | `#2563EB` | Informasi netral, metrik pembanding di grafik |

> Warna **sekunder** tidak dibuat sebagai hue terpisah. Tombol kedua = outline (border `border`, teks `text`, hover latar `primary-light`). Aksen tambahan di grafik memakai `info` dan `warning`.

### Aturan pemakaian warna
- **Kontras:** teks putih hanya di atas `primary-dark` (±5:1). Putih di atas `primary` (`#16A34A`) hanya ±3,3:1 — cukup untuk ikon dan teks bold besar, tidak untuk teks kecil.
- **Badge status** selalu berupa tint (latar muda + teks gelap warna status), bukan solid. Ini juga membedakan badge "stok aman" dari tombol primer yang sama-sama hijau, dan membuat `warning` tetap terbaca (teks amber gelap di atas latar kuning pucat, bukan putih di atas `#F59E0B`).
- **Tombol aksi utama** (mis. "Bayar", "Simpan") selalu solid `primary-dark`; tombol sekunder selalu outline.
- **Delta angka:** naik = `success`, turun = `danger`, selalu disertai ikon panah (tidak mengandalkan warna saja).
- **Sub-metrik / seri grafik:** urutan warna `primary` → `info` → `warning`.

### Tipografi
- **Font:** `Inter` (fallback: `ui-sans-serif, system-ui, sans-serif`).
- **Angka:** gunakan tabular numbers (`font-variant-numeric: tabular-nums`) di tabel, total, dan kembalian agar digit sejajar.
- **Skala:** teks kecil (12–13px) untuk label & metadata di admin; angka KPI besar (±28–32px) dan **total/kembalian di layar kasir lebih besar lagi** (sesuai prinsip "angka besar").

### Bentuk & Elevasi
- Kartu: radius ±16px, border 1px `border`, bayangan sangat tipis; jarak antar kartu lega.
- Input & tombol: radius ±8–10px.
- Overlay modal/drawer: latar gelap semi-transparan.

### Format Angka
- Mata uang Rupiah tanpa desimal dengan pemisah ribuan titik: `Rp 446.700` (bukan `$446.7K`). Angka besar di dashboard boleh disingkat (`Rp 446,7 jt`) hanya di KPI, tidak di tabel/struk.

### Logo
- Logo toko (untuk header/struk): — *perlu diisi*

## Referensi Visual

**Sumber:** mockup dashboard admin e-commerce "Shopeers" (mode terang). Dipakai sebagai acuan **gaya dan struktur layout untuk halaman Admin (Dashboard & Laporan)** — **bukan** untuk layar Kasir, karena tekstur teksnya kecil dan padat, sedangkan layar kasir butuh angka besar dan tanpa scroll. **Warna tidak mengikuti referensi** (referensi biru → diganti palet hijau di atas); semua elemen biru di referensi (tombol utama, item sidebar aktif, bar yang disorot) menjadi `primary`/`primary-dark`.

**Struktur layout referensi**
- Sidebar kiri + header atas (search `⌘K`, notifikasi, avatar) + area konten dengan judul halaman di kiri dan kontrol di kanan (rentang tanggal, dropdown preset "30 hari terakhir", tombol outline, tombol utama "Export").
- Grid kartu: baris atas 4 KPI; di bawahnya kolom lebar (grafik + tabel) di kiri dan kolom sempit (widget kecil) di kanan.

**Elemen yang diadopsi**
| Elemen referensi | Padanan di aplikasi ini |
|---|---|
| Sidebar + header | `AdminLayout` |
| 4 KPI card + delta badge | Dashboard: penjualan hari ini, jumlah transaksi, shift aktif, produk stok menipis |
| Grafik garis + tooltip + garis pembanding | Laporan Penjualan & Dashboard |
| 3 sub-metrik bergaris warna di kartu profit | Rincian per metode bayar (cash/transfer/debit) |
| Bar chart per hari (satu bar disorot) | Penjualan per hari/jam ramai |
| Tabel "Best Selling Products" | Produk terlaris di Laporan |
| Date range + dropdown preset + Export | `DateRangePicker` (Export → Fase 2) |
| Badge hitungan di menu sidebar | Indikator stok menipis |

**Elemen yang tidak diadopsi (di luar scope PRD)**
- Widget builder "Add Widget" (drawer + drag & drop), AI Assistant, kartu promo "Upgrade to Premium".
- Metrik web e-commerce: Page Views, Visitors, Click, menu Content/Online Store (toko ini fisik, bukan online).
- Ditunda: Repeat Customer Rate & Customers (menunggu fitur member di Fase 2), toggle tema gelap.

**Catatan penyesuaian**
- Semua nilai uang berformat Rupiah (lihat "Format Angka").
- Referensi hanya tampilan terang; tidak ada contoh layar kasir, jadi layar Transaksi/Checkout didesain terpisah dengan prinsip di bagian atas dokumen.

- (tempel link mockup/Figma/screenshot referensi tambahan di sini)
