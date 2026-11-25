# Dokumen Desain Teknis (Spesifikasi UI/UX Overhaul)
## Overhaul Antarmuka & Perbaikan Frontend Sistem Informasi Antrean PST BPS Kota Tasikmalaya

- **Tanggal Dokumen**: 25 November 2026
- **Status**: Menunggu Persetujuan Akhir Spesifikasi (Design Approved, Spec Review)
- **Pendekatan Teknis**: Pendekatan 1 — Vite Tailwind CSS v4 Lokal + Komponen Blade Modular (`<x-...>`) + Vanilla JS Terarah
- **Gaya Visual**: *Civic Institutional Precision* (Mode Terang Institusional untuk Portal/Kiosk/Workspace, *High-Contrast Dark Board* untuk Display TV Ruang Tunggu)

---

## 1. Latar Belakang, Tujuan & Batasan Ruang Lingkup

### 1.1 Permasalahan Saat Ini (Hasil Audit Kode)
1. **Ketergantungan CDN pada Sistem LAN Offline:** Seluruh 15 berkas view Blade memuat `<script src="https://cdn.tailwindcss.com"></script>` dari internet, sedangkan `vite.config.js` dan `resources/css/app.css` belum dikompilasi (`node_modules` dan `public/build` belum tersedia). Di lingkungan LAN offline kantor BPS, kegagalan akses CDN merusak seluruh tata letak halaman.
2. **Estetika Generik ("AI Slop") & Duplikasi Markup:** Setiap halaman menyalin ulang kerangka `<!DOCTYPE html>`, `<head>`, `<header>`, dan `<footer>`. Tampilan mengandalkan pola generik seperti `rounded-3xl` dan `shadow-2xl` berlebihan, kartu bertumpuk, ikon emoji (`🏛️`, `🎫`, `📺`, `🖥️`, `📊`, `⚠️`, `🔊`, `🔍`, `📥`, `📄`), serta animasi mengganggu (`animate-bounce`, `animate-ping`).
3. **Inkonsistensi Antar Halaman:** Header admin pada `reports`, `services`, `counters`, dan `users` tidak memiliki tautan `Kata Sandi` yang ada di `dashboard`. Navigasi admin tidak responsif di layar sempit. Halaman `/loket` tidak memiliki footer dan mencampur warna header berbeda (`bg-blue-900` vs `bg-blue-950`).
4. **Bug Fungsional Frontend:**
   - **Cetak Termal Kiosk Kosong (`kiosk/index.blade.php`):** Elemen `#ticket-print-area` memakai class `hidden` (`display: none`), sementara blok `@media print` hanya mengubah `visibility`, sehingga cetak fisik `window.print()` berisiko mencetak halaman kosong.
   - **Status Loket Stagnan di Display TV (`display/index.blade.php`):** `initial-state` dan `pollUpdates` hanya memperbarui loket yang muncul di `active_queues` dan tidak mereset loket yang antreannya sudah `completed` atau `skipped`. Daftar riwayat 5 panggilan terakhir juga tidak disinkronkan saat mode fallback polling dan memakai interpolasi `innerHTML`.
   - **UX Pemblokir & Fragilitas Form (`counter/index.blade.php` & `admin/*`):** Meja loket masih memakai `alert()` / `confirm()` bawaan browser, reload halaman penuh tanpa indikator loading tombol, serta tidak menampilkan error validasi per-field. Modal admin memakai `div.hidden` tanpa dukungan `Esc` / klik luar / reset form, dan fungsi edit mengirim string mentah via `addslashes()` di atribut `onclick`.
   - **Kebocoran UI Pengembang & Data Mentah:** Panel pengujian audio pengembang tampil terbuka di footer Display TV publik; tabel `/admin/reports` menampilkan kode mentah `institution_type` (`pendidikan`, `umum`) alih-alih label Indonesia yang terbaca.

