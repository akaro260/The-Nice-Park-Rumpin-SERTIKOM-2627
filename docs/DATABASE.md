# Database Katalog dan Pemesanan

## Status schema

Schema aplikasi memiliki sembilan migration. Status penerapan pada MySQL lokal belum dapat diverifikasi: koneksi ke `127.0.0.1:3306` ditolak saat menjalankan `migrate:status` dan `migrate --pretend`. Jalankan `php artisan migrate:status`, lalu `php artisan migrate --force --no-interaction` setelah layanan database tersedia. Test suite memakai database test dan tidak membuktikan migration sudah diterapkan ke MySQL lokal.

| Migration | Perubahan |
|---|---|
| 0001_01_01_000000_create_users_table | users, password_reset_tokens, sessions |
| 0001_01_01_000001_create_cache_table | cache dan cache_locks |
| 0001_01_01_000002_create_jobs_table | jobs, job_batches, failed_jobs |
| 2026_11_23_062047_create_tickets_table | Katalog produk |
| 2026_11_23_062048_create_registrations_table | Pengajuan dan pemesanan |
| 2026_11_23_062051_create_payments_table | Transaksi Midtrans |
| 2026_11_24_044952_add_catalog_and_booking_fields | Jenis produk, detail kunjungan, e-ticket, kuota harian |
| 2026_11_24_142352_add_discount_pricing_snapshots_to_tickets_and_payments | Konfigurasi diskon produk dan snapshot harga payment |
| 2026_11_25_115406_create_promo_codes_table | Kode redeem satu kali dan snapshot kode pada payment |

Migration katalog bersifat maju dan mempertahankan kolom lama. Produk lama yang `type`-nya masih `NULL` tidak muncul di katalog. Admin perlu menetapkan jenis produk sebelum mengaktifkannya.

## Tabel bisnis

### `users`

Akun memakai kolom Laravel standar serta `role`, dengan nilai default `customer`. Role tidak dapat diisi melalui mass assignment formulir pendaftaran. Untuk memberi akses admin, daftarkan akun terlebih dahulu lalu jalankan perintah CLI di server:

```text
php artisan app:make-admin email-terdaftar
```

Perintah gagal jika akun belum ada atau alamat email tidak valid. Jangan menyimpan kata sandi admin di source code atau seeder.

### `tickets`

`tickets` menyimpan tiket umum dan paket sekolah. `type` bernilai `general` atau `school`, tetapi nullable untuk menjaga produk lama. Harga tersimpan pada `price` DECIMAL(12,2). `quota` adalah kapasitas per tanggal. `available_quota` dipertahankan untuk kompatibilitas data lama dan tidak dipakai sebagai sisa kuota harian.

Katalog publik hanya mengambil produk aktif dengan jenis yang dikenali. Harga dan total checkout berasal dari record ini, bukan input browser.

### `ticket_date_quotas`

Setiap baris menyimpan kapasitas dan sisa kuota satu produk untuk satu `visit_date`. Kombinasi `ticket_id` dan `visit_date` unik. Baris dibuat saat checkout memesan kuota dan dilindungi transaksi serta row lock.

### `registrations`

Setiap pemesanan memiliki pemilik, produk, kode pendaftaran, tanggal, jumlah, total harga, status, dan waktu pendaftaran.

- Untuk tiket umum, `quantity` adalah jumlah tiket dan `total_price` merupakan harga per tiket dikalikan jumlah.
- Untuk paket sekolah, `quantity` adalah jumlah peserta dan `total_price` adalah harga tetap per pengajuan.
- `school_name`, `contact_person`, dan `rejection_reason` menyimpan data pengajuan sekolah.
- `e_ticket_code` dan `issued_at` diisi setelah backend memverifikasi pembayaran.
- `visit_date` nullable untuk menjaga riwayat pendaftaran lama.

### `payments`

Tabel `payments` menyimpan percobaan pembayaran Midtrans, termasuk `order_id`, nominal `gross_amount`, status provider, Snap token/URL, dan `paid_at`. Snapshot harga menyimpan subtotal, jenis/nilai diskon, jumlah potongan, serta `promo_code` bila kode redeem digunakan. `promo_code_id` adalah relasi nullable untuk administrasi; `promo_code` mempertahankan teks kode pada riwayat jika kode dihapus. `order_id` dan `transaction_id` memiliki unique index. Foreign key tetap mengikuti migration dasar: penghapusan user atau registration menghapus payment terkait, sedangkan penghapusan ticket yang memiliki registration dibatasi.

