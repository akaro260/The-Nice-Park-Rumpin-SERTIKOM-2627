# Panduan Maintenance Fitur Diskon

Dokumen ini berisi prosedur untuk mengelola promo dan mengubah kode diskon tanpa merusak harga checkout, riwayat payment, kuota, atau verifikasi Midtrans.

## 1. Mengelola diskon dari dashboard

Form produk berada di menu pengelolaan katalog admin. Route-nya menggunakan resource `admin.products.*`.

1. Buat produk atau buka produk yang akan diubah.
2. Isi harga normal sebagai angka Rupiah bulat.
3. Pilih jenis diskon:
   - Persentase: bilangan bulat 1 sampai 99.
   - Nominal tetap: bilangan bulat positif dan harus lebih kecil dari harga normal.
4. Isi tanggal mulai dan akhir. Tanggal akhir tidak boleh sebelum tanggal mulai. Kedua tanggal termasuk periode promo.
5. Centang `Aktifkan diskon dalam periode yang dipilih`.
6. Simpan produk.

Status produk dan status diskon adalah dua hal berbeda:

- `is_active` menentukan apakah produk dapat tampil dan dipesan.
- `discount_is_active` menentukan apakah konfigurasi diskonnya boleh digunakan.

Produk harus aktif agar pengunjung dapat melihat detail dan checkout. Diskon hanya berlaku ketika produk aktif, toggle diskon aktif, konfigurasi valid, dan tanggal hari ini berada dalam periode promo.

### Menonaktifkan atau menghapus konfigurasi

- Untuk menghentikan promo tetapi menyimpan pengaturannya, hilangkan centang diskon lalu simpan.
- Untuk menghapus konfigurasi diskon, kosongkan jenis, nilai, dan tanggal, pastikan toggle mati, lalu simpan.
- Jika hanya mengubah harga, periksa juga nilai diskon tetap. Server menolak nominal diskon yang sama dengan atau lebih besar dari harga normal baru.

Harga katalog kembali normal otomatis setelah tanggal promo lewat. Tidak diperlukan perubahan data atau perintah terjadwal untuk mengakhiri promo.

### Membuat dan mengelola kode redeem

Admin membuka menu **Kode redeem**, lalu memasukkan kode dan memilih diskon persentase atau nominal. Kode dinormalisasi menjadi huruf kapital. Kode berlaku untuk tiket umum dan paket sekolah. Admin dapat menonaktifkan kode kapan saja; kode yang sudah berhasil dipakai tidak dapat diaktifkan kembali. Kode yang sudah direservasi atau dipakai tidak dapat dihapus.

Pelanggan memasukkan kode pada formulir tiket umum. Untuk paket sekolah, pelanggan mengisinya saat checkout setelah admin menyetujui pengajuan. Jika kode dimasukkan, potongan dihitung dari harga normal dan menggantikan diskon produk. Potongan tetap dikenakan per tiket untuk tiket umum dan satu kali untuk satu paket sekolah.

Satu kode hanya dapat berhasil dipakai satu kali total. Saat payment dibuat, kode dikunci dan direservasi ke payment tersebut. Pending payment menahan kode. Settlement menghabiskan kode; status `expire`, `cancel`, atau `deny` melepaskan kode. Jika notifikasi tidak tiba, pelanggan dapat memakai tindakan periksa status pembayaran untuk meminta server menyinkronkan status dari Midtrans.

Endpoint checkout yang menerima kode dibatasi 10 permintaan per menit per akun dan alamat IP untuk mengurangi percobaan menebak kode.

## 2. Aturan bisnis yang tidak boleh berubah tanpa pembaruan test

| Aturan | Dampak |
|---|---|
| Satu konfigurasi diskon produk per produk | Kode redeem disimpan terpisah dan menggantikan diskon produk, bukan ditumpuk |
| Persentase 1 sampai 99 | Nilai 0, 100, pecahan, dan di luar rentang ditolak |
| Diskon tetap lebih kecil dari harga normal | Harga hasil tidak boleh nol atau negatif |
| Harga akhir minimal Rp1 | Harga persentase diperiksa setelah pembulatan |
| Periode memakai tanggal inklusif | Promo berlaku pada tanggal mulai dan tanggal akhir |
| Zona waktu mengikuti aplikasi | Nilai default project adalah `Asia/Jakarta`; cek `APP_TIMEZONE` pada environment |
| Potongan tetap untuk tiket umum per unit | Jumlah potongan dikalikan jumlah tiket |
| Potongan tetap paket sekolah sekali per pengajuan | Jumlah peserta tidak mengalikan harga paket |
| Checkout sekolah menghitung promo setelah persetujuan | Harga pengajuan awal bukan jaminan harga checkout |
| Payment memiliki snapshot sendiri | Perubahan produk tidak boleh mengubah rincian payment lama |
| Midtrans menerima `gross_amount` backend | Jangan membaca harga dari browser sebagai sumber transaksi |
| Kode redeem hanya sekali total | Reservasi mencegah payment pending lain mengambil kode yang sama |
| Kode tidak ditumpuk dengan diskon produk | Kode menggantikan promo produk dan dihitung dari harga normal |