### 1.2 Ruang Lingkup & Batasan
* **Termasuk dalam cakupan:** Kompilasi Tailwind CSS v4 lokal via Vite, arsitektur layout & komponen Blade, desain ulang visual seluruh 15 view web (7 halaman `portal`, 6 halaman `workspace`, 2 halaman `screen`) serta penyelarasan visual 1 template cetak PDF DomPDF (`admin/reports/pdf.blade.php`), perbaikan seluruh bug frontend pada Bagian 1.1, serta penambahan/pembaruan feature test terkait.
* **Di luar cakupan (Tidak Diubah):** Skema basis data dan migrasi, logika bisnis/state-machine backend (`QueueNumberService`, `CounterController`, `DisplayController`, `DashboardController`, `ReportController`), struktur respons JSON API, serta tidak menambah framework JS baru (Alpine.js, React, Vue).

---

## 2. Sistem Desain (*Civic Institutional Precision*)

### 2.1 Zero-CDN & Offline Font Strategy
* Menghapus `https://cdn.tailwindcss.com` dari seluruh template Blade.
* Menghapus konfigurasi font eksternal `bunny(...)` pada `vite.config.js` agar build produksi tidak menghasilkan permintaan jaringan eksternal ke CDN font saat berjalan di LAN tertutup.
* Mendefinisikan font stack lokal yang bersih pada `@theme` di `resources/css/app.css`:
  - `--font-sans`: `'Public Sans', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif`
  - `--font-mono`: `'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, Monaco, Consolas, monospace`
* Seluruh angka dinamis (nomor antrean, jam digital, durasi menit, statistik KPI, paginasi) memakai `font-variant-numeric: tabular-nums` agar lebar digit konsisten dan tidak bergeser saat pembaruan realtime.

### 2.2 Token Warna, Permukaan & Radius (`@theme`)
* **Kanvas & Permukaan (Light Institutional):**
  - `--color-canvas`: `#F4F6F9` (latar utama halaman)
  - `--color-surface`: `#FFFFFF` (kartu, tabel, dialog)
  - `--color-surface-muted`: `#EDF1F6` (header tabel, baris netral)
  - `--color-border`: `#DCE3EC` (garis batas struktural `1px`, menggantikan shadow berat)
  - `--color-border-strong`: `#CBD5E1` (garis input dan divider tegas)
* **Tipografi Tinta:**
  - `--color-ink`: `#0F172A` (heading & teks utama, kontras tinggi di atas `#FFFFFF` dan `#F4F6F9`)
  - `--color-ink-secondary`: `#475569` (subjudul, label deskripsi)
  - `--color-ink-muted`: `#64748B` (metadata sekunder)
* **Identitas Institusional BPS (Terukur):**
  - `--color-bps-navy`: `#0A2540` (topbar aplikasi & penekanan otoritatif)
  - `--color-bps-cobalt`: `#0B4F9C` (aksi utama CTA, fokus input, tautan aktif)
  - `--color-bps-cobalt-hover`: `#083D7A`
  - `--color-bps-green`: `#15803D` (aksen pilar layanan B & status berhasil)
  - `--color-bps-orange`: `#C2410C` (aksen pilar layanan C & peringatan aktif)
  - `--color-bps-teal`: `#0F766E` (aksen pilar layanan D)
* **Display TV Dark Board (`/display`):**
  - `--color-tv-canvas`: `#090E17`
  - `--color-tv-surface`: `#111827`
  - `--color-tv-elevated`: `#0D1420`
  - `--color-tv-border`: `#1F2937`
  - `--color-tv-amber`: `#F59E0B` (nomor antrean prioritas keterbacaan jarak jauh)
  - `--color-tv-call`: `#EF4444` (penanda status `Dipanggil` dengan transisi border tegas, tanpa efek neon/glow berlebihan)
* **Disiplin Radius & Elevasi:**
  - Kontrol form, tombol, badge: `rounded-md` (`6px`).
  - Kartu data, tabel, dialog: `rounded-lg` (`8px`) hingga `rounded-xl` (`12px`).
  - Kartu sentuh Kiosk: maksimal `rounded-2xl` (`16px`).
  - Menghindari `rounded-3xl`, `shadow-2xl`, `hover:-translate-y-1`, `animate-bounce`, `animate-ping`, serta kartu bertumpuk tanpa batas fungsi yang jelas.
