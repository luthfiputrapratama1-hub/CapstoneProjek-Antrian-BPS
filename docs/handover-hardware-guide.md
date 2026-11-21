# Catatan Serah Terima Fase Perangkat Keras (Hardware Handover Guide)
### Sistem Informasi Antrean & Rekapitulasi Pelayanan Statistik Terpadu (PST) BPS Kota Tasikmalaya

Dokumen ini disusun sebagai panduan teknis dan operasional untuk mengintegrasikan perangkat keras fisik (printer termal tiket, layar display TV ruang tunggu, sistem tata suara, dan anjungan *kiosk touchscreen*) pada saat sistem diterapkan di lingkungan kantor BPS Kota Tasikmalaya.

---

## 1. Arsitektur Kesiapan Perangkat Keras (*Hardware Readiness*)

Sistem web antrean ini telah dirancang secara *hardware-agnostic* (siap dipasangkan dengan perangkat fisik tanpa mengubah logika bisnis inti). Seluruh antarmuka telah mengisolasi lapisan komunikasi hardware:

```
┌────────────────────────────────────────────────────────┐
│                   APLIKASI LARAVEL                     │
│  - Kiosk Controller & Virtual Print Modal              │
│  - Display Controller (SSE Stream & Polling Fallback)  │
│  - Counter Controller (FIFO Call & Mutex Lock)         │
└───────────────┬────────────────────────┬───────────────┘
                │                        │
       [window.print()]           [Web Audio API / SSE]
                │                        │
┌───────────────▼────────┐      ┌────────▼───────────────┐
│     PRINTER TERMAL     │      │     LAYAR TV & AUDIO   │
│ - 58 mm / 80 mm ESC/POS│      │ - TV 16:9 HDMI         │
│ - Chromium Silent Print│      │ - Audio FIFO Queue     │
└────────────────────────┘      └────────────────────────┘
```

---

## 2. Integrasi Printer Termal Tiket (Anjungan Kiosk)

### A. Pengaturan Perangkat Lunak (.env)
Pada berkas `.env` di komputer anjungan Kiosk, ubah mode pratinjau tiket virtual menjadi cetak fisik:
```env
# Matikan mode simulasi preview agar langsung memicu window.print()
KIOSK_MOCK_PRINT=false
```

### B. Spesifikasi Kertas & Layout Cetak
Sistem telah dilengkapi dengan CSS `@media print` yang secara otomatis mengisolasi elemen `#ticket-print-area`:
- **Ukuran Lebar Kertas**: Kompatibel dengan lebar standar **58 mm** maupun **80 mm**.
- **Konten Tiket**:
  - Kop Instansi: `BADAN PUSAT STATISTIK KOTA TASIKMALAYA - PST`
  - Nama Layanan (A, B, C, atau D)
  - Kode Nomor Antrean Besar (font monospace `Courier New` dengan ukuran 28pt tebal)
  - Waktu Pengambilan: Tanggal dan Jam
  - Informasi Antrean di Depan: *"Menunggu di depan Anda: X orang"*
  - Catatan Kaki: *"Silakan menunggu panggilan nomor Anda"*

### C. Mode Cetak Otomatis Tanpa Dialog (*Silent Kiosk Printing*)
Agar pengunjung tidak perlu menekan tombol "Print" pada dialog browser, gunakan browser Google Chrome / Chromium dengan flag *kiosk printing*:

#### Pada Sistem Operasi Windows:
1. Pasang driver printer termal USB (misal: Xprinter, Epson TM-T82, Posiflex) dan jadikan sebagai **Default Printer** di *Settings > Devices > Printers & Scanners*.
2. Buat pintasan (*shortcut*) Google Chrome di Desktop, klik kanan > **Properties**, pada kolom **Target** tambahkan parameter:
   ```cmd
   "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk --kiosk-printing --disable-pinch --overscroll-history-navigation=0 http://192.168.1.100/kiosk
   ```
3. Saat tombol layanan disentuh oleh pengunjung di Kiosk, tiket akan langsung dicetak secara otomatis dan kertas terpotong (*auto-cut*), kemudian layar Kiosk mereset kembali ke beranda dalam waktu 5 detik.

#### Pada Sistem Operasi Linux (Ubuntu / Raspberry Pi Kiosk):
Buat skrip `start-kiosk.sh`:
```bash
#!/bin/bash
xset s noblank
xset s off
xset -dpms
chromium-browser --noerrdialogs --disable-infobars --kiosk --kiosk-printing http://192.168.1.100/kiosk
```

---

## 3. Sistem Audio & Layar Display TV Ruang Tunggu

### A. Kebijakan Browser Autoplay (*Audio Gesture Lock*)
Sebagian besar browser modern (Chrome, Edge, Firefox) secara ketat memblokir audio yang diputar secara otomatis sebelum ada interaksi pengguna (*click gesture*) pada halaman tersebut.
- **Solusi yang Diterapkan**: Halaman Display TV (`/display`) dilengkapi tombol **"AKTIFKAN SUARA TV"** berukuran mencolok dengan animasi berkedip.
- **SOP Operator**: Saat komputer TV ruang tunggu dinyalakan setiap pagi, operator cukup mengklik tombol tersebut satu kali untuk membuka kunci Web Audio API. Setelah itu, seluruh panggilan loket sepanjang hari akan berbunyi secara otomatis tanpa perlu interaksi lagi.

