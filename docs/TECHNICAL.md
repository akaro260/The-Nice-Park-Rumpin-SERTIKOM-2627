# Dokumentasi Teknis
## Sertikom – The Nice Park Rumpin

## 1. Baseline Project

Repository menggunakan Laravel Framework 13.43.0 dan PHP 8.4. Composer memasang midtrans/midtrans-php 2.6.2, Laravel Breeze, serta Pest. Frontend memakai Blade, Vite, Alpine.js, dan Tailwind CSS. Database engine pada dokumen lama disebut MySQL; pastikan nilai aktual mengikuti konfigurasi environment sebelum migration.

Source saat ini mencakup autentikasi Breeze, dashboard/profil, model User/Ticket/Registration/Payment, serta migration ticketing. Route web yang ada masih mencakup welcome, dashboard, profil, dan autentikasi. Katalog, alur booking, admin, e-ticket, serta endpoint webhook belum terlihat pada baseline yang diperiksa. Anggap modul di bawah sebagai rancangan implementasi sampai code dan pengujian membuktikan sebaliknya.

## 2. Arsitektur

Ikuti struktur Laravel yang telah ada. Gunakan Eloquent untuk relasi dan query, Form Request atau validasi Laravel sesuai pola controller, policy/middleware untuk otorisasi, Blade untuk halaman, dan service hanya bila ada batas tanggung jawab yang jelas. Model inti: User, Ticket, Registration, Payment.

Area logis: autentikasi; katalog dan administrasi produk; registration/approval; payment; riwayat pelanggan; e-ticket. Controller dan route spesifik ditentukan saat implementasi dengan memeriksa pola project. Jangan membuat nama endpoint di dokumentasi sebagai klaim bahwa endpoint tersebut sudah tersedia.

## 3. Alur Pemesanan

1. Validasi login, pemilik, jenis tiket, status aktif, tanggal, jumlah, dan kuota.
2. Ambil harga ticket dari database; jangan menerima total dari browser.
3. Simpan registration dan alokasi kuota dalam transaksi database dengan perlindungan konkurensi.
4. Untuk school, tunggu persetujuan admin sebelum membuat pembayaran.
5. Buat order ID unik untuk setiap percobaan pembayaran.
6. Buat Snap transaction melalui SDK/backend dengan gross_amount dari server.
7. Simpan token dan URL Snap pada payment yang terkait registration/user.
8. Browser menggunakan Snap untuk checkout; callback/redirect hanya untuk tampilan.
9. Webhook server memvalidasi signature/status/order/nominal lalu memperbarui payment secara idempoten.
10. Setelah status sukses terverifikasi, konfirmasi registration dan terbitkan e-ticket satu kali.

Aturan reservasi kuota, tanggal, pembatalan, masa berlaku, dan refund perlu keputusan client. Hindari mengasumsikan masa hold atau pengembalian kuota sebelum aturan ditetapkan.

## 4. Midtrans Snap Sandbox

### Dependency dan environment

SDK PHP tersedia pada versi 2.6.2. Gunakan Sandbox untuk pengembangan dan pengujian. Endpoint pembuatan transaksi Snap Sandbox adalah https://app.sandbox.midtrans.com/snap/v1/transactions. Snap.js Sandbox dimuat dari https://app.sandbox.midtrans.com/snap/snap.js. Production memakai host dan key berbeda; tidak termasuk scope.

Variabel environment yang disarankan:

    MIDTRANS_SERVER_KEY=...
    MIDTRANS_CLIENT_KEY=...
    MIDTRANS_SIGNATURE_KEY=...
    MIDTRANS_IS_PRODUCTION=false

Jangan isi nilai rahasia dalam dokumentasi, screenshot, log, atau repository. Server Key mengotorisasi panggilan server ke Midtrans. Client Key digunakan untuk Snap.js di browser. Signature Secret Key digunakan untuk memverifikasi notifikasi pada handler kustom.

Konfigurasi project yang ditemukan sekarang membaca Server Key dari Server_Key_Midtrans melalui config/services.php dan menyediakan MIDTRANS_IS_PRODUCTION default false. Nama environment Server_Key_Midtrans berbeda dari nama yang disarankan di atas; rapikan konfigurasi saat integrasi diimplementasikan, atau dokumentasikan pemetaan secara eksplisit. Jangan memindahkan Server Key ke frontend.

### Siklus pembayaran

