@extends('layouts.screen', ['screenTheme' => 'light'])

@section('title', 'Anjungan Tiket Mandiri — PST BPS Kota Tasikmalaya')

@php
    $servicePalette = [
        'A' => ['badge' => 'bg-[#0B4F9C] text-white', 'label' => 'text-[#0B4F9C]'],
        'B' => ['badge' => 'bg-[#15803D] text-white', 'label' => 'text-[#15803D]'],
        'C' => ['badge' => 'bg-[#C2410C] text-white', 'label' => 'text-[#C2410C]'],
        'D' => ['badge' => 'bg-[#0F766E] text-white', 'label' => 'text-[#0F766E]'],
    ];
@endphp

@section('content')
    <header class="bg-[#0A2540] text-white border-b border-white/10">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <x-bps-brand
                title="BADAN PUSAT STATISTIK"
                subtitle="Pelayanan Statistik Terpadu (PST) Kota Tasikmalaya"
                :clickable="false"
                :dark="true"
                size="lg"
            />
            <div class="text-right rounded-lg border border-white/15 bg-white/5 px-4 py-2 tabular-nums-strict">
                <div id="live-clock" class="text-2xl font-mono font-bold tracking-wider text-white">--:--:--</div>
                <div id="live-date" class="text-xs text-slate-300">--</div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 flex-1 flex flex-col justify-center w-full">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-[#DCE3EC] pb-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-[#0B4F9C]">Anjungan Mandiri Layar Sentuh</span>
                <h1 class="text-2xl md:text-3xl font-bold text-[#0F172A] mt-1">Pilih Layanan Statistik Anda</h1>
            </div>
            <p class="text-sm text-[#475569]">Sentuh salah satu kategori di bawah untuk menerbitkan nomor antrean.</p>
        </div>

        <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($services as $service)
                @php
                    $prefix = strtoupper($service->code_prefix);
                    $tone = $servicePalette[$prefix] ?? $servicePalette['A'];
                @endphp
                <button type="button"
                        id="btn-service-{{ $service->id }}"
                        data-service-id="{{ $service->id }}"
                        class="service-btn group w-full min-h-[144px] rounded-xl border border-[#CBD5E1] bg-white p-6 text-left transition-colors hover:border-[#0B4F9C] hover:bg-[#F8FAFC] active:bg-[#EDF1F6] flex items-center gap-5">
                    <span class="w-20 h-20 rounded-lg {{ $tone['badge'] }} flex items-center justify-center font-mono font-bold text-4xl shrink-0 tabular-nums-strict">
                        {{ $service->code_prefix }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xl font-bold text-[#0F172A]">
                            {{ $service->name }}
                        </span>
                        <span class="block text-xs sm:text-sm text-[#475569] mt-1.5 line-clamp-2">
                            {{ $service->description ?? 'Layanan konsultasi dan informasi statistik terpadu.' }}
                        </span>
                    </span>
                </button>
            @empty
                <div class="col-span-2 rounded-xl border border-[#DCE3EC] bg-white p-10 text-center">
                    <p class="text-sm font-medium text-[#475569]">Belum ada layanan aktif yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <footer class="bg-white border-t border-[#DCE3EC] py-3.5 px-6 text-xs text-[#475569]">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© 2026 Badan Pusat Statistik Kota Tasikmalaya · Pelayanan Statistik Terpadu</span>
            <span class="inline-flex items-center gap-1.5 font-semibold text-[#15803D]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#15803D]" aria-hidden="true"></span>
                <span>Anjungan Siap Operasional</span>
            </span>
        </div>
    </footer>

    {{-- Processing Overlay --}}
    <div id="loading-overlay" role="status" aria-live="polite" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl border border-[#DCE3EC] p-6 max-w-xs w-full text-center shadow-lg">
            <div class="w-10 h-10 border-4 border-[#DCE3EC] border-t-[#0B4F9C] rounded-full animate-spin mx-auto mb-3"></div>
            <h2 class="text-base font-bold text-[#0F172A]">Menerbitkan Tiket...</h2>
            <p class="text-xs text-[#475569] mt-1">Mohon tunggu, sistem sedang menyiapkan nomor antrean Anda.</p>
        </div>
    </div>

    {{-- Native Dialog: Virtual Ticket Preview --}}
    <dialog id="ticket-modal" closedby="any" aria-labelledby="ticket-modal-title" class="pst-dialog max-w-sm">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-200 text-[#15803D] flex items-center justify-center mx-auto mb-3">
                <x-icon name="check" class="w-6 h-6" />
            </div>
            <h2 id="ticket-modal-title" class="text-xl font-bold text-[#0F172A]">Tiket Diterbitkan</h2>
            <p class="text-xs text-[#475569] mt-1">Silakan ambil struk tiket dan tunggu panggilan pada layar ruang tunggu.</p>

            <div class="my-5 rounded-lg border border-dashed border-[#CBD5E1] bg-[#F8FAFC] p-4 text-left font-mono text-xs text-[#334155]">
                <div class="text-center font-bold pb-2.5 border-b border-dashed border-[#CBD5E1]">
                    BADAN PUSAT STATISTIK
                    <span class="block font-normal text-[11px] text-[#64748B]">PST BPS KOTA TASIKMALAYA</span>
                </div>
                <div class="text-center my-3.5">
                    <div class="font-sans text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Nomor Antrean</div>
                    <div id="modal-ticket-code" class="text-4xl font-bold text-[#0A2540] my-1 tracking-wider tabular-nums-strict">A-001</div>
                    <div id="modal-service-name" class="font-sans font-semibold text-sm text-[#0F172A]">Konsultasi Statistik</div>
                </div>
                <div class="border-t border-dashed border-[#CBD5E1] pt-2.5 space-y-1.5 text-xs tabular-nums-strict">
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Waktu:</span>
                        <span id="modal-ticket-time" class="font-semibold text-[#0F172A]">-</span>
                    </div>
                    <div class="flex justify-between rounded bg-orange-50 border border-orange-200 px-2 py-1 font-semibold text-[#C2410C]">
                        <span>Antrean di Depan:</span>
                        <span id="modal-ticket-ahead">0 Orang</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="w-full bg-[#EDF1F6] rounded-full h-1.5 overflow-hidden">
                    <div id="countdown-progress" class="bg-[#0B4F9C] h-1.5 transition-all duration-1000" style="width: 100%;"></div>
                </div>
                <p class="text-xs text-[#475569] mt-1.5 tabular-nums-strict">
                    Kembali otomatis dalam <span id="countdown-number" class="font-bold text-[#0F172A]">5</span> detik
                </p>
            </div>

            <button type="button" id="btn-close-ticket-modal"
                    class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-3 text-sm font-semibold text-white">
                Selesai / Kembali Sekarang
            </button>
        </div>
    </dialog>

    {{-- Native Dialog: Error Feedback --}}
    <dialog id="error-modal" closedby="any" aria-labelledby="error-modal-title" class="pst-dialog max-w-sm">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 border border-red-200 text-red-700 flex items-center justify-center mx-auto mb-3">
                <x-icon name="warning" class="w-6 h-6" />
            </div>
            <h2 id="error-modal-title" class="text-lg font-bold text-[#0F172A]">Gagal Memproses Tiket</h2>
            <p id="error-modal-message" class="text-xs text-[#475569] my-3 leading-relaxed">
                Gagal memproses tiket antrean. Silakan coba kembali atau hubungi petugas.
            </p>
            <button type="button" id="btn-close-error-modal"
                    class="w-full rounded-md bg-[#0A2540] hover:bg-[#0F172A] py-2.5 text-sm font-semibold text-white">
                Tutup &amp; Coba Lagi
            </button>
        </div>
    </dialog>

    {{-- Physical Thermal Receipt Area (58mm / 80mm @media print) --}}
    <div id="ticket-print-area" class="print-only">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const clockEl = document.getElementById('live-clock');
        const dateEl = document.getElementById('live-date');
        const loadingOverlay = document.getElementById('loading-overlay');
        const ticketDialog = document.getElementById('ticket-modal');
        const errorDialog = document.getElementById('error-modal');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let isSubmitting = false;
        let countdownInterval = null;

        function formatTimeHHMMSS(date) {
            const d = date instanceof Date && !Number.isNaN(date.getTime()) ? date : new Date();
            return [d.getHours(), d.getMinutes(), d.getSeconds()]
                .map((n) => String(n).padStart(2, '0'))
                .join(':');
        }

        function updateClock() {
            const now = new Date();
            if (clockEl) {
                clockEl.textContent = formatTimeHHMMSS(now);
            }
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
        updateClock();
        window.setInterval(updateClock, 1000);

        function setButtonsDisabled(disabled) {
            document.querySelectorAll('.service-btn').forEach((btn) => {
                btn.disabled = disabled;
                btn.classList.toggle('opacity-50', disabled);
                btn.classList.toggle('pointer-events-none', disabled);
            });
        }

        function setLoadingVisible(visible) {
            if (!loadingOverlay) return;
            loadingOverlay.classList.toggle('hidden', !visible);
            loadingOverlay.classList.toggle('flex', visible);
        }

        function resetKioskState() {
            window.clearInterval(countdownInterval);
            isSubmitting = false;
            setButtonsDisabled(false);
        }

        ticketDialog?.addEventListener('close', resetKioskState);
        errorDialog?.addEventListener('close', resetKioskState);

        document.getElementById('btn-close-ticket-modal')?.addEventListener('click', () => window.PstUi.closeModal('ticket-modal'));
        document.getElementById('btn-close-error-modal')?.addEventListener('click', () => window.PstUi.closeModal('error-modal'));

        function startCountdown() {
            window.clearInterval(countdownInterval);
            let remaining = 5;
            const progressEl = document.getElementById('countdown-progress');
            const numberEl = document.getElementById('countdown-number');

            if (numberEl) numberEl.textContent = String(remaining);
            if (progressEl) progressEl.style.width = '100%';

            countdownInterval = window.setInterval(() => {
                remaining -= 1;
                if (numberEl) numberEl.textContent = String(Math.max(0, remaining));
                if (progressEl) progressEl.style.width = `${Math.max(0, (remaining / 5) * 100)}%`;
                if (remaining <= 0) {
                    window.clearInterval(countdownInterval);
                    window.PstUi.closeModal('ticket-modal');
                }
            }, 1000);
        }

        async function takeTicket(serviceId) {
            if (isSubmitting) return;
            isSubmitting = true;
            setButtonsDisabled(true);
            setLoadingVisible(true);

            try {
                const response = await fetch("{{ route('kiosk.ticket') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ service_id: serviceId }),
                });
                const result = await response.json();
                setLoadingVisible(false);

                if (response.ok && result.status === 'success') {
                    const ticket = result.data;
                    document.getElementById('modal-ticket-code').textContent = ticket.ticket_code;
                    document.getElementById('modal-service-name').textContent = ticket.service_name;
                    document.getElementById('modal-ticket-time').textContent = ticket.created_at;
                    document.getElementById('modal-ticket-ahead').textContent = `${ticket.waiting_ahead} Orang`;

                    document.getElementById('print-ticket-code').textContent = ticket.ticket_code;
                    document.getElementById('print-service-name').textContent = ticket.service_name;
                    document.getElementById('print-ticket-time').textContent = ticket.created_at;
                    document.getElementById('print-ticket-ahead').textContent = `${ticket.waiting_ahead} Orang`;

                    window.PstUi.openModal('ticket-modal');
                    if (!ticket.mock_print) {
                        try {
                            window.print();
                        } catch (e) {
                            console.warn('Silent print invocation:', e);
                        }
                    }
                    startCountdown();
                } else {
                    document.getElementById('error-modal-message').textContent =
                        result.message || 'Gagal menerbitkan nomor antrean. Silakan coba kembali.';
                    window.PstUi.openModal('error-modal');
                }
            } catch (err) {
                console.error(err);
                setLoadingVisible(false);
                document.getElementById('error-modal-message').textContent =
                    'Terjadi kendala jaringan saat berkomunikasi dengan peladen sistem antrean.';
                window.PstUi.openModal('error-modal');
            }
        }

        document.querySelectorAll('.service-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                takeTicket(Number(btn.dataset.serviceId));
            });
        });
    });
</script>
@endpush