* **Pemetaan Warna Deterministik Layanan (`A`–`D` & Tambahan):**
  - `A`: Cobalt (`#0B4F9C` / tint `#EFF6FF`)
  - `B`: Emerald/Green (`#15803D` / tint `#F0FDF4`)
  - `C`: Orange/Amber (`#C2410C` / tint `#FFF7ED`)
  - `D`: Teal (`#0F766E` / tint `#F0FDFA`)
  - Prefix lain (`E` dst.): fallback deterministik ke Cobalt (`#0B4F9C` / tint `#EFF6FF`).

### 2.3 Aturan Anti-AI-Slop & Aksesibilitas
* **Nol Emoji Struktural:** Semua ikon navigasi, aksi tombol, kartu status, dan alert menggunakan komponen `<x-icon>` berbasis SVG seragam (`stroke-width="1.75"`, `aria-hidden="true"` bila mendampingi teks).
* **Fokus & Kontras:** Semua kontrol interaktif memiliki `:focus-visible` ring 2–3px kontras tinggi, `cursor-pointer` untuk elemen klik, serta mematuhi `@media (prefers-reduced-motion: reduce)` untuk menonaktifkan transisi/animasi yang tidak esensial.

---

## 3. Arsitektur Layout & Komponen Blade

### 3.1 Struktur Tiga Layout (`resources/views/layouts/`)
1. **`layouts/portal.blade.php`**
   - Dipakai oleh: `welcome.blade.php`, `auth/login.blade.php`, `auth/change-password.blade.php`, dan `errors/403|404|419|500.blade.php`.
   - Struktur: `<header>` institusional BPS Kota Tasikmalaya (opsional dapat disembunyikan/diringkas di halaman login/error via slot/prop), `<main>` terpusat, dan `<footer>` alamat kantor BPS + hak cipta.
2. **`layouts/workspace.blade.php`**
   - Dipakai oleh: `counter/index.blade.php` (`/loket`) dan seluruh halaman admin (`admin/dashboard`, `admin/reports/index`, `admin/services/index`, `admin/counters/index`, `admin/users/index`).
   - Struktur:
     - **Unified Topbar:** `<x-bps-brand>`, judul modul aktif, satu pohon `<nav aria-label="Navigasi Utama">` yang sama untuk desktop dan mobile.
     - **Navigasi Responsif:** Di layar lebar tampil inline; di layar sempit/tablet menciut ke tombol menu (`aria-expanded`, `aria-controls`) yang membuka panel/drawer navigasi tanpa duplikasi HTML link. Link halaman aktif ditandai `aria-current="page"` dengan kombinasi indikator border/latar + teks tebal (bukan warna saja).
     - **Kontrol Akun Konsisten:** Nama & peran pengguna, tautan `Ubah Kata Sandi` (`route('password.change')`) yang kini hadir konsisten di seluruh halaman admin dan loket, serta tombol `Keluar`.
     - **Kontainer Toast (`<x-toast-region>`):** Satu wilayah notifikasi terpusat di level layout.
3. **`layouts/screen.blade.php`**
   - Dipakai oleh: `kiosk/index.blade.php` dan `display/index.blade.php`.
   - Karakteristik: Full-viewport (`min-h-dvh`), properti `theme` (`light` untuk Kiosk, `dark` untuk Display TV), bebas navigasi aplikasi yang tidak perlu, dan dukungan bersih `@media print` untuk Kiosk.

