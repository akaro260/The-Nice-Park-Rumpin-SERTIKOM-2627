# Product Backlog
## Sertikom – The Nice Park Rumpin

**Metode:** Agile Scrum  
**Jumlah:** 31 Product Backlog Items
**Prioritas:** High untuk alur utama, Medium untuk fitur pendukung, Low untuk fitur yang dapat dijadwalkan setelah alur inti.
**Status:** PB-28 sampai PB-31 telah diimplementasikan; hasil uji otomatis dicatat pada TESTING.md.

## Daftar Product Backlog Items

| ID | Epic | User story | Prioritas | Kriteria penerimaan |
|---|---|---|---|---|
| PB-01 | Authentication | Sebagai pengunjung, saya ingin mendaftar agar dapat memesan. | High | Akun valid dibuat; email duplikat ditolak; role pelanggan konsisten. |
| PB-02 | Authentication | Sebagai pengguna, saya ingin login dan logout dengan aman. | High | Kredensial benar membuka sesi; kredensial salah ditolak; logout mengakhiri sesi. |
| PB-03 | Authorization | Sebagai admin, saya ingin halaman admin dibatasi. | High | User non-admin ditolak di server, termasuk akses URL langsung. |
| PB-04 | Authorization | Sebagai pelanggan, saya hanya ingin melihat pesanan milik saya. | High | Akses ke registration, payment, atau e-ticket user lain ditolak. |
| PB-05 | Catalog | Sebagai pengunjung, saya ingin melihat katalog tiket dan paket. | High | Produk aktif tampil dengan jenis dan harga tersimpan. |
| PB-06 | Catalog | Sebagai pengunjung, saya ingin melihat detail harga dan ketentuan. | High | Detail menampilkan data produk serta ketentuan kunjungan yang berlaku. |
| PB-07 | Catalog | Sebagai pengunjung, saya ingin mencari dan memfilter katalog. | Low | Pencarian/filter mempersempit hasil dan tidak menyertakan produk nonaktif. |
| PB-08 | General Ticket | Sebagai pelanggan, saya ingin memilih tanggal dan jumlah tiket. | High | Tanggal valid, jumlah positif, produk aktif, dan kuota cukup. |
| PB-09 | General Ticket | Sebagai pelanggan, saya ingin total dihitung server. | High | Perubahan nominal dari browser tidak mengubah harga tersimpan. |
| PB-10 | School Package | Sebagai pelanggan, saya ingin mengajukan paket sekolah online. | High | Data sekolah, kontak, peserta, tanggal, dan pemilik tersimpan. |
| PB-11 | School Package | Sebagai pelanggan, saya ingin memantau status pengajuan. | High | Pemilik dapat melihat status dan alasan penolakan. |
| PB-12 | School Package | Sebagai pelanggan, saya ingin membayar setelah pengajuan disetujui. | High | Status belum disetujui atau ditolak tidak dapat membuat transaksi. |
| PB-13 | School Package | Sebagai admin, saya ingin menyetujui atau menolak pengajuan. | High | Admin menetapkan keputusan; pelanggan tidak dapat menetapkan hasil. |
| PB-14 | School Package | Sebagai admin, saya ingin memberikan alasan penolakan. | Medium | Penolakan menyimpan alasan yang terlihat bagi pemilik pengajuan. |
| PB-15 | Payment | Sebagai pelanggan, saya ingin membayar melalui Midtrans Sandbox. | High | Backend membuat transaksi Snap dan menyimpan order lokal beserta token/URL. |
| PB-16 | Payment | Sebagai sistem, saya ingin memverifikasi pembayaran di server. | High | Webhook diverifikasi, order/nominal dicocokkan, status hanya berubah di server. |
| PB-17 | Payment | Sebagai pelanggan, saya ingin melanjutkan pembayaran yang belum selesai. | Medium | Aksi hanya tersedia selama transaksi berlaku; order baru punya ID unik. |
| PB-18 | E-Ticket | Sebagai pelanggan, saya ingin memperoleh e-ticket setelah pembayaran diverifikasi. | High | Kode unik dan waktu terbit dibuat satu kali setelah status berhasil. |
| PB-19 | E-Ticket | Sebagai pelanggan, saya ingin melihat riwayat dan detail pesanan. | High | Detail memisahkan status registration dan payment. |
| PB-20 | Admin | Sebagai admin, saya ingin CRUD tiket umum. | High | Produk umum tervalidasi; produk yang sudah dirujuk tidak merusak riwayat. |
| PB-21 | Admin | Sebagai admin, saya ingin CRUD paket sekolah. | High | Produk school dan status aktif dapat dikelola. |
| PB-22 | Admin | Sebagai admin, saya ingin memantau pesanan dan pembayaran. | High | Admin melihat pemilik, total, registration, dan status payment. |
| PB-23 | Admin | Sebagai admin, saya ingin memfilter pesanan berdasarkan status. | Medium | Daftar sesuai filter status yang dipilih. |
| PB-24 | Technical | Sebagai pengguna, saya ingin pesan validasi yang jelas. | High | Input salah menampilkan pesan terkait tanpa detail internal sensitif. |
| PB-25 | Technical | Sebagai pengembang, saya ingin memakai eager loading. | High | Halaman katalog/riwayat tidak menjalankan query relasi per baris. |
| PB-26 | Technical | Sebagai pengembang, saya ingin seeder akun dan data uji. | Medium | Seeder dev dapat dijalankan ulang dan tidak berisi credential produksi. |
| PB-27 | Testing | Sebagai pengembang, saya ingin menguji fitur, akses, harga, dan pembayaran. | High | Uji alur sukses/gagal, ownership, kuota, webhook duplikat, dan status. |
| PB-28 | Discount | Sebagai admin, saya ingin mengatur satu diskon pada setiap produk. | High | Admin mengatur jenis persentase atau nominal, nilai, periode inklusif, dan status; validasi mencegah konfigurasi tidak sah atau harga akhir di bawah Rp1. |
| PB-29 | Discount | Sebagai pengunjung dan pelanggan, saya ingin melihat dan membayar harga promo yang berlaku. | High | Produk aktif menampilkan harga normal, harga promo, dan label saat diskon aktif; backend menghitung harga tiket per unit dan paket sekolah per pengajuan saat checkout. |
| PB-30 | Discount and Payment | Sebagai pelanggan, saya ingin rincian harga transaksi tetap sama di riwayat. | High | Setiap payment menyimpan subtotal normal, jenis dan nilai diskon, total potongan, serta `gross_amount` final; perubahan produk tidak mengubah snapshot payment. |
| PB-31 | Redeem Code | Sebagai admin, saya ingin membuat dan mengelola kode redeem agar pelanggan bisa mendapat potongan. | High | Admin membuat kode persentase atau nominal dan mengaktifkan/menonaktifkannya. Kode berlaku untuk tiket umum dan paket sekolah, menggantikan diskon produk, hanya dapat dipakai satu kali total, dicadangkan selama payment pending, dan dianggap terpakai setelah pembayaran berhasil. Snapshot kode tersimpan pada payment. |