- Backend membuat request dengan order_id unik dan gross_amount berdasarkan database.
- Snap membatasi order_id pada 50 karakter dengan karakter yang didukung Midtrans. API menerima gross_amount dalam integer untuk IDR; konversi dari DECIMAL lokal harus eksak dan server tidak boleh membulatkan nominal secara diam-diam.
- Midtrans mengembalikan transaction token. Frontend membuka Snap menggunakan token dan Client Key.
- Browser dapat kembali ke halaman aplikasi setelah interaksi, tetapi redirect bukan sumber kebenaran status.
- Midtrans mengirim HTTP notification ke endpoint server yang dikonfigurasi.
- Endpoint harus HTTPS pada lingkungan yang dapat dijangkau Midtrans.
- Handler mencocokkan order_id dengan record lokal dan memastikan gross_amount sama. Periksa status_code, transaction_status, dan fraud_status sesuai aturan.
- Status settlement dapat diperlakukan sukses. Capture hanya sukses jika fraud_status accept; challenge/deny bukan sukses.
- Pada keadaan tidak jelas, periksa status transaksi melalui API Midtrans dari server.
- Gunakan transaksi database dan idempotensi. Satu event yang terkirim ulang tidak boleh membuat payment atau e-ticket ganda; notifikasi status berikutnya untuk order yang sama tetap perlu diproses sebagai transisi status yang sah.

### Signature notification

Dokumentasi Midtrans terkini menyatakan signature notification dihitung sebagai SHA512(order_id + status_code + gross_amount + Signature Secret Key). Sejak 28 Oktober 2026, Signature Secret Key terpisah dari Server Key untuk integrasi handler sendiri. Gunakan Signature Secret Key untuk signature notification dan Server Key untuk API authorization. Jangan memakai Server Key untuk signature baru bila key keduanya sudah terpisah. Rotasi key dan notifikasi tertunda perlu ditangani sesuai instruksi Midtrans.

Sumber resmi:
- [HTTP(S) Notification/Webhooks](https://docs.midtrans.com/docs/https-notification-webhooks)
- [Access Keys](https://docs.midtrans.com/docs/access-keys)
- [Snap Integration Guide](https://docs.midtrans.com/docs/snap-snap-integration-guide)
- [Handle Notifications](https://docs.midtrans.com/reference/handle-notifications)

### Pemetaan status

| Midtrans | Dampak aplikasi |
|---|---|
| pending | payment menunggu; registration belum dikonfirmasi |
| settlement | pembayaran berhasil setelah order/nominal diverifikasi |
| capture + fraud accept | berhasil setelah seluruh pemeriksaan lolos |
| capture + fraud challenge/deny | tidak menerbitkan e-ticket |
| expire | payment kedaluwarsa; tindak lanjut kuota mengikuti kebijakan |
| cancel | payment dibatalkan |
| deny | payment gagal |
| refund / partial_refund | catat pengembalian dana; status registration dan e-ticket perlu kebijakan pembatalan yang ditetapkan |

Nilai transaction_status dari Midtrans disimpan terpisah dari status registration. Jangan mereduksi seluruh status gateway ke label paid/gagal tanpa menyimpan status asal.

## 5. Schema dan Integritas

Migration saat ini menyediakan users, tickets, registrations, dan payments. Rincian kolom, foreign key, cascade, dan delta tersedia di DATABASE.md. Schema belum mencukupi jenis tiket, kunjungan/rombongan, dan e-ticket. Lakukan migration maju setelah memeriksa data yang telah ada.

Foreign key registrations.ticket_id membatasi penghapusan ticket; payments terhubung ke registration dan user. Pastikan payment.user_id sama dengan registration.user_id di service/policy. Unique order_id mencegah order lokal ganda. Transisi kuota dan payment harus dibungkus transaksi database dan mempertimbangkan permintaan bersamaan.

## 6. Keamanan dan Validasi

- Lindungi route admin dengan middleware/authorization di server.
- Terapkan policy/ownership check untuk setiap registration, payment, dan e-ticket pelanggan.
- Validasi quantity sebagai integer positif, ticket aktif, tanggal valid, dan kuota cukup.
- Validasi field sekolah secara kondisional.
- Jangan menerima harga, role, status pembayaran, atau e-ticket code sebagai nilai tepercaya dari user.
- Verifikasi signature notification dengan Signature Secret Key; cocokkan order dan nominal lokal.
- Batasi payload log; jangan menulis key, token rahasia, atau data kartu.
- Endpoint webhook memerlukan HTTPS pada deployment yang dapat diakses Midtrans.
- Proteksi formulir web dengan mekanisme CSRF Laravel; pengecualian webhook hanya bila diperlukan dan diganti verifikasi signature yang benar.
- Idempotensi mencakup pembaruan status, pemakaian kuota, dan penerbitan e-ticket.

## 7. Kinerja dan Observabilitas

Gunakan eager loading pada daftar yang menampilkan relasi, pagination untuk tabel, dan indeks berdasarkan query aktual. Catat order ID, payment ID, hasil validasi, dan error yang dapat ditindaklanjuti tanpa rahasia. Pantau webhook gagal agar status dapat direkonsiliasi menggunakan API status Midtrans.

## 8. Konfigurasi dan Operasional

- Pisahkan konfigurasi local/test dan production.
- Jaga .env di luar repository; sediakan contoh tanpa nilai rahasia.
- Sandbox menggunakan key dan endpoint Sandbox.
- Verifikasi URL callback/webhook dari dashboard Midtrans.
- Sebelum menjalankan migration, backup database dan periksa status migration.
- Jangan menggunakan migrate:fresh pada database dengan data penting.

