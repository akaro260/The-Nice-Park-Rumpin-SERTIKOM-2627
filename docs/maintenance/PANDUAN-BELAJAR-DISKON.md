# Panduan Belajar Fitur Diskon

Panduan ini membantu memahami cara fitur diskon tiket umum dan paket sekolah bekerja di Laravel. Baca file sesuai urutan supaya alurnya terlihat dari data produk sampai pembayaran.

## 1. Gambaran singkat

Produk tiket umum dan paket sekolah disimpan bersama di tabel `tickets`. Kolom `type` membedakan produk `general` dan `school`.

Admin menyimpan harga dan diskon produk pada produk, lalu membuat kode redeem secara terpisah. Website memakai satu layanan untuk menghitung harga. Saat payment dibuat, aplikasi menyimpan salinan rincian harganya agar perubahan produk sesudahnya tidak mengubah riwayat transaksi.

```text
Admin mengatur produk
    -> ProductController memvalidasi dan menyimpan Ticket
Admin membuat kode redeem
    -> PromoCodeController memvalidasi dan menyimpan PromoCode
Pengunjung melihat katalog
    -> CatalogController meminta harga ke TicketPricingService
Pelanggan checkout
    -> CheckoutService mengambil harga Ticket dan, bila diisi, mengunci kode redeem
    -> TicketPricingService menghitung total di server
    -> Payment menyimpan snapshot dan total yang dikirim ke Midtrans
Midtrans mengirim notifikasi
    -> CheckoutService mencocokkan order dan nominal, lalu menandai atau melepas kode
```

## 2. Baca file dalam urutan ini

1. [`Ticket.php`](../app/Models/Ticket.php) menyimpan relasi dan casts untuk harga, tanggal promo, dan status.
2. [`ProductController.php`](../app/Http/Controllers/Admin/ProductController.php) memvalidasi serta menyimpan konfigurasi produk dan diskon.
3. [`PromoCodeController.php`](../app/Http/Controllers/Admin/PromoCodeController.php) mengelola kode dari dashboard admin.
4. [`index.blade.php`](../resources/views/admin/promo-codes/index.blade.php) memuat formulir pembuatan dan daftar kode redeem.
5. [`PromoCode.php`](../app/Models/PromoCode.php) mendefinisikan status reservasi dan pemakaian kode.
6. [`PromoCodeService.php`](../app/Services/PromoCodeService.php) mengunci kode dan menangani reservasi, konsumsi, serta pelepasan.
7. [`TicketPricingService.php`](../app/Services/TicketPricingService.php) menghitung harga normal, harga setelah diskon, potongan, dan total.
8. [`CatalogController.php`](../app/Http/Controllers/CatalogController.php) meminta hasil hitung untuk Welcome dan halaman detail.
9. [`CheckoutService.php`](../app/Services/CheckoutService.php) menghitung harga dari database, menyimpan payment, lalu mengirim total ke Midtrans.
10. [`Payment.php`](../app/Models/Payment.php) mendefinisikan relasi dan casts data payment.
11. [`migration diskon produk`](../database/migrations/2026_11_24_142352_add_discount_pricing_snapshots_to_tickets_and_payments.php) menambahkan diskon produk dan snapshot harga.
12. [`migration kode redeem`](../database/migrations/2026_11_25_115406_create_promo_codes_table.php) membuat tabel kode dan menambah snapshot kode pada payment.
13. [`CatalogAndBookingTest.php`](../tests/Feature/CatalogAndBookingTest.php) menunjukkan contoh perilaku yang harus tetap benar.
14. [`web.php`](../routes/web.php) memperlihatkan route katalog, checkout, notifikasi, dan pengelolaan admin.

## 3. Konsep Laravel yang terlihat di fitur ini

### Model

`Ticket` dan `Payment` adalah model Eloquent. Model menghubungkan aplikasi ke tabel database dan mendefinisikan casts. Contohnya, `discount_starts_on` dan `discount_ends_on` pada `Ticket` dibaca sebagai tanggal.

