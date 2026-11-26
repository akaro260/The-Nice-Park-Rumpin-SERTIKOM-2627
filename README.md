# The Nice Park Rumpin

Aplikasi katalog dan pemesanan tiket umum serta paket sekolah. Admin mengelola produk dan kode redeem dari dashboard. Pelanggan dapat memesan tiket umum atau mengajukan paket sekolah, lalu membayar melalui Midtrans.

## Fitur website

### Pengunjung dan pelanggan

- Halaman utama menampilkan tiket umum dan paket sekolah aktif dari database, termasuk deskripsi, harga, promo produk, dan kuota.
- Halaman detail menampilkan informasi produk dan ketersediaan berdasarkan tanggal kunjungan.
- Pelanggan dapat membuat akun, masuk, memverifikasi email, meminta reset kata sandi, dan mengelola profil.
- Pemesanan tiket umum meminta tanggal kunjungan, jumlah tiket, serta kode redeem jika tersedia.
- Pengajuan paket sekolah meminta nama sekolah, penanggung jawab, tanggal kunjungan, dan jumlah peserta. Pengajuan harus disetujui admin sebelum pembayaran.
- Dashboard pelanggan menampilkan daftar pesanan, status pengajuan dan pembayaran, rincian diskon, alasan penolakan, serta kode e-ticket setelah pembayaran berhasil.
- Pelanggan dapat melanjutkan pembayaran yang masih menunggu atau meminta aplikasi memeriksa status terbaru ke Midtrans.

### Dashboard admin

- Admin dapat menambah, melihat, mengubah, dan menghapus tiket umum maupun paket sekolah.
- Data produk mencakup nama, deskripsi, harga, kuota per tanggal, dan status aktif. Produk nonaktif tidak tampil di katalog publik.
- Admin dapat mengatur diskon produk berupa persentase atau nominal tetap, masa berlaku, dan status aktif.
- Admin dapat meninjau pengajuan sekolah, menyetujui atau menolak pengajuan, serta mencatat alasan penolakan.
- Admin dapat membuat kode redeem dengan diskon persentase atau nominal, mengaktifkan atau menonaktifkannya, dan melihat status penggunaannya. Kode berlaku satu kali total untuk tiket umum atau paket sekolah.
- Produk yang sudah memiliki riwayat pesanan tidak dapat dihapus. Admin dapat menonaktifkannya agar produk tidak muncul lagi di katalog.

### Harga dan pembayaran

- Backend menghitung harga dan total transaksi dari data produk. Harga yang dikirim browser tidak digunakan sebagai sumber transaksi.
- Diskon produk hanya berlaku dalam periode yang diatur admin. Kode redeem menggantikan diskon produk pada transaksi yang menggunakannya.
- Rincian harga, diskon, kode redeem, dan total disimpan pada pembayaran agar riwayat transaksi tetap sesuai dengan saat pemesanan.
- Pembayaran menggunakan Midtrans Snap. Server memeriksa signature notifikasi, order, dan nominal sebelum mengonfirmasi pesanan.
- Kuota tanggal kunjungan dikelola saat checkout. Kuota yang tertahan dilepas jika pembayaran gagal, dibatalkan, atau kedaluwarsa.
- Setelah pembayaran terverifikasi, pesanan dikonfirmasi dan kode e-ticket ditampilkan pada dashboard pelanggan.

### Tampilan dan gambar

- Antarmuka menggunakan palet Tropical Adventure dan dirancang untuk tampilan desktop maupun perangkat mobile.
- Hero dan area foto produk saat ini berupa placeholder. Upload gambar ke Laravel Storage belum tersedia.

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
