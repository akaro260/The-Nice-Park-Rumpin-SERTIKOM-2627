# Product Requirements Document
## Sertikom – The Nice Park Rumpin

| Atribut | Nilai |
|---|---|
| Program | Sertikom Produktif RPL XI |
| Tahun ajaran | 2026/2027 |
| Client | Wisata The Nice Park Rumpin |
| Produk | Aplikasi web pemesanan tiket dan paket kunjungan |
| Metode | Agile Scrum, 5 sprint |
| Framework | Laravel 13, PHP 8.4 |
| Pembayaran | Midtrans Snap Sandbox |

## 1. Latar Belakang

The Nice Park Rumpin melayani pengunjung umum dan rombongan sekolah. Informasi harga, ketersediaan tiket, pengajuan paket, dan pembayaran masih membutuhkan komunikasi dengan petugas. Proyek ini merencanakan aplikasi web yang menampilkan katalog, mencatat pemesanan, mengelola persetujuan rombongan, memproses pembayaran online, dan menyediakan e-ticket setelah pembayaran terverifikasi.

Dokumen ini menjelaskan kebutuhan produk, bukan menyatakan semua fitur sudah tersedia. Baseline saat dokumentasi disusun masih berupa kerangka Laravel dengan autentikasi Breeze, halaman welcome/dashboard, serta profil pengguna. Katalog, pemesanan, admin, e-ticket, dan webhook masih menjadi pekerjaan backlog.

PRD ini menjadi sumber utama kebutuhan produk. Backlog, rencana sprint, desain, testing, dan dokumentasi teknis harus diturunkan dari PRD. DATABASE.md mencatat schema yang benar-benar ada secara terpisah dari perubahan yang dibutuhkan untuk memenuhi PRD.

## 2. Masalah Pengguna

- Informasi tiket dan paket belum tersaji dalam satu katalog.
- Pelanggan perlu menghubungi petugas untuk mengetahui harga, kuota, dan prosedur pembayaran.
- Pengajuan rombongan sekolah dicatat secara manual.
- Petugas perlu memeriksa pembayaran sebelum memastikan pesanan.
- Pelanggan membutuhkan tempat untuk memantau status pesanan dan bukti tiket.

## 3. Tujuan dan Ukuran Keberhasilan

| Tujuan | Kriteria |
|---|---|
| Informasi terpusat | Pengunjung dapat melihat katalog aktif, detail, harga, dan ketentuan |
| Pemesanan mandiri | Pelanggan login dapat memesan tiket umum dan melihat status |
| Dukungan rombongan sekolah | Pelanggan dapat mengajukan paket; admin menyetujui atau menolak dengan alasan |
| Pembayaran online | Backend membuat transaksi melalui Midtrans Snap Sandbox |
| Verifikasi transaksi | Webhook server memvalidasi notifikasi dan pemrosesan bersifat idempoten |
| Bukti kunjungan | E-ticket hanya tersedia setelah pembayaran terverifikasi berhasil |
| Dukungan operasional | Admin mengelola produk dan memantau pesanan serta pembayaran |

Kriteria tersebut harus dibuktikan dengan pengujian, bukan diasumsikan dari dokumentasi.

## 4. Pengguna dan Hak Akses

| Peran | Kebutuhan |
|---|---|
| Pengunjung | Melihat katalog publik dan detail produk |
| Pelanggan (customer) | Mengelola akun, memesan, mengajukan paket sekolah, membayar, melihat pesanan dan e-ticket sendiri |
| Admin (admin) | Mengelola tiket/paket, meninjau pengajuan sekolah, memantau pesanan dan pembayaran |

Login diwajibkan untuk membuat pesanan dan membuka data privat. Setiap akses pelanggan harus memeriksa kepemilikan di server. Halaman administrasi harus memeriksa role.

## 5. Ruang Lingkup

### Termasuk

- Registrasi, login, logout, dan pembatasan akses berdasarkan role.
- Katalog tiket umum dan paket sekolah, detail, dan ketentuan.
- Pencarian dan filter katalog.
- Pemesanan tiket umum dengan tanggal kunjungan dan jumlah.
- Pengajuan paket sekolah, pemantauan status, persetujuan atau penolakan admin, dan alasan penolakan.
- Perhitungan harga di server dan pemeriksaan kuota.
- Pembayaran Midtrans Snap Sandbox.
- Penerimaan dan verifikasi webhook server-side.
- Riwayat pesanan, status pembayaran, dan e-ticket.
- CRUD tiket umum dan paket sekolah oleh admin.
- Pengujian validasi, otorisasi, alur transaksi, dan tampilan responsif.

### Di luar cakupan

- Pemrosesan pembayaran Midtrans Production.
- Aplikasi native Android atau iOS.
- Notifikasi WhatsApp otomatis.
- Membership, loyalitas, atau poin.
- Integrasi marketplace wisata pihak ketiga.
- Penjadwalan berbasis slot jika kebijakan operasional belum ditetapkan.

## 6. Alur Utama

### Pemesanan tiket umum

