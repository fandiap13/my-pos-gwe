# UI / UX Design

## Prinsip Desain
- **Kasir dulu, admin kedua.** Layar kasir adalah layar yang paling sering dipakai sepanjang hari — harus cepat, minim klik, besar (ramah sentuh/mouse cepat), dan tidak butuh scroll untuk aksi utama (cari produk → bayar).
- **Angka besar & jelas.** Harga, total, kembalian ditulis dengan font besar — ini yang paling sering dilihat kasir & pelanggan.
- **Konfirmasi untuk aksi berisiko.** Void transaksi, hapus produk, tutup shift dengan selisih kas — selalu ada dialog konfirmasi, tidak langsung eksekusi.
- **Responsive, prioritas desktop/tablet.** Kasir kemungkinan pakai PC/tablet di meja kasir (bukan HP), tapi halaman admin/laporan tetap harus bisa dibuka dari HP untuk pemilik toko yang mobile.
- **State stok & shift selalu terlihat.** Kasir harus selalu tahu: shift sedang aktif atau tidak, dan kalau stok produk yang dicari menipis/habis.

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
| Dashboard | Admin | Ringkasan: penjualan hari ini, shift aktif, stok menipis (lihat 5.2 PRD — nice to have, bisa versi sederhana dulu) |
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

## Komponen UI yang Dipakai Berulang
- **ProductSearchInput** — search box dengan autocomplete produk (nama/SKU/barcode), dipakai di halaman Transaksi.
- **CartTable** — tabel keranjang belanja dengan kontrol quantity (+/-), dipakai di halaman Transaksi.
- **MoneyInput** — input angka khusus format Rupiah (tanpa desimal, auto-format ribuan), dipakai di semua form harga/pembayaran.
- **StatusBadge** — badge warna untuk status (`completed`/`voided`, `open`/`closed`, stok menipis), konsisten di seluruh tabel.
- **ConfirmDialog** — dialog konfirmasi generik untuk aksi berisiko (void, hapus, tutup shift dengan selisih).
- **DateRangeFilter** — filter rentang tanggal, dipakai di Laporan & Riwayat Transaksi.
- **ReceiptPreview** — komponen cetak struk (format thermal 58mm/80mm), dipakai di Transaksi (setelah checkout) dan Riwayat Transaksi (cetak ulang).
- **RoleGuardLayout** — layout wrapper yang cek role & shift aktif (khusus Kasir), redirect kalau tidak memenuhi syarat.

## Hal yang Masih Perlu Diputuskan
- Modal inline vs halaman terpisah untuk tambah kategori cepat dari form produk.

## Design Tokens
> **Diisi sebelum mengerjakan `docs/ROADMAP.md` Fase 1.2** (sebelum halaman Login dibuat), supaya semua halaman berikutnya konsisten sejak awal — bukan restyle ulang di akhir. Diterapkan ke `tailwind.config.js`.

- **Warna primer** (tombol utama, link aktif, highlight): `#` — *perlu diisi*
- **Warna sekunder** (tombol kedua, aksen): `#` — *perlu diisi*
- **Warna status badge:**
  - Sukses / stok aman: `#` — *perlu diisi*
  - Warning / stok menipis: `#` — *perlu diisi*
  - Bahaya / stok habis, voided: `#` — *perlu diisi*
- **Font** (kalau bukan default Tailwind/system font): `` — *perlu diisi*
- **Logo toko** (kalau ada, untuk header/struk): — *perlu diisi*

## Referensi Visual
- (tempel link mockup/Figma/screenshot referensi di sini)