### 3.2 Komponen Blade Reusable (`resources/views/components/`)
* **`<x-bps-brand>`:** Emblem SVG geometris tri-warna khas BPS + teks `BADAN PUSAT STATISTIK` / `Kota Tasikmalaya` (tanpa mengklaim atau mengubah aset logo resmi eksternal; siap diganti file gambar resmi kapan saja bila disediakan).
* **`<x-icon>`:** Peta ikon SVG terpusat (`ticket`, `tv`, `monitor`, `chart`, `shield`, `clock`, `volume-on`, `volume-off`, `search`, `download`, `document`, `plus`, `edit`, `lock`, `check`, `warning`, `close`, `menu`, `arrow-right`, `arrow-left`, `refresh`, `users`, `eye`, `eye-off`).
* **`<x-badge>`:** Badge status dan kode layanan dengan varian semantik terkendali (`neutral`, `cobalt`, `green`, `orange`, `teal`, `danger`).
* **`<x-table-wrapper>`:** Pembungkus tabel dengan `tabindex="0"`, `role="region"`, `aria-labelledby`, overflow horizontal halus, dan `thead` sticky.
* **`<x-modal>`:** Dibangun di atas elemen HTML native `<dialog>` dengan:
  - Pembukaan via `dialog.showModal()` (memanfaatkan top layer, `inert` halaman belakang, `Esc`, dan pengembalian fokus otomatis ke pemicu).
  - Atribut `closedby="any"` + fallback JS batas bounding-box (`pointerdown` dan `pointerup` di luar `getBoundingClientRect()`) untuk browser tanpa dukungan `closedby`.
  - CSS `dialog:modal { overscroll-behavior: contain; }` dan `dialog::backdrop { overflow: hidden; overscroll-behavior: contain; }` (tanpa `overflow: hidden` pada `body`), serta penataan `dialog[open]` agar tidak menimpa status tertutup.
  - Listener `close` yang mereset `dialog.returnValue = ''` serta mereset form internal bila ada (`data-reset-on-close`).
* **`<x-confirm-dialog>`:** Modal konfirmasi berbasis `<dialog>` native (menggantikan `window.confirm()`), memfokuskan aksi aman (`Batal`) secara default untuk tindakan destruktif/lewati.
* **`<x-toast-region>`:** Wilayah `<div role="region" aria-label="Notifikasi Sistem">` berisi `aria-live="polite"` + daftar toast non-intrusif dengan tombol tutup eksplisit, jeda timer otomatis saat fokus/hover berada di dalam toast, dan penundaan toast ketika `<dialog>` modal sedang terbuka.

---

## 4. Rancangan Per Halaman, Alur Data & Penanganan Error

### 4.1 Portal Beranda (`/`, `welcome.blade.php`)
* **Tata Letak 2 Zona:**
  - **Hero Ringkas:** Judul `Pelayanan Statistik Terpadu (PST)` — `Badan Pusat Statistik Kota Tasikmalaya`, penjelasan ringkas alur layanan, dan strip status jam operasional (`Senin–Jumat, 08.00–15.30 WIB`) yang statis dan jelas tanpa animasi `animate-pulse`.
  - **Zona 1 — Layanan Publik & Ruang Tunggu (Prioritas Utama):** 2 kartu besar berstruktur bersih untuk `Anjungan Kiosk Tiket` (`/kiosk`) dan `Layar Monitor TV` (`/display`).
  - **Zona 2 — Operasional Staf & Pimpinan (Sekunder):** 2 kartu lebih ringkas dengan badge akses staf untuk `Meja Kerja Pelayanan` (`/loket`) dan `Dashboard & Rekap` (`/admin/dashboard`).
  - **4 Pilar Layanan PST (A–D):** Grid 4 kolom berbatas garis `1px` menampilkan kode kotak mono `A`/`B`/`C`/`D`, judul layanan, dan ringkasan cakupan.
  - **Strip Kepatuhan UU PDP:** Keterangan ringkas jaminan kerahasiaan data berdasarkan `UU No. 27 Tahun 2022`.
* **Keutuhan Test:** Mempertahankan seluruh teks yang diuji di `LandingAndErrorPagesTest`.

### 4.2 Anjungan Kiosk Layar Sentuh (`/kiosk`, `kiosk/index.blade.php`)
* **Tampilan Layar Sentuh:** Header solid BPS Navy dengan garis aksen tri-warna tipis di tepi bawah, jam & tanggal `tabular-nums`, judul instruksi jelas, dan grid layanan `2×2` berdimensi tombol besar (`min-h-[140px]`), kode huruf `A/B/C/D` besar berkontras tinggi, serta efek tekan (`active:bg-slate-50`) tanpa `scale` yang menggeser batas layout.
* **Alur Tiket & Modal:**
  - Klik layanan menonaktifkan semua tombol layanan sementara, memunculkan overlay pemrosesan (`<dialog>` atau overlay non-geser), lalu memanggil `POST /kiosk/ticket`.
  - Jika sukses: mengisi `#ticket-print-area` dan dialog `#ticket-modal` (`<dialog>`), memicu `window.print()` saat `!ticket.mock_print`, dan menjalankan hitung mundur 5 detik (`tabular-nums`) dengan tombol `Selesai / Kembali Sekarang`.
  - Jika gagal/timeout: menampilkan dialog `#error-modal` dengan tombol `Tutup & Coba Lagi`.