## 3. Jika mengubah aturan atau kode

### Mengubah validasi admin

Edit [`ProductController.php`](../../app/Http/Controllers/Admin/ProductController.php), khususnya `validatedData()`. Periksa aturan harga dan tanggal di satu tempat tersebut. Bila aturan baru mengubah jenis atau rentang nilai, selaraskan pesan dan atribut formulir di [`form.blade.php`](../../resources/views/admin/products/form.blade.php).

Pastikan input yang diberikan admin selalu divalidasi di server. Jangan mengandalkan atribut HTML seperti `min`, `max`, atau `required` sebagai perlindungan.

### Mengubah rumus harga

Edit [`TicketPricingService.php`](../../app/Services/TicketPricingService.php). Pertahankan service ini sebagai sumber hitung bersama untuk katalog dan checkout. Hindari menambahkan rumus kedua pada Blade atau controller.

Validasi ketersediaan dan perubahan status kode berada pada [`PromoCodeService.php`](../../app/Services/PromoCodeService.php). Jangan menghapus `lockForUpdate()` atau memindahkan reserve/redeem/release ke luar transaksi checkout/webhook: row lock dan status payment mencegah satu kode dipakai pada beberapa payment.

Setelah perubahan, periksa:

1. Harga unit normal dan harga unit setelah diskon.
2. Subtotal, potongan, dan total akhir untuk jumlah beberapa tiket.
3. Batas harga akhir minimal Rp1.
4. Periode promo sebelum, pada, dan setelah batas tanggal.
5. Kasus paket sekolah yang hanya menghitung satu unit.
6. Konsistensi angka pada katalog, payment, dan request Midtrans.

### Mengubah struktur tabel

Struktur diskon berada di tabel `tickets`; snapshot berada di tabel `payments`. Jika kolom perlu diubah:

1. Periksa migration dan schema yang sudah ada agar tidak membuat kolom duplikat.
2. Buat migration baru yang maju. Jangan mengedit migration yang sudah diterapkan pada environment bersama.
3. Tentukan nilai untuk baris lama, nullability, default, dan cara rollback sebelum menjalankan migration.
4. Tambahkan atau ubah model casts dan field yang disimpan.
5. Uji migration pada database test dan periksa status migration pada environment tujuan.
6. Backup database sebelum migration di staging atau production.

Migration diskon awal mengisi `subtotal_amount` payment lama dari `gross_amount`. `subtotal_amount` masih nullable pada schema. Jangan menganggap semua baris historis yang dibuat sebelum migration memiliki rincian promo.

### Menjaga snapshot transaksi

`CheckoutService::createPayment()` menyimpan snapshot dan menggunakan `gross_amount` yang sama untuk request Midtrans. Jangan mengganti tampilan riwayat agar menghitung ulang total dari harga `Ticket` terkini. Jangan menimpa snapshot payment lama ketika admin mengubah harga produk.

Webhook tetap harus mencocokkan order ID dan nominal Midtrans dengan payment lokal. Jangan mengubah status pembayaran berdasarkan redirect browser atau data nominal yang dikirim pelanggan.

## 4. Pengujian sebelum merge atau deploy

Mulai dengan test yang terkait:

```powershell
php artisan test --compact tests\Feature\CatalogAndBookingTest.php
```

Setelah perubahan menyentuh alur bersama, jalankan suite lengkap:

```powershell
php artisan test --compact
```

Test fitur katalog diskon mencakup CRUD admin, nilai dan periode yang ditolak, tampilan promo aktif, harga checkout dari backend, snapshot payment, dan checkout ulang paket sekolah setelah payment kedaluwarsa. Tambahkan test yang gagal sebelum perubahan untuk setiap aturan baru.
Test redeem juga harus mencakup izin admin, duplikasi kode, normalisasi huruf, penggantian diskon produk, pemakaian tiket umum dan sekolah, reservasi pending, settlement, serta pelepasan setelah expire.

Jika mengubah asset atau Blade yang membutuhkan bundle, jalankan:

```powershell
npm run build
```

Untuk PHP yang berubah, format dengan:

```powershell
vendor\bin\pint --dirty --format agent
```

## 5. Migration dan deployment

Sebelum menerapkan migration:

```powershell
php artisan migrate:status
php artisan migrate --pretend --no-interaction
```