1. Pengunjung memilih produk umum yang aktif.
2. Pelanggan login, menentukan tanggal kunjungan dan jumlah tiket.
3. Server memvalidasi produk, kuota, tanggal, dan jumlah.
4. Server membaca harga dari database, menghitung total, lalu membuat registration.
5. Server membuat order ID unik dan meminta Snap Token ke Midtrans.
6. Pelanggan menyelesaikan pembayaran melalui Snap.
7. Backend menerima webhook, memvalidasi autentisitas, order, nominal, dan status.
8. Setelah berhasil diverifikasi, sistem mengonfirmasi registration dan membuat e-ticket.

### Pengajuan paket sekolah

1. Pelanggan memilih paket sekolah aktif dan mengirim tanggal kunjungan, nama sekolah, penanggung jawab, dan jumlah peserta.
2. Admin meninjau pengajuan.
3. Admin menyetujui atau menolak; penolakan menyertakan alasan.
4. Pengajuan yang disetujui dapat dibayar. Pengajuan yang ditolak tidak dapat dibayar.
5. E-ticket terbit setelah pembayaran berhasil diverifikasi server.

## 7. Aturan Bisnis

1. Pelanggan harus login sebelum membuat atau melihat pesanan privat.
2. Harga, total, role, dan status transaksi ditentukan atau diverifikasi server.
3. Produk harus aktif dan kuota mencukupi sebelum pesanan diterima.
4. Pembaruan kuota harus mencegah penjualan melebihi persediaan, termasuk pada pemesanan bersamaan.
5. Pengajuan sekolah harus disetujui admin sebelum pembayaran dibuat.
6. Pengajuan yang ditolak tidak dapat dibayar.
7. Setiap order ID Midtrans unik dan hanya mewakili satu transaksi.
8. Kegagalan meminta Snap Token tidak boleh meninggalkan pesanan yang dapat dibayar tanpa token yang sah.
9. Pelanggan dapat melanjutkan pembayaran yang masih berlaku; setiap percobaan pembayaran baru memakai order ID unik.
10. Redirect browser atau callback JavaScript bukan bukti pembayaran. Backend hanya mengonfirmasi setelah verifikasi notifikasi atau status melalui server.
11. Webhook berulang tidak boleh mengurangi kuota atau menerbitkan e-ticket berulang.
12. Status settlement adalah berhasil. Status capture hanya dianggap berhasil bila fraud_status bernilai accept. Status lain tidak menerbitkan e-ticket.
13. Pelanggan hanya dapat membaca pesanan, pembayaran, dan e-ticket miliknya.
14. Produk yang telah dirujuk registration tidak dihapus secara fisik; nonaktifkan atau ikuti aturan foreign key.
15. Status registration dan payment adalah dua hal terpisah.

## 8. Kebutuhan Fungsional

**Pelanggan:** mendaftar/login/logout; melihat, mencari, dan memfilter katalog; melihat harga dan ketentuan; memesan tiket umum atau mengajukan paket sekolah; memantau status; membayar atau melanjutkan pembayaran yang masih berlaku; melihat riwayat, detail, dan e-ticket.

**Admin:** mengelola produk dan kuota; meninjau pengajuan sekolah; menyetujui atau menolak dengan alasan; memantau pembayaran dan pesanan; memfilter status.

**Sistem:** memvalidasi input; menghitung nominal dari database; menjaga kuota dan konsistensi transaksi; memverifikasi webhook secara idempoten; menerapkan role dan ownership; menghindari query N+1; menyediakan seeder development untuk akun dan data uji tanpa credential produksi.

## 9. Kebutuhan Nonfungsional

- Antarmuka responsif untuk desktop, tablet, dan ponsel.
- Pesan validasi menjelaskan perbaikan yang perlu dilakukan.
- Status tidak hanya dibedakan berdasarkan warna.
- Kredensial disimpan di environment dan tidak dikirim ke browser.
- Webhook production menggunakan HTTPS.
- Operasi pembayaran dapat ditelusuri lewat order ID tanpa mencatat rahasia.
- Database memakai decimal untuk uang dan foreign key untuk integritas relasi.

## 10. Dependensi dan Risiko

- Client perlu menetapkan kebijakan harga, kuota, tanggal kunjungan, pembatalan, dan refund.
- Katalog sekolah membutuhkan klasifikasi jenis produk dan field rombongan.
- Migration saat ini belum menyimpan semua data untuk tanggal kunjungan, alur sekolah, dan e-ticket; rincian ada di DATABASE.md.
- Integrasi Sandbox memerlukan Server Key, Client Key, dan Signature Secret Key. Kunci berasal dari dashboard Midtrans dan tidak boleh dicantumkan di dokumentasi atau repository.
- Webhook lokal memerlukan URL HTTPS yang dapat dijangkau Midtrans.

## 11. Kriteria Penerimaan Produk

Pengajuan penerimaan dilakukan setelah PBI prioritas tinggi diuji; pelanggan tidak dapat mengakses data pengguna lain; harga dihitung server-side; paket sekolah mengikuti persetujuan; webhook valid dan aman terhadap pengiriman ulang; status cocok dengan hasil Midtrans; e-ticket tidak terbit untuk transaksi pending/gagal; dan hasil uji dicatat pada TESTING.md.