`TicketPricingService` bukan model. Service memusatkan aturan hitung supaya tampilan katalog dan checkout tidak membuat rumus masing-masing.

### Route dan controller

Route memberi nama pada alamat aplikasi. Controller menerima request lalu menjalankan alur yang sesuai.

- `home` menampilkan katalog.
- `catalog.show` menampilkan detail satu produk.
- `admin.products.*` dikelola oleh resource controller dan dilindungi middleware admin.
- `admin.promo-codes.*` mengelola kode redeem dan dilindungi middleware admin.
- `bookings.store` memulai pemesanan umum.
- `payments.checkout` memulai pembayaran pengajuan sekolah yang sudah disetujui.
- `midtrans.notifications` menerima notifikasi pembayaran.

Nama route merupakan pengenal yang dipakai Blade dan controller. Gunakan `route('nama.route')` daripada menulis URL berulang kali.

### Validasi

`ProductController::validatedData()` memeriksa input admin di server. Validasi browser membantu pemakaian formulir, tetapi bukan pengganti validasi server.

Checkout juga tidak mempercayai harga atau total yang dikirim browser. Aplikasi mengambil produk dari database dan menghitung total di server.

## 4. Cara kerja penghitungan harga

`TicketPricingService::quote($ticket, $quantity, $promoCode)` mengembalikan rincian berikut. Argumen kode boleh kosong:

| Nilai | Makna |
|---|---|
| `normal_unit_amount` | Harga normal untuk satu tiket atau satu paket |
| `unit_amount` | Harga satu unit setelah diskon, atau harga normal jika promo tidak berlaku |
| `subtotal_amount` | Harga normal dikalikan jumlah unit yang dihitung |
| `discount_type`, `discount_value` | Jenis dan nilai diskon yang benar-benar dipakai pada quote |
| `discount_amount` | Jumlah potongan total |
| `gross_amount` | Total akhir yang harus dibayar |
| `is_discounted` | Bernilai benar jika ada potongan |

Diskon hanya digunakan jika toggle diskon aktif, konfigurasinya lengkap dan valid, serta tanggal aplikasi berada di antara tanggal mulai dan akhir. Kedua tanggal termasuk masa promo. Zona waktu berasal dari konfigurasi aplikasi, yang saat ini memakai `Asia/Jakarta`.

Jika pelanggan memberi kode redeem, kode menggantikan diskon produk dan menghitung potongan dari harga normal. Kode tidak ditumpuk dengan promo produk. Tanpa kode, diskon produk yang sedang aktif tetap berlaku.

### Contoh tiket umum

Misalnya harga tiket Rp100.000, diskon tetap Rp10.000, dan pembeli memesan dua tiket:

- Harga normal per tiket: Rp100.000
- Harga setelah diskon per tiket: Rp90.000
- Subtotal normal: Rp200.000
- Total potongan: Rp20.000
- Total pembayaran: Rp180.000

Diskon tetap berlaku untuk setiap tiket umum yang dipesan.

### Contoh paket sekolah

Misalnya harga paket Rp100.000 dengan diskon tetap Rp15.000. Paket sekolah dikenakan sekali per pengajuan, bukan dikalikan jumlah peserta. Checkout paket memanggil quote dengan jumlah satu.

Harga total setelah diskon menjadi Rp85.000 meskipun pengajuan mencatat banyak peserta.

### Pembulatan persentase

Persentase harus berupa bilangan bulat 1 sampai 99. Harga satu unit dibulatkan ke Rupiah terdekat dengan aturan half-up. Contohnya, Rp1.001 setelah diskon 10% menjadi Rp901. Katalog dan checkout memakai layanan hitung yang sama.

## 5. Mengapa ada snapshot pada payment?

`Payment` menyimpan `subtotal_amount`, `discount_type`, `discount_value`, `discount_amount`, `gross_amount`, serta `promo_code` jika pelanggan memakai kode redeem.

