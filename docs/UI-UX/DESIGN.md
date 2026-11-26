# Panduan UI/UX
## Sertikom – The Nice Park Rumpin

## 1. Arah Visual

**Arah desain:** Tropical Adventure. Karakter yang dituju adalah wisata alam yang hangat, aktif, dan mudah dipahami. Beranda mengambil inspirasi dari alur Ancol yang menonjolkan hero dan pembelian tiket, tetapi mempertahankan identitas The Nice Park, konten dan aset sendiri. Jangan menyalin logo, foto, teks, atau komposisi khas Ancol.

## 2. Sistem Warna

### Palet Tropical Adventure

| Token | Nilai | Penggunaan |
|---|---|---|
| Forest Green | #174B2D | Navbar, footer, hero, dan elemen utama |
| Sunshine Yellow | #FFD447 | CTA utama, harga, dan highlight |
| Natural Cream | #FFF9E8 | Latar halaman |
| Leaf Green | #397A45 | Aksen sekunder |
| Adventure Orange | #F28C28 | Aksen kecil saja |
| Natural Charcoal | #26332F | Teks utama |
| Secondary Text | #64716B | Keterangan dan metadata |
| White Surface | #FFFFFF | Kartu dan formulir |
| Border | #E5DFC9 | Garis tepi dan pemisah |

Gunakan warna sesuai perannya. Jangan menyebarkan orange atau yellow ke semua komponen.

### Warna fungsional

| Token | Nilai | Penggunaan |
|---|---|---|
| Surface | #FFFFFF | Form, kartu informasi, modal |
| Teks utama | #26332F | Isi dan heading pada latar terang |
| Teks sekunder | #64716B | Keterangan dan metadata |
| Border | #E5DFC9 | Batas panel dan pemisah |
| Berhasil | #27845A | Pembayaran berhasil atau pengajuan disetujui |
| Menunggu | #A66D17 | Pembayaran atau pengajuan yang menunggu |
| Gagal/ditolak | #B83B35 | Error, penolakan, pembayaran gagal |

Periksa kontras aktual di setiap kombinasi warna. Status harus memiliki label teks, bukan hanya warna.

## 3. Tipografi

Gunakan sans-serif yang mudah dibaca dan tersedia di project. Plus Jakarta Sans dapat dipilih setelah lisensi dan pemuatan font diperiksa; fallback sistem harus tetap berfungsi tanpa jaringan eksternal.

| Elemen | Acuan desktop |
|---|---|
| H1 | 40–56 px |
| H2 | 32–40 px |
| H3 | 24–28 px |
| Isi | 14–16 px |
| Caption | 12–13 px |

Sesuaikan ukuran dan panjang baris untuk layar kecil. Hierarki dibentuk melalui ukuran, bobot, dan jarak, bukan kapitalisasi serta tracking berlebihan.

## 4. Struktur Layar

### Situs publik

Header memuat identitas, katalog, informasi kunjungan, dan aksi akun. Hero memakai foto lokasi asli jika tersedia. Katalog menunjukkan nama dan jenis produk, harga, serta ketersediaan. Halaman detail menjelaskan harga, ketentuan, kuota, dan langkah pemesanan. Kartu katalog serta halaman detail menyediakan rasio gambar yang konsisten. Informasi paket sekolah menjelaskan data yang diminta dan proses persetujuan. Footer hanya memuat kontak dan tautan yang benar-benar tersedia.

### Area pelanggan

- Daftar pesanan dengan kode, tanggal kunjungan, jumlah, total, dan status.
- Detail memisahkan status registration dari status payment.
- Aksi pembayaran hanya muncul jika pesanan memenuhi syarat dan transaksi masih berlaku.
- E-ticket menunjukkan kode, produk, tanggal kunjungan, serta jumlah tiket/peserta setelah verifikasi.

### Area admin