Periksa output SQL dan pastikan target database benar. Pada production, backup database dan gunakan proses deployment yang disetujui. Perintah migration production Laravel umumnya menggunakan `--force`:

```powershell
php artisan migrate --force --no-interaction
```

Jangan gunakan `migrate:fresh` pada database yang berisi data. Jangan rollback migration diskon jika kolom snapshot sudah berisi transaksi, kecuali sudah ada rencana penyimpanan dan pemulihan data. Rollback akan menghapus kolom snapshot dan konfigurasi diskon.

Migration kode redeem membuat tabel baru dan menambah kolom `promo_code_id` serta `promo_code` pada `payments`. Jalankan migration ini pada environment yang sama dengan aplikasi. Jangan deploy kode yang mengakses kolom tersebut sebelum migration tersedia.

Setelah deploy:

1. Jalankan `php artisan migrate:status` dan pastikan migration yang diharapkan berstatus `Ran`.
2. Buka produk di admin dan pastikan konfigurasi masih terlihat.
3. Periksa katalog dan detail produk aktif.
4. Uji checkout pada Sandbox dengan nominal dari produk uji.
5. Pastikan webhook hanya mengonfirmasi payment setelah verifikasi order, nominal, signature, dan status Midtrans.

## 6. Pemecahan masalah

| Gejala | Yang perlu diperiksa |
|---|---|
| Harga normal tampil padahal diskon disetel | Status produk, toggle diskon, tanggal lokal, nilai, jenis, dan kedua tanggal |
| Produk tidak terlihat di katalog | `is_active`, nilai `type` (`general` atau `school`), dan route/katalog |
| Admin tidak bisa menyimpan harga baru | Apakah diskon tetap yang tersimpan sekarang sudah sama dengan atau lebih besar dari harga baru |
| Form menolak diskon | Lihat pesan validasi, kelengkapan tanggal/jenis/nilai, rentang tanggal, harga minimal Rp1, dan batas nilai |
| Total checkout berbeda dari yang dikirim browser | Checkout menghitung ulang dari produk database; nilai browser memang tidak menjadi sumber harga |
| Riwayat tidak berubah setelah harga produk diedit | Perilaku yang diharapkan, riwayat memakai snapshot payment |
| Paket sekolah tetap menunjukkan payment lama | Checkout memakai payment pending yang ada bila masih memiliki Snap URL; periksa status payment sebelum membuat percobaan baru |
| Webhook tidak mengonfirmasi transaksi | Periksa signature, order ID, `gross_amount`, status yang dikonfirmasi Midtrans, dan log aplikasi tanpa membagikan secret |
| Migration belum berjalan | Periksa koneksi database aktif, `php artisan migrate:status`, dan output migration. Jangan menjalankan migration pada database yang salah |

Jangan menaruh Server Key, Signature Key, Snap token, atau data sensitif pelanggan di dokumentasi, screenshot, atau log yang dibagikan.

## 7. File utama

- [`Ticket.php`](../../app/Models/Ticket.php): atribut produk dan casts diskon.
- [`ProductController.php`](../../app/Http/Controllers/Admin/ProductController.php): validasi, simpan, ubah, dan hapus produk.
- [`form.blade.php`](../../resources/views/admin/products/form.blade.php): input admin.
- [`TicketPricingService.php`](../../app/Services/TicketPricingService.php): aturan hitung harga.
- [`CatalogController.php`](../../app/Http/Controllers/CatalogController.php): data harga katalog dan detail.
- [`CheckoutService.php`](../../app/Services/CheckoutService.php): checkout, snapshot, dan verifikasi status payment.
- [`Payment.php`](../../app/Models/Payment.php): relasi dan casts snapshot.
- [`PromoCode.php`](../../app/Models/PromoCode.php): data kode dan hubungan payment/pelanggan.
- [`PromoCodeController.php`](../../app/Http/Controllers/Admin/PromoCodeController.php): pembuatan kode, pengaturan status, dan penghapusan kode yang belum dipakai.
- [`PromoCodeService.php`](../../app/Services/PromoCodeService.php): penguncian, reservasi, konsumsi, dan pelepasan kode.
- [`promo-codes/index.blade.php`](../../resources/views/admin/promo-codes/index.blade.php): dashboard kode redeem.
- [`migration diskon`](../../database/migrations/2026_11_24_142352_add_discount_pricing_snapshots_to_tickets_and_payments.php): perubahan schema.
- [`migration kode redeem`](../../database/migrations/2026_11_25_115406_create_promo_codes_table.php): tabel kode dan snapshot kode di payment.
- [`test katalog dan booking`](../../tests/Feature/CatalogAndBookingTest.php): test fitur diskon dan checkout.
- [`DATABASE.md`](../DATABASE.md): daftar tabel dan aturan data.
- [`TESTING.md`](../TESTING.md): matriks pengujian project.
