<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Anjungan Tiket Mandiri - PST BPS Kota Tasikmalaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Format Cetak Struk Printer Termal (58mm / 80mm ESC/POS) */
        @media print {
            body * {
                visibility: hidden !important;
            }
            #ticket-print-area, #ticket-print-area * {
                visibility: visible !important;
            }
            #ticket-print-area {
                position: fixed;
                left: 0;
                top: 0;
                width: 58mm; /* Ukuran standar, adaptif jika kertas 80mm */
                max-width: 100%;
                padding: 4mm 2mm;
                font-family: 'Courier New', Courier, monospace;
                color: #000;
                background: #fff;
                font-size: 11px;
                line-height: 1.3;
            }
            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none font-sans select-none">
    <!-- Header Anjungan Kiosk -->
    <header class="bg-blue-900 text-white shadow-lg py-5 px-8 flex justify-between items-center border-b-4 border-amber-400">
        <div class="flex items-center space-x-5">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-blue-900 font-black text-3xl shadow-md border-2 border-blue-200 hover:scale-105 transition-transform duration-150">
                BPS
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-wide uppercase">BADAN PUSAT STATISTIK</h1>
                <p class="text-blue-200 text-sm md:text-base font-semibold">Pelayanan Statistik Terpadu (PST) Kota Tasikmalaya</p>
            </div>
        </div>
        <div class="text-right bg-blue-950/60 border border-blue-700/60 px-5 py-2.5 rounded-2xl shadow-inner">
            <div id="live-clock" class="text-3xl font-mono font-black tracking-wider text-emerald-400">--:--:--</div>
            <div id="live-date" class="text-blue-200 text-xs font-semibold mt-0.5 tracking-wide">--</div>
        </div>
    </header>

    <!-- Main Content Layar Sentuh -->
    <main class="max-w-6xl mx-auto px-6 py-8 flex-1 flex flex-col justify-center w-full">
        <div class="text-center mb-8">
            <span class="inline-block px-4 py-1 rounded-full bg-blue-100 text-blue-900 font-extrabold text-xs uppercase tracking-widest mb-2 border border-blue-200">
                Anjungan Mandiri Layar Sentuh
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800">SELAMAT DATANG DI PST BPS</h2>
            <p class="text-slate-600 text-base md:text-lg mt-2 font-medium">Sentuh salah satu kategori layanan di bawah ini untuk mengambil tiket antrean Anda</p>
        </div>

        <!-- Tombol Layanan Touchscreen -->
        <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($services as $service)
                <button type="button"
                    id="btn-service-{{ $service->id }}"
                    onclick="takeTicket({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ $service->code_prefix }}')"
                    class="service-btn group relative bg-white hover:bg-blue-50/80 active:scale-[0.98] border-2 border-slate-200 hover:border-blue-600 focus:border-blue-600 rounded-3xl p-6 shadow-md hover:shadow-2xl transition-all duration-150 flex items-center space-x-6 text-left cursor-pointer">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-700 to-indigo-900 text-white flex items-center justify-center font-black text-4xl shadow-md group-hover:scale-105 transition-transform duration-150 shrink-0">
                        {{ $service->code_prefix }}
                    </div>
                    <div class="flex-1">
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full uppercase tracking-wider mb-1.5">
                            Kategori {{ $service->code_prefix }}
                        </span>
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 group-hover:text-blue-900 transition-colors">
                            {{ $service->name }}
                        </h3>
                        <p class="text-slate-500 text-sm mt-1 line-clamp-2">
                            {{ $service->description ?? 'Layanan konsultasi dan informasi statistik terpadu.' }}
                        </p>
                    </div>
                </button>
            @empty
                <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-slate-200 p-8">
                    <p class="text-slate-500 text-lg">Belum ada layanan aktif yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 px-8 text-center text-slate-500 text-xs flex justify-between items-center shadow-inner">
        <span class="font-medium">© 2026 Badan Pusat Statistik Kota Tasikmalaya - PST Siap Melayani dengan HATI</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
            ● Anjungan Siap Operasional
        </span>
    </footer>

    <!-- Overlay Loading State (Mencegah Tekan Ganda) -->
    <div id="loading-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden flex-col items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl p-8 shadow-2xl flex flex-col items-center max-w-xs w-full text-center border border-slate-100 animate-pulse">
            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-900 rounded-full animate-spin mb-4"></div>
            <h3 class="text-lg font-black text-slate-800">Mencetak Tiket...</h3>
            <p class="text-xs text-slate-500 mt-1">Mohon tunggu sebentar, sistem sedang memproses nomor antrean Anda.</p>
        </div>
    </div>

    <!-- Virtual Mock Ticket Modal -->
    <div id="ticket-modal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl font-black shadow-inner">
                ✓
            </div>
            <h3 class="text-2xl font-black text-slate-800">Tiket Diterbitkan!</h3>
            <p class="text-xs text-slate-500 mt-1">Silakan ambil struk cetak tiket Anda di bawah layar</p>

            <!-- Struk Virtual Mock Preview -->
            <div class="my-5 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl p-5 text-left font-mono text-xs text-slate-700 shadow-inner">
                <div class="text-center font-bold text-xs pb-3 border-b border-dashed border-slate-300">
                    BADAN PUSAT STATISTIK<br>
                    <span class="font-normal text-[11px] text-slate-500">PST BPS KOTA TASIKMALAYA</span>
                </div>
                <div class="text-center my-4">
                    <div class="text-[11px] font-sans font-bold text-slate-400 uppercase tracking-wider">Nomor Antrean</div>
                    <div id="modal-ticket-code" class="text-5xl font-mono font-black text-blue-900 my-1 tracking-wider">A-001</div>
                    <div id="modal-service-name" class="font-sans font-bold text-slate-700 text-sm mt-1">Konsultasi Statistik</div>
                </div>
                <div class="border-t border-dashed border-slate-300 pt-3 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Waktu Kedatangan:</span>
                        <span id="modal-ticket-time" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 bg-amber-50 p-1.5 rounded-lg border border-amber-200">
                        <span>Antrean di Depan:</span>
                        <span id="modal-ticket-ahead" class="text-amber-800">0 Orang</span>
                    </div>
                </div>
                <div class="text-center text-[10px] text-slate-500 mt-3 pt-2 border-t border-dashed border-slate-300 font-sans">
                    Harap menunggu panggilan nomor Anda di ruang tunggu.
                </div>
            </div>

            <!-- Auto Close Countdown Bar -->
            <div class="mb-4">
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div id="countdown-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-1000" style="width: 100%;"></div>
                </div>
                <div class="text-[11px] text-slate-500 mt-1.5 font-medium">
                    Kembali ke layar utama dalam <span id="countdown-number" class="font-bold text-blue-900">5</span> detik
                </div>
            </div>

            <button type="button" onclick="closeModal()"
                class="w-full bg-blue-900 hover:bg-blue-800 active:scale-[0.99] text-white font-bold py-3.5 rounded-2xl transition shadow-lg text-sm">
                Selesai / Kembali Sekarang
            </button>
        </div>
    </div>

    <!-- Modal Error Ramah Pengguna -->
    <div id="error-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-red-200">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl font-black">
                ⚠️
            </div>
            <h3 class="text-xl font-black text-slate-800">Pemberitahuan</h3>
            <p id="error-modal-message" class="text-xs text-slate-600 my-4 leading-relaxed">
                Gagal memproses tiket antrean. Silakan coba kembali atau hubungi petugas.
            </p>
            <button type="button" onclick="closeErrorModal()"
                class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 rounded-2xl transition text-sm">
                Tutup & Coba Lagi
            </button>
        </div>
    </div>

    <!-- Container Cetak Struk Fisik (58mm / 80mm ESC/POS via @media print) -->
    <div id="ticket-print-area" class="hidden">
        <div style="text-align: center; font-weight: bold; padding-bottom: 4px; border-bottom: 1px dashed #000;">
            BADAN PUSAT STATISTIK<br>
            PST BPS KOTA TASIKMALAYA
        </div>
        <div style="text-align: center; margin: 10px 0;">
            <div style="font-size: 10px; font-weight: bold;">NOMOR ANTREAN</div>
            <div id="print-ticket-code" style="font-size: 32px; font-weight: 900; margin: 4px 0; letter-spacing: 1px;">A-001</div>
            <div id="print-service-name" style="font-size: 12px; font-weight: bold;">Konsultasi Statistik</div>
        </div>
        <div style="border-top: 1px dashed #000; padding-top: 6px; font-size: 10px; line-height: 1.4;">
            <div>Waktu: <span id="print-ticket-time">--/--/---- --:-- WIB</span></div>
            <div>Antrean di Depan: <span id="print-ticket-ahead">0 Orang</span></div>
        </div>
        <div style="text-align: center; font-size: 9px; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px;">
            Silakan menunggu hingga nomor Anda dipanggil di meja loket.<br>
            Terima kasih atas kunjungan Anda.
        </div>
    </div>

    <script>
        // Jam digital realtime
        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('live-date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        let isSubmitting = false;
        let countdownInterval = null;
        let remainingSeconds = 5;

        function setButtonsDisabled(disabled) {
            const buttons = document.querySelectorAll('.service-btn');
            buttons.forEach(btn => {
                btn.disabled = disabled;
                if (disabled) {
                    btn.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    btn.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }

        async function takeTicket(serviceId, serviceName, codePrefix) {
            // Cegah klik ganda
            if (isSubmitting) return;
            isSubmitting = true;
            setButtonsDisabled(true);

            // Tampilkan loading overlay
            document.getElementById('loading-overlay').classList.remove('hidden');
            document.getElementById('loading-overlay').classList.add('flex');

            try {
                const response = await fetch("{{ route('kiosk.ticket') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ service_id: serviceId })
                });

                const result = await response.json();

                // Sembunyikan loading
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('loading-overlay').classList.remove('flex');

                if (response.ok && result.status === 'success') {
                    const ticket = result.data;

                    // Isi modal preview
                    document.getElementById('modal-ticket-code').innerText = ticket.ticket_code;
                    document.getElementById('modal-service-name').innerText = ticket.service_name;
                    document.getElementById('modal-ticket-time').innerText = ticket.created_at;
                    document.getElementById('modal-ticket-ahead').innerText = `${ticket.waiting_ahead} Orang`;

                    // Isi kontainer cetak fisik termal
                    document.getElementById('print-ticket-code').innerText = ticket.ticket_code;
                    document.getElementById('print-service-name').innerText = ticket.service_name;
                    document.getElementById('print-ticket-time').innerText = ticket.created_at;
                    document.getElementById('print-ticket-ahead').innerText = `${ticket.waiting_ahead} Orang`;

                    // Tampilkan modal tiket
                    const modal = document.getElementById('ticket-modal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // Jika bukan mode mock print (hardware printer termal fisik terhubung), picu dialog cetak otomatis
                    if (!ticket.mock_print) {
                        try {
                            window.print();
                        } catch (e) {
                            console.warn('Silent print invocation:', e);
                        }
                    }

                    // Mulai countdown reset otomatis 5 detik
                    startCountdown();
                } else {
                    showError(result.message || 'Gagal menerbitkan nomor antrean. Silakan coba kembali.');
                }
            } catch (err) {
                console.error(err);
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('loading-overlay').classList.remove('flex');
                showError('Terjadi kendala jaringan saat berkomunikasi dengan peladen sistem antrean.');
            }
        }

        function startCountdown() {
            clearInterval(countdownInterval);
            remainingSeconds = 5;
            const progressEl = document.getElementById('countdown-progress');
            const numberEl = document.getElementById('countdown-number');

            numberEl.innerText = remainingSeconds;
            progressEl.style.width = '100%';

            countdownInterval = setInterval(() => {
                remainingSeconds--;
                if (numberEl) numberEl.innerText = remainingSeconds;
                if (progressEl) progressEl.style.width = `${(remainingSeconds / 5) * 100}%`;

                if (remainingSeconds <= 0) {
                    clearInterval(countdownInterval);
                    closeModal();
                }
            }, 1000);
        }

        function closeModal() {
            clearInterval(countdownInterval);
            const modal = document.getElementById('ticket-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Pulihkan kembali tombol layanan
            isSubmitting = false;
            setButtonsDisabled(false);
        }

        function showError(message) {
            document.getElementById('error-modal-message').innerText = message;
            const errorModal = document.getElementById('error-modal');
            errorModal.classList.remove('hidden');
            errorModal.classList.add('flex');
        }

        function closeErrorModal() {
            const errorModal = document.getElementById('error-modal');
            errorModal.classList.add('hidden');
            errorModal.classList.remove('flex');
            isSubmitting = false;
            setButtonsDisabled(false);
        }
    </script>
</body>
</html>
