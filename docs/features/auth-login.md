# Fitur: Autentikasi (Login/Logout)

## Deskripsi
User (Admin atau Kasir) login dengan email & password untuk mengakses aplikasi. Setelah login, sistem mengarahkan ke area yang sesuai dengan role-nya (`/admin` atau `/kasir`). User yang belum login tidak bisa mengakses halaman manapun selain halaman login.

## User Role Terkait
- Admin — login lalu diarahkan ke `/admin`.
- Kasir — login lalu diarahkan ke `/kasir`. Kalau belum punya shift aktif, diarahkan lagi ke halaman Buka Shift (lihat `docs/features/checkout.md`).

## Alur
1. User buka `/login`, isi email & password.
2. Submit → backend validasi kredensial (`Auth::attempt()`).
3. Kalau gagal → tampilkan pesan error generik ("Email atau password salah"), jangan beri tahu mana yang salah (hindari user enumeration).
4. Kalau berhasil tapi `users.is_active = false` → tolak login, pesan "Akun Anda tidak aktif, hubungi admin".
5. Kalau berhasil & aktif → buat session, redirect sesuai `role`:
   - `admin` → `/admin` (Dashboard)
   - `kasir` → cek shift aktif. Ada → `/kasir` (Transaksi). Tidak ada → `/kasir/shift/buka` (Buka Shift).
6. User klik Logout → session dihancurkan, redirect ke `/login`.

## Aturan Bisnis Khusus
- Tidak ada fitur "lupa password" / reset password lewat email di versi awal (lihat `docs/PRD.md` — belum masuk scope eksplisit; kalau dibutuhkan, tambahkan ke PRD §5 dulu sebelum dikerjakan).
- Tidak ada pembatasan jumlah percobaan login (rate limiting) secara eksplisit di PRD — tapi **tetap pakai** `ThrottleRequests` bawaan Laravel di route login (default framework, bukan fitur tambahan) sebagai praktik keamanan standar.
- Hanya Admin yang bisa membuat akun user baru (lihat `docs/features/`, belum dibuat — menyusul). Tidak ada self-registration publik.

## Data yang Terlibat
- Tabel: `users`
- Field penting: `email` (unique), `password` (hashed), `role` (`admin`/`kasir`), `is_active`

## Acceptance Criteria
- [ ] User dengan kredensial benar & aktif berhasil login dan diarahkan sesuai role.
- [ ] User dengan kredensial salah mendapat pesan error generik, tidak ada info mana yang salah.
- [ ] User dengan `is_active = false` ditolak login meski kredensial benar.
- [ ] Kasir tanpa shift aktif diarahkan ke halaman Buka Shift setelah login, bukan langsung ke Transaksi.
- [ ] User yang belum login tidak bisa akses route `/admin/*` atau `/kasir/*` manapun (redirect ke `/login`).
- [ ] Logout menghancurkan session dan route yang tadinya bisa diakses jadi terkunci lagi.
- [ ] Test: Feature test untuk tiap skenario di atas (Pest).

## Catatan Teknis
- Middleware: `auth` (bawaan Laravel) untuk gate semua route admin/kasir. Middleware tambahan `role:admin` / `role:kasir` untuk membedakan akses sesuai pemisahan route di `AGENTS.md`.
- Redirect berdasarkan role sebaiknya ditangani di satu tempat (misal `LoginResponse` custom atau method di `AuthenticatedSessionController`), bukan logic tersebar di beberapa controller.
- Cek shift aktif kasir: query `Shift::where('user_id', $id)->where('status', 'open')->exists()` — pertimbangkan taruh di middleware terpisah (`EnsureShiftActive`) supaya dicek ulang di tiap request ke route kasir, bukan cuma saat login (kasir bisa logout di tengah shift lalu login lagi).
