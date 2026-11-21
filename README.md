# Sistem Informasi Antrean & Rekapitulasi Pengunjung PST BPS Kota Tasikmalaya

[![Laravel](https://img.shields.io/badge/Laravel-11%20%2F%2012-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=flat&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/PHPUnit-42%20Passed-brightgreen?style=flat&logo=php)](file:///tests)

Sistem Informasi Antrean Berbasis Web dengan Kiosk Thermal Printing Layout, Realtime Display Sound Announcer (Server-Sent Events), Meja Kerja Pelayanan Terpadu Buku Tamu Digital (Kepatuhan UU PDP No. 27 Tahun 2022), serta Mini Dashboard Rekapitulasi Kunjungan untuk **Pelayanan Statistik Terpadu (PST) Badan Pusat Statistik (BPS) Kota Tasikmalaya**.

---

## 🏛️ Latar Belakang & Ruang Lingkup Proyek

Sistem ini mereplikasi alur antrean modern perbankan yang disesuaikan dengan kebutuhan pelayanan publik di lingkungan BPS:
1. **Pengunjung** mengambil tiket antrean di anjungan mandiri Kiosk berdasarkan salah satu dari 4 kategori layanan PST BPS.
2. **Layar Display TV** di ruang tunggu secara otomatis menampilkan panggilan nomor antrean, membunyikan nada lonceng (*chime*) dan suara panggilan sintetis, serta menampilkan jumlah antrean menunggu per layanan.
3. **Petugas Loket** memanggil antrean secara berurutan (*FIFO*), melayani pengunjung, dan mencatat buku tamu digital (nama, kontak, instansi, kategori, kebutuhan data, serta pernyataan persetujuan penggunaan data pribadi).
4. **Pimpinan & Administrator** memantau indikator kinerja pelayanan (KPI), tren harian, jam sibuk pelayanan, proporsi layanan dan instansi melalui grafik interaktif offline (Chart.js lokal), serta mengunduh dokumen laporan resmi berformat **CSV (UTF-8 BOM)** dan **PDF ber-Kop Resmi BPS**.

---

## ⚙️ Aturan Bisnis & Logika Antrean (*Core Queue Rules*)

1. **4 Pilar Layanan Resmi PST BPS**:
   - **A**: Konsultasi Statistik
   - **B**: Rekomendasi Kegiatan Statistik (Romantik)
   - **C**: Permintaan Data Mikro & Publikasi
   - **D**: Perpustakaan Statistik
2. **Penomoran Mandiri & Reset Harian**:
   - Tiket bernomor urut per kode prefix layanan (misal: `A-001`, `B-001`). Nomor tiket di-reset otomatis setiap pergantian hari kerja.
3. **Pembatasan Panggil Ulang (*Recall Limit*) & Tombol Lewati (*Skip*)**:
   - Petugas loket dapat memanggil ulang (*recall*) maksimal **3 kali** per tiket.
   - Tombol **Lewati (Skip)** baru aktif setelah panggilan ulang ke-3 dilakukan (kecuali bagi akun bertingkat Administrator yang dapat melewati langsung jika terjadi anomali).
4. **Panggilan Ulang Tiket yang Terlewat (*Recall Skipped Ticket*)**:
   - Tiket berstatus `skipped` dapat dipanggil ulang oleh petugas loket sebanyak **1 kali** pada hari yang sama.
   - Tiket yang dipanggil ulang otomatis dipindahkan kembali ke posisi **paling depan** antrean layanannya.
5. **Pencegahan Panggilan Ganda (*Double Call Lock*)**:
   - Suatu meja loket tidak dapat memanggil tiket baru selama masih memiliki antrean aktif berstatus `calling` (sedang dipanggil) atau `serving` (sedang dilayani).
6. **Validasi Transisi Status Ketat**:
   - Alur antrean wajib mengikuti status berurutan: `waiting` → `calling` → `serving` → `completed` (atau `calling` → `skipped`). Tiket tidak dapat diselesaikan jika belum berada dalam status `serving`.
7. **Perhitungan Durasi Presisi (Non-Negatif)**:
   - **Waktu Tunggu (*Wait Time*)**: Selisih `called_at − created_at`.
   - **Waktu Pelayanan (*Serve Time*)**: Selisih `completed_at − served_at`.
8. **Penutupan Antrean Terjadwal Otomatis**:
   - Command Artisan `php artisan queues:close-expired` secara otomatis menutup tiket yang tertinggal berstatus `waiting`, `calling`, atau `serving` dari hari-hari sebelumnya menjadi `skipped` agar data statistik rekapitulasi tetap bersih dan akurat.

---

## 🚀 Fitur Unggulan Sistem

### 1. Portal Landing Page Terpadu (`/`)
- Gerbang akses elegan dengan identitas instansi resmi BPS Kota Tasikmalaya.
- Tautan cepat ke Anjungan Kiosk, Layar Display TV, Meja Loket, dan Dashboard Admin.
- Edukasi publik mengenai 4 pilar layanan PST dan jaminan privasi UU No. 27 Tahun 2022 (UU PDP).

### 2. Anjungan Kiosk Mandiri Layar Sentuh (`/kiosk`)
- Antarmuka ramah layar sentuh (*touch-friendly*) dengan tombol berukuran besar dan kontras tinggi.
- Proteksi klik ganda (*double-click prevention*) dan status animasi *loading*.
- Pratinjau tiket dengan hitung mundur reset otomatis (*auto-reset countdown*) 5 detik.
- Siap cetak printer termal fisik (lebar 58 mm dan 80 mm) via mode *Silent Printing* Chromium.

### 3. Layar Display Monitor TV & Audio Announcer (`/display`)
- Monitor visual ruang tunggu dengan jam digital realtime dan riwayat 5 panggilan terakhir.
- Ringkasan jumlah antrean menunggu per layanan A/B/C/D secara waktu nyata.
- **Koneksi SSE Realtime dengan Fallback Polling Cerdas**: Indikator status 3 warna (Hijau: SSE Terhubung, Kuning: Menghubungkan Ulang, Biru: Mode Polling LAN).
- **Penanganan Browser Autoplay**: Tombol interaktif **"AKTIFKAN SUARA TV"** untuk membuka kunci Web Audio API di pagi hari.
- **Audio Queue FIFO**: Mencegah tabrakan pengumuman suara saat dua loket memanggil bersamaan.
- Panel uji coba audio yang dapat disembunyikan (*collapsible debug panel*).

### 4. Meja Kerja Pelayanan Loket Terpadu (`/loket`)
- Pilihan loket kerja yang fleksibel dengan ringkasan antrean menunggu per layanan loket tersebut.
- Penampil tiket aktif lengkap dengan penghitung panggil ulang (*Recall Counter: x/3*).
- **Formulir Buku Tamu Digital Terpadu**:
  - Validasi sisi klien dan server: Nama Lengkap, Nomor Kontak/WhatsApp, Email, Asal Instansi, Kategori Instansi (Pendidikan, Pemerintah, Swasta, LSM, Umum), dan Deskripsi Kebutuhan Data.
  - Checkbox pernyataan persetujuan penggunaan data pribadi sesuai amanat **UU No. 27 Tahun 2022 (UU PDP)**.
- Kartu panggilan ulang tiket terlewat hari ini (*Recall Skipped*).

### 5. Mini Dashboard Rekapitulasi & Master Data Admin (`/admin/dashboard`)
- **Filter Eksplisit Bulan & Tahun**: Dilengkapi perbandingan persentase *Month-over-Month (MoM)* terhadap bulan sebelumnya.
- **4 Grafik Interaktif Offline (Chart.js Lokal)**:
  - Tren kunjungan harian sepanjang bulan.
  - Analisis jam sibuk kedatangan pemohon (*Peak Hours*: 08.00 - 15.00 WIB).
  - Distribusi volume pemohon per 4 layanan PST BPS.
  - Segmentasi kategori instansi pemohon data.
- **Kelola Layanan**: Tambah, ubah nama, prefix, urutan prioritas, dan aktifkan/nonaktifkan layanan.
- **Kelola Loket**: Atur nomor loket, nama, dan penugasan layanan spesifik per loket.
- **Kelola Pengguna**: Tambah akun petugas baru, aktifkan/nonaktifkan akun, dan reset kata sandi.

### 6. Rekapitulasi Laporan & Ekspor Data (`/admin/reports`)
- Pencarian teks terpadu (*Nama, Instansi Asal, Kontak, atau Kode Tiket*).
- Filter rentang tanggal mulai & selesai, layanan, dan kategori instansi dengan paginasi terjaga (`withQueryString`).
- **Ekspor CSV**: Menyertakan **UTF-8 BOM** (`0xEF, 0xBB, 0xBF`) agar data dan karakter terbuka rapi di Microsoft Excel.
- **Ekspor PDF Resmi**: Format A4 Landscape ber-**Kop Dinas Resmi BPS Kota Tasikmalaya** (garis ganda dinas, rekapitulasi KPI, tabel data pengunjung, dan lembar tanda tangan pengesahan Kepala BPS & Penanggung Jawab PST).

### 7. Keamanan & Kepatuhan
- **Role Middleware**: Membatasi hak akses (`admin` untuk `/admin/*`, `petugas` untuk `/loket`). Petugas dilarang membuka panel admin (otomatis dialihkan ke halaman 403 kustom).
- **Validasi Akun Nonaktif**: Akun yang dinonaktifkan (`is_active = false`) langsung ditolak saat mencoba login.
- **Fitur Ganti Password Mandiri**: Tersedia di rute `/password/change`.
- **Peringatan Kata Sandi Bawaan**: Dashboard admin menampilkan peringatan dinamis jika admin masih menggunakan kata sandi default.
- **Halaman Error Informatif (Bahasa Indonesia)**: 403 (Akses Ditolak), 404 (Tidak Ditemukan), 419 (Sesi Berakhir), dan 500 (Kendala Server).

---

## 🔑 Akun Bawaan Sistem (*Default Credentials*)

Sistem telah dilengkapi dua akun awal untuk keperluan pengujian dan operasional:

| Peran (*Role*) | Username | Password | Wewenang & Akses Halaman |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Akses penuh ke Portal Admin (`/admin/*`), Master Data, Meja Loket, Dashboard, & Laporan. |
| **Petugas Loket** | `petugas1` | `petugas123` | Meja Kerja Operasional Loket Pelayanan (`/loket`). Ditolak dari `/admin/*`. |

---

## 💻 Panduan Instalasi & Menjalankan di Komputer Lokal

### 1. Kebutuhan Sistem
- **PHP 8.2 ke atas** (Pastikan ekstensi `pdo_sqlite`, `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, dan `curl` aktif).
- **Composer 2.x**.
- **Web Browser modern** (Google Chrome, Microsoft Edge, atau Mozilla Firefox).

### 2. Langkah Instalasi

```bash
# 1. Masuk ke direktori proyek
cd CapstoneProject-master

# 2. Pasang dependensi PHP
composer install

# 3. Salin berkas lingkungan .env
cp .env.example .env
# Pada Windows PowerShell:
# Copy-Item .env.example .env

# 4. Generate App Key Laravel
php artisan key:generate

# 5. Buat basis data SQLite lokal (jika menggunakan driver SQLite dev)
touch database/database.sqlite
# Pada Windows PowerShell:
# New-Item -ItemType File -Path database/database.sqlite -Force

# 6. Jalankan migrasi basis data & seeder data master
php artisan migrate:fresh --seed
```

### 3. Memuat Data Demo Kunjungan Fiktif Lintas Bulan (Rekomendasi Demo)
Untuk mengisi dashboard dengan **ratusan data kunjungan realistis fiktif** lintas bulan (September, Oktober, November 2026) agar seluruh grafik dan komparasi MoM terisi penuh:
```bash
php artisan db:seed --class=DemoVisitorSeeder
```

### 4. Menjalankan Peladen Lokal
Karena modul Display TV menggunakan koneksi *keep-alive* Server-Sent Events (SSE), jalankan server dengan multi-worker:
```bash
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000
```
Buka peramban pada alamat: **`http://localhost:8000`**.

---

## 🧪 Menjalankan Rangkaian Pengujian Otomatis (*Automated Tests*)

Seluruh logika antrean, nomor urut unik, keamanan role, proteksi konkurensi, validasi buku tamu, SSE, filter laporan, ekspor CSV, dan ekspor PDF diuji secara otomatis menggunakan PHPUnit:

```bash
php artisan test
```

**Hasil Pengujian:**
```
PASS  Tests\Feature\AdminManagementTest (4 tests)
PASS  Tests\Feature\ConcurrencyQueueTest (2 tests)
PASS  Tests\Feature\CounterOperationsTest (3 tests)
PASS  Tests\Feature\DashboardReportTest (4 tests)
PASS  Tests\Feature\DatabaseSchemaTest (3 tests)
PASS  Tests\Feature\DisplayPollTest (2 tests)
PASS  Tests\Feature\DisplayStreamTest (1 test)
PASS  Tests\Feature\ExampleTest (1 test)
PASS  Tests\Feature\GuestBookValidationTest (4 tests)
PASS  Tests\Feature\KioskTicketTest (2 tests)
PASS  Tests\Feature\LandingAndErrorPagesTest (3 tests)
PASS  Tests\Feature\QueueNumberingTest (2 tests)
PASS  Tests\Feature\QueueRecallSkipTest (6 tests)
PASS  Tests\Feature\RoleSecurityTest (4 tests)
PASS  Tests\Feature\SseStreamTest (1 test)

Tests:    42 passed (220 assertions)
Duration: ~20-30 detik
```

---

## 🖥️ Panduan Simulasi Pengujian 4 Tab di Browser

Untuk mensimulasikan alur pelayanan antrean secara utuh di satu komputer:

1. **Tab 1 — Kiosk Pengunjung (`http://localhost:8000/kiosk`)**:
   - Sentuh layanan **"Konsultasi Statistik"**.
   - Tiket virtual `A-001` akan terbit dengan jumlah antrean di depan. Layar akan mereset kembali dalam 5 detik.
2. **Tab 2 — Display TV Ruang Tunggu (`http://localhost:8000/display`)**:
   - Klik tombol **"AKTIFKAN SUARA TV"** untuk membuka izin audio browser.
   - Amati antrean menunggu kategori A bertambah menjadi 1.
3. **Tab 3 — Meja Petugas Loket (`http://localhost:8000/login`)**:
   - Masuk dengan akun `petugas1` / `petugas123`.
   - Pilih "Loket 1", lalu klik tombol hijau **"PANGGIL BERIKUTNYA"**.
   - Tab 2 (Layar TV) seketika menampilkan nomor `A-001` berkedip dan membunyikan suara panggilan.
   - Di Tab 3, tekan **"Mulai Melayani"** untuk membuka formulir Buku Tamu Digital.
   - Isi nama pengunjung, nomor WhatsApp, instansi, centang persetujuan UU PDP, dan klik **"SIMPAN BUKU TAMU & SELESAIKAN ANTREAN"**.
4. **Tab 4 — Dashboard Administrator (`http://localhost:8000/admin/dashboard`)**:
   - Masuk dengan akun `admin` / `admin123`.
   - Perhatikan total tamu dilayani bertambah, durasi tunggu & layanan terhitung otomatis, grafik tren terbarui, dan data pengunjung muncul di tabel tamu terakhir.
   - Buka menu **"Rekap Laporan"** (`/admin/reports`), coba pencarian teks nama, lalu klik tombol **"Ekspor CSV"** dan **"Ekspor PDF Resmi"**.

---

## 🖨️ Kesiapan & Catatan Serah Terima Hardware

Untuk panduan lengkap mengenai konfigurasi printer termal fisik (ESC/POS 58mm/80mm), mode *silent kiosk printing*, optimasi Web Audio API, skrip auto-start sistem operasi, dan konfigurasi Nginx + PHP-FPM di jaringan LAN kantor BPS, silakan membaca dokumen terpisah:
👉 **[Catatan Serah Terima Fase Hardware (docs/handover-hardware-guide.md)](docs/handover-hardware-guide.md)**.

---

## 📄 Lisensi & Hak Cipta

Dikembangkan untuk proyek capstone **Pelayanan Statistik Terpadu (PST) Badan Pusat Statistik (BPS) Kota Tasikmalaya** © 2026. Hak Cipta Dilindungi.