### B. Antrean Pemutaran Suara Bebas Benturan (*FIFO Audio Queue*)
Untuk mencegah benturan audio ketika dua loket pelayanan menekan tombol "Panggil Berikutnya" pada detik yang hampir bersamaan, JavaScript pada `/display` dilengkapi dengan pengunci antrean:
```javascript
let audioPlaybackQueue = [];
let isProcessingQueue = false;

function enqueueAudioCall(payload) {
    audioPlaybackQueue.push(payload);
    processNextAudioCall();
}
```
Panggilan kedua akan mengantre dan baru dibunyikan setelah panggilan pertama selesai seluruhnya.

### C. Payload Panggilan SSE yang Disediakan Backend
Event `call_event` dari backend Laravel menyediakan payload terstruktur lengkap:
```json
{
  "ticket_code": "A-001",
  "code_prefix": "A",
  "ticket_number": 1,
  "counter_number": 1,
  "counter_name": "Loket 1",
  "service_name": "Konsultasi Statistik"
}
```

### D. Opsi Penggantian Suara Sintetis ke File Rekaman Manusia
Saat ini suara menggunakan kombinasi *Ding-Dong chime* frekuensi Web Audio API dan synthesizer suara bahasa Indonesia (`SpeechSynthesisUtterance`).

Bila kantor BPS ingin meningkatkan ke rekaman suara penyiar resmi (*human voice artist*), siapkan folder `public/audio/voice/` dengan berkas WAV/MP3 berikut:
1. `chime.wav` (Nada bel pembuka)
2. `nomor-antrean.wav` ("Nomor antrean")
3. `huruf-a.wav`, `huruf-b.wav`, `huruf-c.wav`, `huruf-d.wav`
4. `0.wav` s.d. `9.wav`, `belas.wav`, `puluh.wav`, `ratus.wav`
5. `menuju-loket.wav` ("Silakan menuju ke")
6. `loket-1.wav`, `loket-2.wav`, `loket-3.wav`, `loket-4.wav`

Pola pemutaran sekuensial tinggal memanggil fungsi `playAudioChain(['chime.wav', 'nomor-antrean.wav', 'huruf-a.wav', '0.wav', '0.wav', '1.wav', 'menuju-loket.wav', 'loket-1.wav'])`.

---

## 4. Konfigurasi Jaringan LAN & Kapasitas Peladen (Web Server)

### A. Karakteristik Server-Sent Events (SSE)
Metode SSE menjaga satu koneksi HTTP tetap terbuka (`keep-alive`) antara peramban Display TV dengan peladen Laravel:
- **Penting**: Server pengembangan bawaan `php artisan serve` secara default bersifat *single-threaded*. Jika satu layar TV membuka koneksi stream, thread tersebut akan terkunci sehingga request dari loket atau kiosk dapat terhambat.
- **Solusi untuk Pengujian Multi-Klien di Lingkungan Lokal**:
  Jalankan server dengan parameter multi-worker:
  ```bash
  PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000
  ```
- **Fallback Polling Cerdas**:
  Bila koneksi SSE terputus karena batas *timeout* jaringan LAN, `/display` secara otomatis beralih ke interval *polling* 3 detik ke endpoint `/display/poll-updates`, dan akan otomatis menyambung kembali ke SSE begitu jaringan stabil.

### B. Rekomendasi Peladen Produksi di Kantor BPS
Untuk penerapan permanen pada server lokal BPS (PC Server / Mini PC):
1. **Sistem Operasi**: Linux Ubuntu Server 22.04 LTS atau Windows Server.
2. **Web Server**: **Nginx** dipasangkan dengan **PHP-FPM 8.2+**.
3. **Konfigurasi Nginx untuk SSE**:
   Pastikan *buffering* dimatikan khusus untuk endpoint `/display/stream` agar pengumuman antrean terkirim instan tanpa jeda buffer:
   ```nginx
   location /display/stream {
       proxy_pass http://127.0.0.1:9000;
       proxy_http_version 1.1;
       proxy_set_header Connection '';
       proxy_buffering off;
       proxy_cache off;
       chunked_transfer_encoding off;
   }
   ```
4. **Alokasi IP Statis Kantor**:
   Tetapkan IP statis pada peladen lokal BPS (misal: `192.168.1.100`), sehingga seluruh perangkat klien dapat mengakses secara konsisten:
   - Kiosk: `http://192.168.1.100/kiosk`
   - TV Display: `http://192.168.1.100/display`
   - Meja Loket: `http://192.168.1.100/loket`
   - Admin: `http://192.168.1.100/admin/dashboard`

---

## 5. Pengamanan Fisik & Mode Kiosk (*Enclosure Lockdown*)

Untuk anjungan mandiri yang diletakkan di ruang pelayanan publik:
1. **Proteksi Keyboard & Mouse**:
   - Gunakan layar sentuh tanpa keyboard fisik terbuka bagi pengunjung.
   - Nonaktifkan tombol kombinasi sistem operasi (seperti `Alt + F4`, `Ctrl + Alt + Del`, `F12 / DevTools`, `Ctrl + N`) melalui *Group Policy Object* (GPO) di Windows atau *Assigned Access (Single App Kiosk)*.
2. **Auto-Start & Power-On Resume**:
   - Konfigurasikan BIOS komputer Kiosk dan TV Display pada pengaturan:
     `AC Power Recovery / Restore on AC Power Loss = Power On`
   - Tambahkan *shortcut* browser kiosk pada folder `shell:startup` di Windows agar saat listrik kantor menyala di pagi hari, Kiosk dan Display langsung siap beroperasi otomatis.

---
*Dokumen ini merupakan bagian integral dari serah terima proyek Capstone Sistem Informasi Antrean & Rekapitulasi PST BPS Kota Tasikmalaya © 2026.*