* **Perbaikan Cetak Termal (`58mm` / `80mm`):**
  - Menghapus class Tailwind `hidden` dari `#ticket-print-area`.
  - Memakai class `.print-only` dengan aturan CSS:
    - `@media screen { .print-only { display: none !important; } }`
    - `@media print { body > *:not(#ticket-print-area) { display: none !important; } #ticket-print-area { display: block !important; position: static; width: 58mm; max-width: 100%; padding: 3mm 2mm; font-family: 'Courier New', monospace; color: #000; background: #fff; } }`
  - Memastikan printer termal tidak lagi menerima halaman kosong.

### 4.3 Layar Monitor TV (`/display`, `display/index.blade.php`)
* **Header TV:** Logo BPS, judul `BADAN PUSAT STATISTIK` + label `PST KOTA TASIKMALAYA`, tombol `Aktifkan Suara TV` (desain tombol jelas kontras tinggi tanpa `animate-bounce`) yang berubah menjadi badge `Audio Aktif`, indikator status koneksi SSE/Polling, dan jam digital `tabular-nums`.
* **Grid Utama 2:1:**
  - **Kolom Kiri (2/3):** Grid kartu loket (`Loket 1`, `Loket 2`, dst.) dengan nomor antrean super besar (`text-6xl xl:text-7xl font-mono tabular-nums`), nama layanan, dan badge status (`Menunggu`, `Dipanggil`, `Melayani`). Kartu berstatus `Dipanggil` memakai border kontras `#EF4444` dan latar aksen lembut agar terbaca jelas dari jauh tanpa animasi `scale`/blur berlebih. Di bawah kartu loket tampil **Ringkasan Antrean Menunggu per Layanan (A–D)**.
  - **Kolom Kanan (1/3):** Panel `Riwayat 5 Panggilan Terakhir` berisi kartu ringkas tiket, nama layanan, loket tujuan, dan jam panggil.
* **Ticker Footer & Penyembunyian Panel Debug:**
  - Footer menampilkan label `INFO PST BPS` dan teks berjalan halus memakai CSS `@keyframes` marquee yang berhenti otomatis dan membungkus rapi saat `prefers-reduced-motion: reduce`.
  - Panel Uji Coba Audio disembunyikan secara default dari ruang tunggu publik; dapat dibuka petugas/teknisi lewat tombol ikon tersembunyi/minimal di sudut kanan bawah atau pintasan keyboard `Alt + D`.
* **Sinkronisasi State & Keamanan DOM pada Display TV:**
  - Saat menerima event `initial-state` (SSE) maupun respons `pollUpdates`: seluruh kartu loket terlebih dahulu di-reset ke state idle (`---`, `Belum ada antrean`, badge `Menunggu`), baru kemudian diisi ulang berdasarkan `active_queues`, sehingga loket yang sudah `completed` atau `skipped` otomatis bersih kembali ke `---`.
  - Pada mode fallback `pollUpdates`, daftar `recent-calls-list` ikut disinkronkan penuh dari `data.recent_queues`, sedangkan event `queue-called` menyisipkan entri baru memakai konstruksi node DOM (`document.createElement` + `textContent`) alih-alih interpolasi string `innerHTML`.
  - Membatasi ukuran Set `processedCallKeys` (misal menyimpan maksimal 200 kunci terbaru) agar memori browser TV tidak membengkak saat menyala seharian.