Contoh: pelanggan membayar saat harga normal Rp100.000 dan harga promo Rp90.000. Jika admin mengubah harga produk esok hari, baris payment tetap menunjukkan angka saat transaksi dibuat. Sistem tidak menghitung ulang riwayat memakai harga produk terbaru.

Untuk paket sekolah, pengajuan dibuat sebelum pembayaran dan menunggu persetujuan admin. Harga promo dinilai saat checkout setelah persetujuan. Jika payment masih pending dan dipakai kembali, aplikasi mengembalikan payment yang sama. Percobaan payment baru menghitung ulang ketika alur mengizinkan checkout baru.

Webhook Midtrans diverifikasi di server. Aplikasi mencocokkan order ID serta nominal dari status Midtrans dengan payment lokal sebelum mengonfirmasi pesanan.

## 6. Cara kerja kode redeem

Admin membuat kode dari menu **Kode redeem**. Setiap kode memiliki satu jenis potongan, aktif/nonaktif, dan hanya dapat berhasil dipakai satu kali total. Admin dapat menghapus kode selama belum dicadangkan atau digunakan. Kode yang sudah dipakai dapat dinonaktifkan, tetapi tidak dapat diaktifkan kembali.

Pelanggan tiket umum mengisi kode pada formulir detail tiket. Pelanggan paket sekolah mengisinya di halaman pesanan saat checkout setelah persetujuan admin. Server menormalisasi kode menjadi huruf kapital, memeriksa ketersediaan, lalu menghitung harga.

Saat server membuat payment, ia mencadangkan kode untuk payment itu. Jika Midtrans mengonfirmasi pembayaran, kode ditandai terpakai dan menyimpan payment, pelanggan, serta waktunya. Jika payment berakhir, dibatalkan, atau ditolak, server melepaskan kode supaya dapat dicoba lagi. Sementara payment pending, kode tidak dapat dipakai untuk checkout lain.

## 7. Latihan membaca kode

1. Buka `Ticket.php` dan temukan casts untuk `discount_value` serta tanggal diskon.
2. Buka `ProductController.php`, lalu ikuti validasi dari `validatedData()` sampai pemanggilan `create()` atau `update()`.
3. Buka `TicketPricingService.php` dan telusuri dari nilai harga normal sampai array hasil quote.
4. Buka `CatalogController.php` untuk melihat bagaimana hasil quote diberikan ke Blade.
5. Buka `CheckoutService.php` dan cari `createPayment()`. Perhatikan bahwa nilai `gross_amount` dari quote disimpan dan dikirim ke Midtrans.
6. Buka `PromoCodeService.php`, lalu ikuti method yang mengunci, mencadangkan, menandai terpakai, dan melepaskan kode.
7. Buka test yang judulnya memuat “redeem codes”, “school customer to redeem”, dan “expired payment”. Cocokkan ekspektasi test dengan alur di service.

Perintah untuk menjalankan pengujian katalog dan pemesanan:

```powershell
php artisan test --compact tests\Feature\CatalogAndBookingTest.php
```

Jalankan pengujian di environment test, bukan pada database produksi.

## 8. Ringkasan yang perlu diingat

- Tabel `tickets` menyimpan produk tiket umum dan paket sekolah.
- `TicketPricingService` adalah satu sumber perhitungan harga untuk tampilan dan checkout.
- Harga browser bukan sumber harga transaksi.
- Diskon nominal diterapkan per tiket umum dan sekali per pengajuan sekolah.
- Kode redeem menggantikan promo produk dan hanya dapat berhasil dipakai satu kali total.
- Payment pending memegang reservasi kode; kode dipakai setelah pembayaran berhasil.
- Setiap payment menyimpan snapshot harga sendiri.
- Midtrans menerima total yang dihitung backend; notifikasi harus cocok dengan payment lokal.

Untuk langkah operasional, validasi admin, deployment migration, dan pemecahan masalah, lanjutkan ke [`Panduan Maintenance Diskon`](PANDUAN-MAINTENANCE-DISKON.md).