## Ketergantungan

- PB-03 memerlukan nilai role yang konsisten. Migration users saat ini memakai default costumer; lihat DATABASE.md.
- PB-05, PB-07, PB-20, dan PB-21 memerlukan klasifikasi tickets.type.
- PB-08 dan PB-10 memerlukan field kunjungan dan rombongan pada registrations.
- PB-15 dan PB-16 membutuhkan kredensial Sandbox, endpoint webhook HTTPS, serta pemisahan Server Key, Client Key, dan Signature Secret Key.
- PB-18 memerlukan kolom e-ticket dan payment yang sukses terverifikasi.
- PB-28 bergantung pada PB-20 dan PB-21 karena konfigurasi diskon dikelola pada produk yang sama.
- PB-29 bergantung pada PB-05, PB-06, PB-08, PB-09, PB-12, dan PB-28 agar harga katalog dan checkout memakai aturan yang sama.
- PB-30 bergantung pada PB-15, PB-16, dan PB-29 agar setiap percobaan payment menyimpan nominal final yang sama dengan transaksi Midtrans.
- PB-31 bergantung pada PB-15, PB-16, PB-28, PB-29, dan PB-30 agar kode tidak digabung dengan diskon produk, terikat ke payment, dilepas jika payment gagal/kedaluwarsa, dan dipakai hanya setelah pembayaran berhasil.

## Definition of Ready

Item siap dikerjakan ketika user story dan kriteria penerimaan dapat dipahami, dependensi diketahui, keputusan client yang diperlukan tersedia, dan schema/API terkait telah diperiksa.

## Definition of Done

Item selesai ketika implementasi memenuhi kriteria, validasi dan otorisasi berjalan, pengujian relevan lulus, tidak ada bug kritis, dan dokumentasi terkait diperbarui. Catat status berdasarkan hasil nyata.

