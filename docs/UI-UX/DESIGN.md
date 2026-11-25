# Panduan UI/UX
## Sertikom – The Nice Park Rumpin

## 1. Arah Visual

**Arah desain:** Nature Premium. Karakter yang dituju adalah suasana wisata alam yang hangat, rapi, premium, dan mudah dipahami. Identitas datang dari warna yang konsisten, informasi kunjungan yang nyata, serta foto lokasi yang benar-benar tersedia. Hindari layout dashboard generik dan dekorasi yang tidak membantu pengguna.

## 2. Sistem Warna

### Palette utama

| Token | Nilai | Penggunaan |
|---|---|---|
| Main | #183630 | Navigasi, footer, tombol primer, teks pada surface terang |
| Secondary | #E3DAC9 | Latar section dan panel informasi |
| Accent | #E5C690 | Penekanan harga, highlight kecil, indikator pilihan |

Gunakan accent secara terbatas, bukan sebagai latar semua komponen.

### Warna fungsional

| Token | Nilai | Penggunaan |
|---|---|---|
| Surface | #FFFFFF | Form, kartu informasi, modal |
| Teks utama | #26332F | Isi dan heading pada latar terang |
| Teks sekunder | #64716B | Keterangan dan metadata |
| Border | #D4CCBD | Batas panel dan pemisah |
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

Header memuat identitas, katalog, informasi kunjungan, dan aksi akun. Hero memakai foto lokasi asli jika tersedia. Katalog menunjukkan nama dan jenis produk, harga, serta ketersediaan. Halaman detail menjelaskan harga, ketentuan, kuota, dan langkah pemesanan. Informasi paket sekolah menjelaskan data yang diminta dan proses persetujuan. Footer hanya memuat kontak dan tautan yang benar-benar tersedia.

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

Gunakan foto lokasi yang tersedia dan berizin. Jangan membuat testimoni, statistik, fasilitas, atau klaim seolah-olah fakta. Gradient, pola, bayangan besar, ikon, dan badge digunakan hanya jika membantu hierarki atau fungsi.

## 8. Prinsip Anti-Slop

Gunakan panduan anti-slop yang kompatibel dengan repository sebagai pemeriksaan kualitas, bukan sumber identitas visual. Identitas tetap Nature Premium dengan palette yang ditetapkan.

- Setiap elemen visual mempunyai tujuan.
- Jangan memilih pola landing page atau dashboard hanya karena umum.
- Jangan mengulang kartu seragam jika konten membutuhkan hierarki berbeda.
- Gunakan ruang kosong untuk memisahkan kelompok informasi.
- Kurangi ornamentasi yang tidak memberi informasi.
- Pastikan interaksi berfungsi, dapat dijangkau keyboard, dan memiliki state.
- Jangan memasukkan klaim atau konten wisata tanpa sumber.

## 9. Pemeriksaan Desain

- [ ] Palette #183630, #E3DAC9, #E5C690 konsisten.
- [ ] Kontras sudah diperiksa untuk kombinasi warna aktual.
- [ ] Layout diperiksa pada desktop, tablet, dan ponsel.
- [ ] Fokus keyboard, label, alt text, dan empty/loading/error states tersedia.
- [ ] Status punya label teks.
- [ ] Tidak ada aksi yang tampak interaktif tetapi tidak bekerja.
- [ ] Foto dan klaim memiliki sumber.
- [ ] Gerak menghormati prefers-reduced-motion.
- [ ] Ornamen punya alasan identitas atau fungsi.