### 4.4 Meja Kerja Pelayanan Loket (`/loket`, `counter/index.blade.php`)
* **Header & Selector Loket:** Memakai `layouts/workspace` dengan dropdown pemilih `Loket Bertugas` (`counter_id`) di sub-toolbar/header agar konsisten dengan navigasi admin (bagi role `admin`, tautan kembali ke Dashboard Admin tetap tersedia di navigasi utama).
* **Alur Kerja 3 Tahap & Panel Kiri (1/3):**
  - Indikator tahap aktif yang jelas: `1. Panggil` → `2. Layani & Buku Tamu` → `3. Selesai`.
  - Info layanan yang ditugaskan ke loket + jumlah antrean menunggu.
  - Kartu antrean aktif (`---` atau kode tiket + badge status + hitungan `Panggilan Ulang: x/3`), tombol `Panggil Berikutnya`, `Panggil Ulang (x/3)`, `Lewati (Skip)`, dan `Mulai Melayani (Buka Buku Tamu)`.
  - Kartu `Antrean Terlewat Hari Ini` (jika ada `$skippedQueues`) dengan tombol `Panggil Kembali`.
* **Formulir Buku Tamu Digital (Kolom Kanan 2/3) & Penanganan Error:**
  - Semua field (`full_name`, `phone_number`, `email`, `institution_type`, `institution_name`, `data_needs_description`, `pdp_consent`) menggunakan komponen input seragam, penanda wajib `*`, dan slot pesan error inline (`aria-describedby`).
  - Mengganti seluruh `alert()` / `confirm()` dengan:
    - `<x-confirm-dialog>` untuk konfirmasi `Lewati (Skip)` dan `Panggil Kembali Antrean Terlewat`.
    - `<x-toast-region>` untuk notifikasi sukses/info (`Recall dikirim`, `Tidak ada antrean menunggu`, dll.).
    - Banner ringkasan error fokusabel (`<div id="guestbook-error-summary" role="alert" tabindex="-1">`) di atas form buku tamu jika server mengembalikan HTTP `422` (`errors` per field ditampilkan tepat di bawah masing-masing input + tautan menuju field terkait).
  - Setiap tombol aksi AJAX menampilkan status loading/disabled selama `fetch()` berjalan untuk mencegah klik ganda.

### 4.5 Admin Console (`/admin/*`) & PDF (`admin/reports/pdf.blade.php`)
* **Dashboard (`/admin/dashboard`):**
  - Peringatan kata sandi default (jika `$isUsingDefaultPassword`) tampil sebagai banner terstruktur dengan border aksen kiri oranye dan tombol aksi `Ubah Kata Sandi`.
  - Toolbar filter Bulan & Tahun dengan label terlihat dan tombol `Terapkan Filter`.
  - 4 Kartu KPI (`Total Kunjungan`, `Selesai Dilayani`, `Rerata Waktu Tunggu`, `Rerata Durasi Layanan`) dengan tinggi seragam, angka `tabular-nums`, dan indikator MoM (`+100%` dll.) dengan ikon panah SVG + teks (bukan warna saja).
  - 4 Grafik Chart.js lokal (`public/vendor/chartjs/chart.umd.js`) memakai palet warna terkurasi (`#0B4F9C`, `#15803D`, `#C2410C`, `#0F766E`, `#475569`), gridline horizontal halus `#E2E8F0`, dan font seragam.
  - Tabel 10 tamu terakhir terbungkus `<x-table-wrapper>` + kolom kebutuhan data menampilkan teks penuh via tooltip/detail klik baris tanpa memotong informasi secara permanen.
* **Rekap Laporan (`/admin/reports`):**
  - Filter tanggal mulai, tanggal selesai, layanan, kategori instansi, dan pencarian teks terpadu dalam grid responsif.
  - Tombol ekspor `Ekspor CSV` dan `Ekspor PDF Resmi` dengan ikon SVG bersih.
  - Tabel menggunakan pemetaan label kategori instansi terbaca (`pendidikan` → `Pendidikan / Riset`, `pemerintah` → `Instansi Pemerintah`, `swasta` → `Sektor Swasta`, `lsm` → `LSM / Media`, `umum` → `Masyarakat Umum`).
  - Paginasi Laravel memakai template khusus `resources/views/vendor/pagination/tailwind.blade.php` yang otomatis dipakai oleh `{{ $visitors->links() }}` agar tombol halaman memakai token warna BPS Cobalt dan radius `rounded-md`.