## Manajemen Diskon dan Snapshot Pembayaran

Tiket umum dan paket sekolah tetap memakai tabel `tickets`; `type` membedakan kedua jenis produk. Diskon produk memakai satu konfigurasi per produk. Kode redeem dikelola terpisah pada `promo_codes`.

Migration `2026_11_24_142352_add_discount_pricing_snapshots_to_tickets_and_payments` menambahkan kolom berikut ke `tickets`:

| Kolom | Tipe | Aturan |
|---|---|---|
| `discount_type` | string nullable | `percentage` atau `fixed` |
| `discount_value` | DECIMAL(12,2) nullable | Persentase atau nominal Rupiah per unit produk |
| `discount_starts_on` | date nullable | Tanggal mulai, inklusif |
| `discount_ends_on` | date nullable | Tanggal berakhir, inklusif |
| `discount_is_active` | boolean, default false | Toggle diskon terpisah dari status aktif produk |

Semua kolom konfigurasi selain toggle dibiarkan null jika produk belum memiliki diskon. Diskon hanya berlaku jika produk dan diskonnya aktif, konfigurasi valid, serta tanggal lokal saat harga ditampilkan atau payment dibuat berada dalam rentang mulai dan akhir. Tanggal memakai zona waktu aplikasi, `Asia/Jakarta`. Untuk paket sekolah, tanggal checkout setelah persetujuan admin menentukan kelayakan diskon. Harga produk yang diubah admin harus tetap memenuhi aturan diskon nominal yang tersimpan.

Validasi menerima persentase bilangan bulat 1 sampai 99 dan nominal tetap bilangan bulat positif yang lebih kecil dari harga normal. Rentang tanggal terbalik dan hasil harga akhir di bawah Rp1 ditolak. Harga persentase per unit dibulatkan ke Rupiah terdekat dengan aturan half-up. Backend memakai aturan yang sama untuk tampilan dan checkout.

Migration yang sama menambahkan snapshot ke `payments`, karena setiap baris payment mempunyai `order_id` sendiri dan dapat mewakili percobaan pembayaran yang berbeda:

| Kolom | Tipe | Aturan |
|---|---|---|
| `subtotal_amount` | DECIMAL(12,2), nullable | Total harga normal sebelum diskon |
| `discount_type` | string nullable | Jenis diskon saat payment dibuat |
| `discount_value` | DECIMAL(12,2) nullable | Nilai konfigurasi saat payment dibuat |
| `discount_amount` | DECIMAL(12,2), default 0 | Total potongan untuk payment |
| `gross_amount` | DECIMAL(12,2), sudah ada | Total akhir yang dikirim ke Midtrans |

Untuk pemesanan umum, subtotal adalah harga normal per tiket dikalikan jumlah, dan potongan nominal berlaku per tiket. Untuk paket sekolah, subtotal adalah harga paket tetap per pengajuan dan potongan nominal berlaku sekali per pengajuan, bukan per peserta. Backend mengisi snapshot setiap payment baru; jenis dan nilai diskon null serta potongan nol jika tidak ada diskon. Migration telah mengisi payment lama dengan `subtotal_amount = gross_amount`, `discount_amount = 0`, serta jenis dan nilai diskon null karena fitur diskon belum tersedia saat transaksi tersebut dibuat.

Diskon paket sekolah dihitung saat checkout setelah admin menyetujui pengajuan. Payment yang sudah dibuat mempertahankan snapshot dan nominalnya sampai kedaluwarsa. Jika pelanggan membuat percobaan payment baru setelah payment sebelumnya kedaluwarsa, backend menghitung ulang harga dan membuat snapshot baru. Riwayat payment memakai snapshot payment, bukan harga produk saat ini atau `registrations.total_price`, yang dapat diperbarui pada alur sekolah.

Relasi `Ticket` ke `Registration` dan `Registration` ke `Payment` tidak berubah. Tidak diperlukan tabel produk sekolah atau relasi model diskon baru.

## Kode Redeem

Migration `2026_11_25_115406_create_promo_codes_table` membuat tabel `promo_codes` dan menambahkan `promo_code_id` serta `promo_code` ke `payments`.

