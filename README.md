# The Nice Park Rumpin

Aplikasi katalog dan pemesanan tiket umum serta paket sekolah. Admin mengelola produk dan kode redeem dari dashboard. Pelanggan dapat memesan tiket umum atau mengajukan paket sekolah, lalu membayar melalui Midtrans.

## Fitur

- Admin mengelola tiket umum dan paket sekolah, termasuk harga, kuota, status aktif, dan diskon produk.
- Katalog publik menampilkan produk aktif dan harga dari database.
- Paket sekolah memerlukan persetujuan admin sebelum pembayaran.
- Kode redeem mendukung diskon persentase atau nominal, berlaku untuk kedua jenis produk, dan hanya dapat digunakan satu kali total.
- Harga transaksi dihitung di backend. Payment menyimpan snapshot harga dan diskon.
- Midtrans menangani pembayaran; status transaksi diverifikasi di server sebelum pesanan ditandai lunas.

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- Database yang didukung Laravel, seperti SQLite atau MySQL

## Menjalankan secara lokal

1. Pasang dependency PHP dan siapkan file environment:

   ```powershell
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

2. Atur database pada `.env`. `.env.example` menggunakan SQLite. Untuk MySQL, atur `DB_CONNECTION=mysql` serta `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai database lokal.

   Jika memakai SQLite dan file database belum ada, buat file `database\database.sqlite`.

3. Jalankan migration dan siapkan asset:

   ```powershell
   php artisan migrate --no-interaction
   npm install
   npm run build
   ```

4. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

Untuk kebutuhan pengembangan frontend, jalankan `npm run dev` di terminal terpisah.

## Menyiapkan akun admin

Daftar akun melalui aplikasi, lalu berikan role admin kepada akun tersebut:

```powershell
php artisan app:make-admin email-terdaftar
```

Command hanya berlaku untuk akun yang sudah terdaftar. Jangan menaruh kata sandi atau credential admin di source code.

## Konfigurasi Midtrans

Isi konfigurasi berikut di `.env` untuk environment yang digunakan:

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_SIGNATURE_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Gunakan key Sandbox saat pengembangan dan pastikan `MIDTRANS_IS_PRODUCTION` sesuai dengan key. Jangan commit `.env` atau membagikan key. Konfigurasi webhook dan langkah verifikasi lebih rinci ada di [docs/TECHNICAL.md](docs/TECHNICAL.md).

## Pengujian

Jalankan seluruh test Laravel:

```powershell
php artisan test --compact
```

Bangun asset untuk produksi:

```powershell
npm run build
```

## Dokumentasi project

- [Skema dan tabel database](docs/DATABASE.md)
- [Matriks pengujian](docs/TESTING.md)
- [Product backlog](docs/PRODUCT-BACKLOG.md)
- [Rencana sprint](docs/SPRINT-PLAN.md)
- [Panduan belajar fitur diskon dan kode redeem](docs/maintenance/PANDUAN-BELAJAR-DISKON.md)
- [Panduan maintenance diskon dan kode redeem](docs/maintenance/PANDUAN-MAINTENANCE-DISKON.md)