* **Manajemen Layanan, Loket, dan Pengguna (`/admin/services`, `/admin/counters`, `/admin/users`):**
  - Mengganti seluruh modal `div.hidden` dengan `<x-modal>` berbasis `<dialog>` native.
  - Mengganti pemanggilan `onclick="openEditModal(..., '{{ addslashes(...) }}')"` dengan atribut `data-payload='@json(...)'` pada tombol edit/reset agar aman dari karakter kutip, baris baru, dan injeksi.
  - Menampilkan pesan flash `session('success')` dan `$errors->any()` dengan gaya banner konsisten dan `role="alert"` / `role="status"`.
* **Dokumen Cetak PDF (`admin/reports/pdf.blade.php`):**
  - Tetap berdiri sendiri tanpa Vite (syarat DomPDF). Dirapikan proporsi logo Kop Dinas BPS, penataan badge kategori instansi agar terbaca penuh (`Pendidikan / Riset`, dll.), dan spasi antar kolom tabel.

### 4.6 Autentikasi & Halaman Error (`/login`, `/password/change`, `errors/*`)
* Menggunakan `layouts/portal.blade.php` dalam mode ringkas terpusat.
* Form login & ubah kata sandi dilengkapi atribut `autocomplete` yang tepat (`username`, `current-password`, `new-password`), tombol toggle lihat/sembunyikan kata sandi dengan `aria-pressed` + `aria-label`, dan ringkasan error yang jelas.
* Halaman `403`, `404`, `419`, `500` memakai kartu bersih bergaris aksen status tipis, ikon SVG geometris, serta mempertahankan seluruh salinan teks Bahasa Indonesia dan tautan navigasi yang diuji di `LandingAndErrorPagesTest`.

---

## 5. Rencana Build, Pengujian & Verifikasi

### 5.1 Build Aset & Lingkungan
1. Menjalankan `npm install` dan `npm run build` via Distrobox (`distrobox enter my-distrobox`) untuk memastikan Vite + Tailwind CSS v4 mengompilasi `public/build/` tanpa error.
2. Pada `tests/TestCase.php`, menambahkan pengaman di `setUp()` agar jika berkas `public/build/manifest.json` belum tersedia di lingkungan pengujian tertentu, pengujian tetap berjalan memakai `$this->withoutVite()` — sementara di lingkungan kerja normal dengan `public/build` ter-build, pengujian ikut memverifikasi kompilasi `@vite` secara nyata.
3. Memeriksa status `AGENTS.md` (terkait prasyarat Laravel Boost) pada fase persiapan implementasi sebelum mengubah kode aplikasi.

### 5.2 Cakupan Verifikasi Otomatis & Manual
1. **Regresi Test Suite:** Menjalankan seluruh suite PHPUnit (`distrobox enter my-distrobox -- php artisan test`) dan memastikan seluruh 42 test existing tetap lulus tanpa perubahan assertion kontrak bisnis.
2. **Penambahan Feature Test UI:** Menambah/memperluas test (misal di `LandingAndErrorPagesTest` atau `UiArchitectureTest`) untuk memverifikasi:
   - Tidak ada lagi referensi `cdn.tailwindcss.com` maupun karakter emoji struktural pada respons HTML view utama (`/`, `/kiosk`, `/display`, `/login`, `/loket`, `/admin/dashboard`, `/admin/reports`, `/admin/services`, `/admin/counters`, `/admin/users`, dan `403`/`404`).
   - Kehadiran penanda CSS/DOM baru (`@vite` asset link, elemen `<dialog>`, atribut `aria-current="page"` pada navigasi aktif admin, serta tautan `password.change` yang konsisten di halaman admin).
   - Validasi render `exportPdf` tetap menghasilkan PDF valid (`%PDF-`).
3. **Verifikasi Build & Browser/DOM:** Memastikan `npm run build` sukses tanpa warning CSS/Tailwind yang belum terselesaikan, serta memeriksa tampilan dan responsivitas halaman kunci (`375px` mobile, `1024px` desktop, `1920x1080` TV/Kiosk).