| Kolom `promo_codes` | Aturan |
|---|---|
| `code` | Wajib unik, dinormalisasi menjadi huruf kapital, maksimum 32 karakter; huruf, angka, dan tanda hubung |
| `discount_type` | `percentage` atau `fixed` |
| `discount_value` | Integer positif; persentase 1 sampai 99 |
| `is_active` | Admin dapat mengaktifkan atau menonaktifkan kode |
| `reserved_payment_id` | Payment pending yang sedang memegang kode |
| `redeemed_payment_id`, `redeemed_by_user_id`, `redeemed_at` | Payment, pelanggan, dan waktu pemakaian setelah pembayaran berhasil |

Setiap kode hanya dapat berhasil dipakai satu kali total. Checkout mengunci baris kode dalam transaksi dan mengikat kode ke payment yang dibuat. Ini mencegah dua checkout memakai kode yang sama secara bersamaan. Payment pending mempertahankan kodenya; status `expire`, `cancel`, atau `deny` melepaskan reservasi. Pembayaran berhasil menandai kode sudah dipakai. Jika notifikasi tidak tiba, pemeriksaan status payment dapat menyelaraskan hasil dan melepaskan kode setelah Midtrans mengonfirmasi status akhir.

Kode berlaku untuk tiket umum dan paket sekolah. Kode yang diberikan menggantikan diskon produk dan dihitung dari harga normal, bukan ditambahkan ke diskon produk. Potongan tetap berlaku per tiket untuk tiket umum dan sekali per pengajuan paket sekolah. Paket sekolah baru menerima kode saat checkout setelah admin menyetujui pengajuan. Jika potongan kode tidak mengurangi harga produk atau membuat harga akhir di bawah Rp1, checkout ditolak.

Riwayat payment menyimpan snapshot `promo_code`, `promo_code_id`, jenis/nilai diskon, subtotal, potongan, dan total akhir. Penghapusan kode hanya diizinkan sebelum reservasi atau pemakaian; nonaktifkan kode yang sudah dipakai agar riwayat audit tetap utuh.

## Status, kuota, dan pembayaran

1. Tiket umum aktif dapat dipesan melalui halaman detail. Backend menghitung total dari harga katalog dan jumlah yang divalidasi.
2. Pengajuan sekolah aktif menyimpan nama sekolah, PIC, tanggal, jumlah peserta, dan harga paket saat diajukan. Pengajuan belum memesan kuota.
3. Admin dapat menyetujui atau menolak pengajuan. Checkout setelah persetujuan menghitung ulang harga paket dari database dan mencoba memesan kuota tanggal yang dipilih. Jika kuota sudah tidak cukup, checkout ditolak.
4. Checkout Midtrans menerima nilai nominal yang dihitung backend. Redirect browser tidak menandai pembayaran berhasil.
5. Backend memeriksa signature notifikasi dengan `MIDTRANS_SIGNATURE_KEY`, lalu meminta status transaksi ke Midtrans sebelum mengubah status atau menerbitkan e-ticket. Status `settlement` diterima, sedangkan `capture` hanya diterima jika `fraud_status` adalah `accept`.
6. Notifikasi `expire`, `cancel`, atau `deny` dapat melepas kuota yang sedang ditahan. Belum ada scheduler terpisah untuk melepas kuota jika notifikasi tidak pernah diterima.

Tanggal kunjungan memakai zona waktu aplikasi `Asia/Jakarta` secara default. `APP_TIMEZONE` dapat mengganti nilai tersebut di environment.

## Catatan operasional

- Isi Midtrans Server Key dan Signature Key pada environment, bukan di repository. Konfigurasi `MIDTRANS_IS_PRODUCTION` harus sesuai dengan key yang dipakai.
- Terapkan migration pada environment tujuan setelah memeriksa status dan membuat backup database.
- Jangan menjalankan `migrate:fresh` pada database yang berisi data.
- Jangan rollback migration katalog setelah kuota atau e-ticket digunakan tanpa rencana rekonsiliasi data.
- Checkout saat ini memanggil Snap saat transaksi database masih terbuka. Kegagalan pembuatan pembayaran membatalkan perubahan kuota dan pendaftaran, tetapi panggilan jaringan dapat memperpanjang row lock.