- Ringkasan operasional berdasarkan data yang tersedia.
- Pengelolaan tiket umum dan paket sekolah.
- Daftar pengajuan dengan aksi setujui/tolak dan alasan penolakan.
- Daftar pesanan dan pembayaran dengan filter status.

## 5. Komponen dan Keadaan

Komponen konsisten meliputi navigasi, tombol, input berlabel, kartu produk, tabel, status, dialog konfirmasi, pagination, notifikasi, dan empty/loading/error states.

- Tombol menyebutkan tindakannya, seperti “Lihat detail” atau “Ajukan paket”.
- Aksi destruktif meminta konfirmasi dan menjelaskan akibatnya.
- Fokus keyboard terlihat; dialog mendukung Escape dan mengembalikan fokus.
- State disabled menjelaskan syarat yang belum dipenuhi bila alasannya tidak jelas.
- Jangan membuat kontrol yang terlihat interaktif tetapi tidak berfungsi.

## 6. Responsif dan Aksesibilitas

- Gunakan layout satu kolom pada ponsel; tambah kolom saat ruang mencukupi.
- Navigasi mobile memakai tombol bernama aksesibel dengan state buka/tutup.
- Form tidak melebar dari viewport.
- Tabel panjang dapat digulir horizontal dengan petunjuk atau diubah menjadi daftar.
- Aksi utama mudah disentuh dan tidak berhimpitan.
- Hormati zoom browser dan prefers-reduced-motion.
- Gunakan HTML semantik, urutan heading yang wajar, alt text bermakna, dan focus state yang konsisten.
- Informasi penting tidak boleh bergantung pada hover.

## 7. Foto, Gerak, dan Dekorasi

Gerak hanya memberi umpan balik singkat, misalnya membuka menu atau menampilkan validasi. Hindari animasi yang menghalangi tugas, berjalan terus, atau mengabaikan preferensi pengurangan gerak.

Gunakan foto lokasi yang tersedia dan berizin. Sebelum foto tersedia, tampilkan placeholder yang jelas dan jangan menyamarkannya sebagai foto asli. Simpan foto produk di Laravel Storage setelah field unggah dan path gambar ditambahkan. Jangan membuat testimoni, statistik, fasilitas, atau klaim seolah-olah fakta. Gradient, pola, bayangan besar, ikon, dan badge digunakan hanya jika membantu hierarki atau fungsi.

## 8. Prinsip Anti-Slop

Gunakan panduan anti-slop yang kompatibel dengan repository sebagai pemeriksaan kualitas, bukan sumber identitas visual. Identitas tetap Tropical Adventure dengan palet yang ditetapkan.

- Setiap elemen visual mempunyai tujuan.
- Jangan memilih pola landing page atau dashboard hanya karena umum.
- Jangan mengulang kartu seragam jika konten membutuhkan hierarki berbeda.
- Gunakan ruang kosong untuk memisahkan kelompok informasi.
- Kurangi ornamentasi yang tidak memberi informasi.
- Pastikan interaksi berfungsi, dapat dijangkau keyboard, dan memiliki state.
- Jangan memasukkan klaim atau konten wisata tanpa sumber.

## 9. Pemeriksaan Desain

- [ ] Palet #174B2D, #FFD447, #FFF9E8, #397A45, #F28C28, #26332F, #64716B, #FFFFFF, dan #E5DFC9 dipakai sesuai perannya.
- [ ] Kontras sudah diperiksa untuk kombinasi warna aktual.
- [ ] Layout diperiksa pada desktop, tablet, dan ponsel.
- [ ] Fokus keyboard, label, alt text, dan empty/loading/error states tersedia.
- [ ] Status punya label teks.
- [ ] Tidak ada aksi yang tampak interaktif tetapi tidak bekerja.
- [ ] Foto dan klaim memiliki sumber; placeholder ditandai jelas.
- [ ] Gerak menghormati prefers-reduced-motion.
- [ ] Ornamen punya alasan identitas atau fungsi.

