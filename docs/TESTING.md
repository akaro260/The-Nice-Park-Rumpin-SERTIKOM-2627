# Dokumentasi Pengujian
## Sertikom – The Nice Park Rumpin

## 1. Tujuan

Pengujian membuktikan alur autentikasi, role, kepemilikan data, kuota, harga server-side, persetujuan sekolah, webhook Midtrans, dan e-ticket. Jangan menandai skenario lulus tanpa eksekusi dan bukti hasil.

## 2. Jenis Pengujian

| Jenis | Cakupan |
|---|---|
| Unit | Perhitungan total, pemetaan status, logika kecil |
| Feature | HTTP, autentikasi, validasi, akses admin/customer, pemesanan |
| Database | Migration, foreign key, unique key, kuota, integritas relasi |
| Integrasi | Snap Sandbox, signature, payload notifikasi, status transaksi |
| Responsif dan aksesibilitas | Breakpoint, keyboard, fokus, label, kontras, status tekstual |

Project menggunakan Pest. Jalankan pengujian hanya pada environment lokal/test dan jangan gunakan kredensial Production.

## 3. Matriks Skenario

| ID | Area | Skenario | Hasil yang diharapkan |
|---|---|---|---|
| TC-01 | Auth | Registrasi valid | Akun dibuat dengan role pelanggan yang benar |
| TC-02 | Auth | Email sudah digunakan | Validasi menolak |
| TC-03 | Auth | Login valid | Sesi terbentuk |
| TC-04 | Auth | Password salah | Akses ditolak |
| TC-05 | Auth | Logout | Sesi berakhir |
| TC-06 | Akses | Customer membuka URL admin | Ditolak oleh server |
| TC-07 | Akses | Admin membuka halaman admin | Diizinkan |
| TC-08 | Akses | Customer membuka registration/payment user lain | Ditolak |
| TC-09 | Katalog | Pengunjung membuka katalog | Produk aktif tampil |
| TC-10 | Katalog | Membuka detail produk | Harga dan ketentuan sesuai data |
| TC-11 | Booking | Membuat pesanan valid | Registration terhubung ke user dan ticket |
| TC-12 | Booking | Quantity nol, negatif, atau bukan integer | Validasi menolak |
| TC-13 | Harga | Browser mengubah harga/total | Server tetap menghitung dari database |
| TC-14 | Booking | Memesan produk nonaktif | Ditolak |
| TC-15 | Kuota | Kuota tidak mencukupi | Ditolak tanpa membuat kuota negatif |
| TC-16 | Kuota | Dua permintaan merebut kuota terakhir bersamaan | Maksimal satu yang berhasil |
| TC-17 | Katalog | Menggunakan filter | Hasil sesuai filter dan produk aktif |
| TC-18 | Sekolah | Pengajuan dengan data lengkap | Data rombongan tersimpan |
| TC-19 | Sekolah | Data wajib atau jumlah peserta tidak valid | Validasi menolak |
| TC-20 | Sekolah | Admin menyetujui | Status registration berubah sesuai alur |
| TC-21 | Sekolah | Admin menolak tanpa alasan | Ditolak bila alasan diwajibkan |
| TC-22 | Sekolah | Pengajuan ditolak mencoba membayar | Transaksi tidak dibuat |
| TC-23 | Sekolah | Pengajuan belum disetujui mencoba membayar | Transaksi tidak dibuat |
| TC-24 | Akses | Customer mencoba menyetujui pengajuan sendiri | Ditolak |
| TC-25 | Midtrans | Membuat transaksi Snap Sandbox | Token/URL tersimpan pada order lokal |
| TC-26 | Webhook | Signature tidak valid | Status tidak berubah |
| TC-27 | Webhook | Order ID tidak dikenal | Ditolak/dicatat tanpa membuat payment |
| TC-28 | Webhook | Nominal berbeda dari record lokal | Tidak dianggap berhasil |
| TC-29 | Webhook | Status pending | Registration belum confirmed dan e-ticket belum terbit |
| TC-30 | Webhook | Settlement terverifikasi | Payment sukses dan proses konfirmasi berjalan |
| TC-31 | Webhook | Capture dengan fraud accept | Berhasil jika pemeriksaan lain lolos |
| TC-32 | Webhook | Capture dengan fraud challenge/deny | Tidak sukses |
| TC-33 | Webhook | Expire, cancel, atau deny | Status sesuai; e-ticket tidak diterbitkan |
| TC-34 | Webhook | Notifikasi yang sama dikirim ulang | Tidak membuat payment/kuota/e-ticket ganda |
| TC-35 | Webhook | Status tidak jelas atau terlambat | Backend dapat meminta status terbaru ke Midtrans |
| TC-36 | E-ticket | Payment sukses terverifikasi | Kode unik dan waktu terbit tersimpan satu kali |
| TC-37 | E-ticket | Payment belum sukses | E-ticket tidak tersedia |
| TC-38 | Akses | Customer membuka e-ticket user lain | Ditolak |
| TC-39 | Admin | Menambah atau mengubah ticket | Data tervalidasi dan tersimpan |
| TC-40 | Admin | Menghapus ticket yang direferensikan | Foreign key membatasi; nonaktifkan sebagai alternatif |
| TC-41 | Admin | Filter registration/payment | Hasil sesuai status yang dipilih |
| TC-42 | UI | Form pada ponsel | Field dan aksi utama berada dalam viewport |
| TC-43 | Aksesibilitas | Navigasi keyboard | Fokus terlihat dan aksi utama dapat digunakan |
| TC-44 | UI | Status memakai warna | Label teks tetap menjelaskan status |
| TC-45 | Keamanan | Input berbahaya | Validasi/escaping mencegah eksekusi |
| TC-46 | Keamanan | Form web tanpa token CSRF | Ditolak sesuai perlindungan Laravel |
| TC-47 | Diskon admin | Admin menyimpan diskon persentase atau nominal yang valid | Konfigurasi tersimpan pada produk yang dipilih |
| TC-48 | Diskon admin | Persentase nol/100 atau lebih, nominal sama dengan/melebihi harga normal, atau hasil di bawah Rp1 | Validasi menolak konfigurasi |
| TC-49 | Diskon admin | Tanggal mulai melewati tanggal akhir | Validasi menolak rentang |
| TC-50 | Diskon | Periksa tanggal mulai dan akhir, diskon nonaktif, sebelum mulai, dan setelah akhir | Batas tanggal inklusif berlaku di `Asia/Jakarta`; harga normal dipakai saat promo nonaktif atau di luar rentang |
| TC-51 | Katalog | Produk dengan diskon aktif ditampilkan | Harga normal, harga setelah diskon, dan label promo sesuai hitungan backend |
| TC-52 | Harga umum | Pesan beberapa tiket dengan diskon nominal dan ubah nominal di browser | Potongan diterapkan per tiket dan total dihitung backend |
| TC-53 | Paket sekolah | Checkout setelah pengajuan disetujui saat promo aktif | Potongan nominal diterapkan sekali pada harga paket, bukan per peserta; tanggal pengajuan tidak mengunci promo |
| TC-54 | Snapshot | Ubah harga atau konfigurasi diskon setelah payment dibuat | Rincian payment dan riwayat transaksi tetap memakai snapshot |
| TC-55 | Midtrans | Payment memakai harga diskon dan webhook mengirim nominal berbeda | `gross_amount` berisi total akhir; nominal webhook yang tidak cocok tidak dianggap berhasil |
| TC-56 | Payment | Promo berakhir saat payment masih menunggu, lalu payment kedaluwarsa dan dicoba lagi | Payment lama mempertahankan snapshot sampai kedaluwarsa; percobaan baru menghitung harga terbaru |
| TC-57 | Diskon | Persentase menghasilkan nilai pecahan Rupiah | Harga per unit dibulatkan ke Rupiah terdekat dengan aturan half-up, konsisten pada katalog dan checkout |
| TC-58 | Redeem admin | Admin membuat kode, mengubah status aktif, dan mencoba duplikat atau input tidak valid | Kode dinormalisasi huruf kapital, unik, valid, dan hanya admin dapat mengelolanya |
| TC-59 | Redeem tiket umum | Pelanggan memasukkan kode saat memesan produk yang juga punya diskon produk | Kode menggantikan promo produk; snapshot payment menyimpan kode dan harga yang dihitung server |
| TC-60 | Redeem sekolah | Pelanggan memasukkan kode saat checkout setelah persetujuan admin | Potongan nominal berlaku sekali untuk satu pengajuan, bukan per peserta, dan menggantikan promo produk |
| TC-61 | Redeem payment | Kode direservasi ketika payment pending lalu payment expire | Kode dilepaskan dan dapat digunakan pada percobaan payment baru |
| TC-62 | Redeem validasi | Potongan kode nominal sama dengan atau lebih besar dari harga produk | Checkout menolak kode tanpa membuat registration atau payment |
| TC-63 | Redeem konfigurasi | Jenis diskon tersimpan tidak dikenal | Checkout menolak kode dan tidak membuat registration atau payment |

