# Rencana Sprint
## Sertikom – The Nice Park Rumpin

**Metode:** Agile Scrum  
**Jumlah sprint:** 7

Pemetaan ini menempatkan semua 31 PBI tepat satu kali. Perubahan setelah sprint review dicatat pada backlog.
**Status:** Sprint 6 dan Sprint 7 telah diimplementasikan. Migration kode redeem belum dapat diverifikasi pada MySQL lokal karena koneksi database tidak tersedia; lihat `docs/TESTING.md`.

## Sprint 1: Fondasi dan Autentikasi

**Tujuan:** Menyiapkan fondasi aplikasi, autentikasi, dan kontrol akses.

| PBI | Hasil |
|---|---|
| PB-01 | Registrasi dengan role pelanggan konsisten |
| PB-02 | Login, logout, dan sesi |
| PB-03 | Pembatasan akses admin |
| PB-04 | Pembatasan data berdasarkan pemilik |

**Deliverable:** Akun dan sesi berjalan, role admin/customer terdefinisi, akses admin dan ownership diuji. Selesaikan normalisasi default costumer sebelum memakai role untuk otorisasi.

## Sprint 2: Katalog dan Pengelolaan Produk

**Tujuan:** Menyediakan katalog publik dan pengelolaan tiket/paket.

| PBI | Hasil |
|---|---|
| PB-05 | Katalog tiket dan paket |
| PB-06 | Detail produk dan ketentuan |
| PB-07 | Pencarian dan filter |
| PB-20 | CRUD tiket umum |
| PB-21 | CRUD paket sekolah |
| PB-26 | Seeder data development |

**Deliverable:** Produk aktif dapat dikelola dan ditampilkan. Tambahkan tickets.type lewat migration aman sebelum memisahkan jenis produk.

## Sprint 3: Pemesanan dan Persetujuan

**Tujuan:** Mencatat pemesanan umum dan pengajuan rombongan sekolah.

| PBI | Hasil |
|---|---|
| PB-08 | Pemesanan tanggal dan jumlah |
| PB-09 | Perhitungan total server-side |
| PB-10 | Pengajuan sekolah |
| PB-11 | Pemantauan status |
| PB-13 | Persetujuan atau penolakan admin |
| PB-14 | Alasan penolakan |
| PB-24 | Pesan validasi |

**Deliverable:** Registration menyimpan tanggal, sekolah, kontak, peserta, dan keputusan admin. Tambahkan kolom yang belum ada sesuai DATABASE.md; putuskan aturan kuota dan tanggal kunjungan bersama client.

## Sprint 4: Pembayaran dan E-Ticket

**Tujuan:** Menghubungkan Snap Sandbox, status registration, dan bukti kunjungan.

| PBI | Hasil |
|---|---|
| PB-12 | Pembayaran hanya setelah persetujuan sekolah |
| PB-15 | Pembuatan transaksi Midtrans Snap |
| PB-16 | Verifikasi notifikasi server |
| PB-17 | Kelanjutan transaksi yang belum selesai |
| PB-18 | Penerbitan e-ticket |
| PB-19 | Riwayat pesanan pelanggan |
| PB-22 | Monitoring admin |
| PB-23 | Filter pesanan |

**Deliverable:** Snap token diterima server-side; webhook memeriksa signature, order, nominal, dan status; pemrosesan idempoten; e-ticket hanya terbit setelah payment berhasil. Webhook lokal membutuhkan URL HTTPS publik.

## Sprint 5: Optimasi dan Penerimaan

**Tujuan:** Memeriksa query, integrasi, regresi, dan kesiapan dokumentasi.

| PBI | Hasil |
|---|---|
| PB-25 | Eager loading relasi |
| PB-27 | Pengujian sistem |

**Deliverable:** Hasil pengujian fitur, validasi, role, ownership, kuota, transaksi, webhook duplikat, dan akses e-ticket dicatat. Bug ditriase dan hasil aktual ditulis di TESTING.md.

## Sprint 6: Manajemen Diskon

**Tujuan:** Mengelola promo produk, menghitung harga akhir di backend, dan menyimpan rincian harga per payment.

| PBI | Hasil |
|---|---|
| PB-28 | Konfigurasi satu diskon per produk |
| PB-29 | Tampilan harga promo dan perhitungan checkout |
| PB-30 | Snapshot harga pada setiap payment |

**Deliverable:** Harga normal dan promo tampil hanya selama periode aktif. Diskon nominal berlaku per tiket atau sekali per pengajuan sekolah. Checkout paket sekolah menghitung promo setelah persetujuan admin. Payment yang sudah dibuat mempertahankan nominal sampai kedaluwarsa; percobaan baru menghitung ulang. Midtrans tetap menerima `gross_amount` hasil hitung backend.

## Sprint 7: Kode Redeem

**Tujuan:** Membuat kode potongan di dashboard admin dan menerapkannya dengan aman pada checkout tiket umum maupun paket sekolah.

| PBI | Hasil |
|---|---|
| PB-31 | Pembuatan, pengaktifan, dan penonaktifan kode redeem sekali pakai |

**Deliverable:** Kode admin mendukung persentase atau nominal. Kode berlaku untuk kedua jenis produk dan menggantikan diskon produk, bukan ditumpuk. Checkout mencadangkan satu kode selama payment pending, menghabiskan kode setelah pembayaran berhasil, dan melepaskannya saat payment expire, cancel, atau deny. Payment menyimpan snapshot kode dan nominal yang dikirim ke Midtrans.

## Rekap Pemetaan

- Sprint 1: PB-01 sampai PB-04 (4).
- Sprint 2: PB-05, PB-06, PB-07, PB-20, PB-21, PB-26 (6).
- Sprint 3: PB-08, PB-09, PB-10, PB-11, PB-13, PB-14, PB-24 (7).
- Sprint 4: PB-12, PB-15, PB-16, PB-17, PB-18, PB-19, PB-22, PB-23 (8).
- Sprint 5: PB-25, PB-27 (2).
- Sprint 6: PB-28 sampai PB-30 (3).
- Sprint 7: PB-31 (1).

## Sprint Review

Pada akhir sprint, demonstrasikan hasil terhadap tujuan, jalankan pengujian terkait, catat hambatan dan keputusan, lalu susun ulang prioritas lewat backlog. PBI yang belum selesai tetap terbuka dengan alasan dan tindak lanjut.