## 4. Pengujian Midtrans

- Gunakan akun, API key, dan endpoint Sandbox.
- Snap.js Sandbox dan API Sandbox harus dipakai bersama.
- Server Key dan Signature Secret Key tidak boleh masuk ke browser, log, screenshot, atau repository.
- Webhook lokal memerlukan endpoint HTTPS yang dapat dijangkau Midtrans, misalnya tunnel yang disetujui tim.
- Uji pembayaran sukses, pending, gagal, expired, fraud challenge, serta notifikasi berulang.
- Catat order ID dan status tanpa menyimpan secret.
- Redirect pelanggan hanya mengubah tampilan; webhook/status server menentukan hasil pembayaran.

Ikuti mekanisme signature yang berlaku untuk akun Midtrans. Per 28 Oktober 2026, integrasi handler sendiri memakai Signature Secret Key terpisah untuk memverifikasi signature notification; Server Key dipakai untuk API authorization. Rincian ada di TECHNICAL.md.

## 5. Pencatatan Hasil

Catat commit/build, environment (local/Sandbox), waktu, status, bukti non-rahasia, dan tindak lanjut. Status yang digunakan: Pass, Fail, Not Tested.

| Rentang ID | Status terakhir | Catatan |
|---|---|---|
| TC-01 sampai TC-46 | Not Tested | Belum diverifikasi satu per satu terhadap matriks ini; beberapa alur tercakup secara tidak langsung oleh suite. |
| TC-47 sampai TC-54 | Pass (otomatis) | Dicakup oleh `tests/Feature/CatalogAndBookingTest.php`: CRUD diskon, validasi, periode, katalog, checkout, dan snapshot. |
| TC-55 | Not Tested | Belum ada skenario khusus nominal webhook yang berbeda untuk payment diskon. |
| TC-56 | Pass (otomatis) | Skenario payment sekolah kedaluwarsa lalu checkout ulang dengan harga terbaru di `tests/Feature/CatalogAndBookingTest.php`. |
| TC-57 | Pass (otomatis) | Pembulatan persentase half-up diverifikasi di katalog dan nominal checkout pada `tests/Feature/CatalogAndBookingTest.php`. |
| TC-58 sampai TC-63 | Pass (otomatis) | Dicakup oleh `tests/Feature/CatalogAndBookingTest.php`: akses admin, normalisasi dan validasi termasuk konfigurasi rusak, penerapan untuk tiket umum/sekolah, reservasi, konsumsi setelah settlement, dan pelepasan setelah expire. |

**Bukti eksekusi:** `php artisan test --compact` lulus dengan 49 test dan 340 assertion; `npm run build` lulus. `php artisan migrate:status` dan `php artisan migrate --pretend --no-interaction` belum dapat dijalankan karena koneksi MySQL lokal pada `127.0.0.1:3306` ditolak. Migration kode redeem belum diterapkan atau diverifikasi oleh pengerjaan ini. Jangan anggap pengujian Midtrans Sandbox manual lulus dari mock feature test.

## 6. Kriteria Penerimaan

Rilis dapat diajukan setelah skenario prioritas tinggi berjalan, tidak ada bug kritis, role dan ownership terbukti, nominal berasal dari server, webhook valid dan idempoten, serta e-ticket hanya terbit pada pembayaran berhasil. Catat kegagalan dan hasil ulang sebelum menyatakan skenario lulus.

