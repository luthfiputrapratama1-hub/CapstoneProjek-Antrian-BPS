@extends('layouts.screen', ['screenTheme' => 'dark'])

@section('title', 'Layar Monitor Antrean Ruang Tunggu — PST BPS Kota Tasikmalaya')

@section('content')
    <header class="bg-[#0D1420] border-b border-[#1F2937]">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>
        <div class="max-w-[1700px] mx-auto px-6 py-3.5 flex flex-wrap items-center justify-between gap-4">
            <x-bps-brand
                title="BADAN PUSAT STATISTIK"
                subtitle="Pelayanan Statistik Terpadu · Informasi Antrean Ruang Tunggu"
                :dark="true"
                size="lg"
            />

            <div class="flex items-center gap-3">
                <button id="btn-enable-audio" type="button"
                        class="inline-flex items-center gap-2 rounded-md bg-[#F59E0B] hover:bg-amber-400 px-3.5 py-2 text-xs font-bold text-[#090E17] transition-colors">
                    <x-icon name="volume-on" class="w-4 h-4" />
                    <span>AKTIFKAN SUARA TV</span>
                </button>

                <div id="audio-active-badge"
                     class="hidden items-center gap-1.5 text-xs font-semibold text-emerald-300">
                    <x-icon name="volume-on" class="w-4 h-4" />
                    <span>Audio Aktif</span>
                </div>

                <span id="connection-status"
                      class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-current" aria-hidden="true"></span>
                    <span>SSE Terhubung</span>
                </span>

                <div class="text-right rounded-lg border border-[#1F2937] bg-[#111827] px-4 py-1.5 tabular-nums-strict">
                    <div id="display-clock" class="text-2xl font-mono font-bold tracking-wider text-[#F59E0B]">--:--:--</div>
                    <div id="display-date" class="text-[11px] text-slate-300">--</div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-[1700px] w-full mx-auto px-6 py-5 flex flex-col gap-5">
        <h1 class="visually-hidden">Layar Monitor Antrean Ruang Tunggu PST BPS Kota Tasikmalaya</h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch flex-1">
            {{-- Left 2/3: Active Counter Cards + Service Waiting Strip --}}
            <div class="lg:col-span-2 flex flex-col justify-between gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 flex-1">
                    @foreach($counters as $counter)
                        <section id="counter-card-{{ $counter->counter_number }}"
                                 data-counter-card="{{ $counter->counter_number }}"
                                 aria-labelledby="counter-heading-{{ $counter->counter_number }}"
                                 class="rounded-xl border border-[#1F2937] bg-[#111827] p-6 flex flex-col transition-colors">
                            <div class="flex items-center justify-between gap-3 pb-4 border-b border-[#1F2937]">
                                <h2 id="counter-heading-{{ $counter->counter_number }}" class="text-lg font-bold tracking-wide text-slate-100 uppercase">
                                    {{ $counter->name }}
                                </h2>
                                <span id="counter-badge-{{ $counter->counter_number }}"
                                      class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Menunggu
                                </span>
                            </div>

                            <div class="flex-1 flex flex-col items-center justify-center py-6 px-3 text-center">
                                <div class="text-xs font-semibold uppercase tracking-widest text-slate-400">Nomor Antrean</div>
                                <div id="counter-ticket-{{ $counter->counter_number }}"
                                     class="text-7xl xl:text-8xl font-mono font-bold text-[#F59E0B] my-4 tracking-wider tabular-nums-strict leading-none">
                                    ---
                                </div>
                                <div id="counter-service-{{ $counter->counter_number }}"
                                     class="text-base font-medium text-slate-300 truncate max-w-full">
                                    Belum ada antrean
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>

                {{-- Waiting Count Strip per Service --}}
                <section aria-labelledby="waiting-strip-heading" class="rounded-xl border border-[#1F2937] bg-[#111827] p-4">
                    <h2 id="waiting-strip-heading" class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">
                        Antrean Menunggu per Layanan PST Hari Ini
                    </h2>
                    <div id="service-waiting-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($services as $srv)
                            <div id="service-card-{{ $srv->id }}" class="rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-mono text-xs font-bold text-[#60A5FA]">
                                        {{ $srv->code_prefix }}
                                    </span>
                                    <div class="text-xs font-medium text-slate-300 truncate mt-0.5" title="{{ $srv->name }}">
                                        {{ $srv->name }}
                                    </div>
                                </div>
                                <span id="service-count-{{ $srv->id }}" class="text-2xl font-mono font-bold text-[#F59E0B] tabular-nums-strict">
                                    {{ $srv->queues_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Right 1/3: Recent 5 Calls --}}
            <aside aria-labelledby="recent-calls-heading" class="rounded-xl border border-[#1F2937] bg-[#111827] p-5 flex flex-col">
                <div class="flex items-center justify-between border-b border-[#1F2937] pb-3 mb-4">
                    <h2 id="recent-calls-heading" class="text-sm font-bold uppercase tracking-wider text-slate-200">
                        Riwayat 5 Panggilan Terakhir
                    </h2>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Realtime</span>
                </div>

                <div id="recent-calls-list" class="space-y-2.5 flex-1">
                    @forelse($recentQueues as $rq)
                        <div class="rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-xl font-mono font-bold text-[#F59E0B] tracking-wider tabular-nums-strict">{{ $rq->ticket_code }}</span>
                                <div class="text-xs text-slate-300 truncate">{{ $rq->service?->name ?? 'Layanan PST' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-semibold text-slate-200">
                                    {{ $rq->counter?->name ?? 'Loket' }}
                                </span>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 tabular-nums-strict">{{ $rq->updated_at->format('H:i') }} WIB</div>
                            </div>
                        </div>
                    @empty
                        <div id="empty-recent-notice" class="py-16 text-center text-xs text-slate-400">
                            Belum ada riwayat antrean hari ini
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>

    {{-- Ticker Footer + Subtle Technician Audio Panel Toggle --}}
    <footer class="bg-[#0D1420] border-t border-[#1F2937] px-6 py-2.5 flex items-center gap-4">
        <span class="rounded bg-[#F59E0B] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#090E17] shrink-0">
            INFO PST BPS
        </span>

        <div class="tv-ticker-viewport text-sm font-medium text-slate-200">
            <div class="tv-ticker-track">
                <span class="pr-12">Selamat datang di Pelayanan Statistik Terpadu Badan Pusat Statistik Kota Tasikmalaya. PST melayani Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Permintaan Data Mikro &amp; Publikasi, serta Perpustakaan Statistik. Harap persiapkan identitas Anda saat dipanggil ke loket.</span>
                <span class="tv-ticker-duplicate pr-12" aria-hidden="true">Selamat datang di Pelayanan Statistik Terpadu Badan Pusat Statistik Kota Tasikmalaya. PST melayani Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Permintaan Data Mikro &amp; Publikasi, serta Perpustakaan Statistik. Harap persiapkan identitas Anda saat dipanggil ke loket.</span>
            </div>
        </div>

        <button id="btn-toggle-debug"
                type="button"
                title="Pengaturan &amp; Uji Audio (Alt+D)"
                aria-label="Buka panel uji audio teknisi"
                aria-expanded="false"
                aria-controls="audio-debug-panel"
                class="rounded-md border border-[#1F2937] bg-[#111827] p-1.5 text-slate-400 hover:text-white shrink-0">
            <x-icon name="sliders" class="w-4 h-4" />
        </button>
    </footer>

    {{-- Hidden Technician Audio Test Drawer (Alt+D) --}}
    <div id="audio-debug-panel" class="hidden bg-[#090E17] border-t border-[#1F2937] px-6 py-3 text-xs text-slate-300">
        <div class="max-w-[1700px] mx-auto flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-slate-200">Uji Audio Teknisi:</span>
                <span id="audio-engine-state" class="text-[#F59E0B] font-medium">Menunggu Aktivasi</span>
                <span id="audio-now-playing" class="text-slate-400 italic"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" data-test-announce="A,1,1" class="rounded bg-[#0B4F9C] hover:bg-[#083D7A] px-2.5 py-1 font-semibold text-white">Panggil A-001 (Loket 1)</button>
                <button type="button" data-test-announce="B,15,2" class="rounded bg-[#15803D] hover:bg-emerald-700 px-2.5 py-1 font-semibold text-white">Panggil B-015 (Loket 2)</button>
                <button type="button" data-test-announce="C,105,1" class="rounded bg-[#C2410C] hover:bg-orange-700 px-2.5 py-1 font-semibold text-white">Panggil C-105 (Loket 1)</button>
                <button type="button" id="btn-test-chime" class="rounded border border-[#374151] bg-[#111827] hover:bg-[#1F2937] px-2.5 py-1 font-semibold text-slate-200">Uji Ding-Dong</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const clockEl = document.getElementById('display-clock');
        const dateEl = document.getElementById('display-date');
        const statusBadge = document.getElementById('connection-status');
        const debugPanel = document.getElementById('audio-debug-panel');
        const debugToggleBtn = document.getElementById('btn-toggle-debug');

        function formatTimeHHMMSS(date) {
            const d = date instanceof Date && !Number.isNaN(date.getTime()) ? date : new Date();
            return [d.getHours(), d.getMinutes(), d.getSeconds()]
                .map((n) => String(n).padStart(2, '0'))
                .join(':');
        }

        function updateDisplayClock() {
            const now = new Date();
            if (clockEl) clockEl.textContent = formatTimeHHMMSS(now);
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        updateDisplayClock();
        window.setInterval(updateDisplayClock, 1000);

        let audioCtx = null;
        let isAudioUnlocked = false;

        function enableAudioEngine() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                isAudioUnlocked = true;
                document.getElementById('btn-enable-audio')?.classList.add('hidden');
                const badge = document.getElementById('audio-active-badge');
                badge?.classList.remove('hidden');
                badge?.classList.add('inline-flex');
                const stateEl = document.getElementById('audio-engine-state');
                if (stateEl) {
                    stateEl.textContent = 'Audio Aktif';
                    stateEl.className = 'text-emerald-400 font-semibold';
                }
                playChime(0.15);
            } catch (e) {
                console.warn('Audio activation error:', e);
            }
        }

        function playChime(volume = 0.3) {
            return new Promise((resolve) => {
                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const now = audioCtx.currentTime;
                    const osc1 = audioCtx.createOscillator();
                    const gain1 = audioCtx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now);
                    gain1.gain.setValueAtTime(volume, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                    osc1.connect(gain1);
                    gain1.connect(audioCtx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.6);

                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880.0, now + 0.4);
                    gain2.gain.setValueAtTime(volume, now + 0.4);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start(now + 0.4);
                    osc2.stop(now + 1.2);

                    window.setTimeout(resolve, 1100);
                } catch (e) {
                    console.warn('Audio Context Error:', e);
                    resolve();
                }
            });
        }

        function speakText(text) {
            return new Promise((resolve) => {
                if (!('speechSynthesis' in window)) {
                    resolve();
                    return;
                }
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.88;
                utterance.pitch = 1.0;
                utterance.onend = () => resolve();
                utterance.onerror = () => resolve();
                window.speechSynthesis.speak(utterance);
            });
        }

        const audioQueue = [];
        let isPlayingAudio = false;
        const seenVisualKeys = [];
        const announcedCallKeys = [];
        const MAX_CALL_KEYS = 200;
        const highlightTimers = new Map();

        function rememberKey(list, key) {
            if (list.includes(key)) return false;
            list.push(key);
            if (list.length > MAX_CALL_KEYS) {
                list.shift();
            }
            return true;
        }

        function clearCardHighlightTimer(counterNumber) {
            const key = String(counterNumber);
            const existingTimer = highlightTimers.get(key);
            if (existingTimer) {
                window.clearTimeout(existingTimer);
                highlightTimers.delete(key);
            }
        }

        function setCardCallingHighlight(card, counterNumber, durationMs = 7000) {
            if (!card) return;
            const key = String(counterNumber);
            clearCardHighlightTimer(key);
            card.classList.add('tv-card-calling');
            if (durationMs > 0) {
                const timerId = window.setTimeout(() => {
                    card.classList.remove('tv-card-calling');
                    highlightTimers.delete(key);
                }, durationMs);
                highlightTimers.set(key, timerId);
            }
        }

        function formatTimeHHMM(date) {
            const d = date instanceof Date && !Number.isNaN(date.getTime()) ? date : new Date();
            return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        }

        async function processAudioQueue() {
            if (isPlayingAudio || audioQueue.length === 0) return;
            isPlayingAudio = true;

            const item = audioQueue.shift();
            const textToSpeak = `Nomor antrean, ${item.prefix}, ${item.number}, menuju ${item.counterName}`;
            const playingEl = document.getElementById('audio-now-playing');
            if (playingEl) playingEl.textContent = `(${textToSpeak})`;

            await playChime(0.3);
            await speakText(textToSpeak);

            if (playingEl) playingEl.textContent = '';
            isPlayingAudio = false;

            if (audioQueue.length > 0) {
                window.setTimeout(processAudioQueue, 500);
            }
        }

        function setCounterBadge(badgeEl, status) {
            if (!badgeEl) return;
            if (status === 'calling') {
                badgeEl.textContent = 'Dipanggil';
                badgeEl.className = 'text-xs font-bold uppercase tracking-wider text-red-400';
            } else if (status === 'serving') {
                badgeEl.textContent = 'Melayani';
                badgeEl.className = 'text-xs font-bold uppercase tracking-wider text-emerald-400';
            } else {
                badgeEl.textContent = 'Menunggu';
                badgeEl.className = 'text-xs font-semibold uppercase tracking-wider text-slate-400';
            }
        }

        function resetAllCounterCards() {
            document.querySelectorAll('[data-counter-card]').forEach((card) => {
                const counterNum = card.dataset.counterCard;
                const ticketEl = document.getElementById(`counter-ticket-${counterNum}`);
                const serviceEl = document.getElementById(`counter-service-${counterNum}`);
                const badgeEl = document.getElementById(`counter-badge-${counterNum}`);
                if (ticketEl) ticketEl.textContent = '---';
                if (serviceEl) serviceEl.textContent = 'Belum ada antrean';
                setCounterBadge(badgeEl, 'idle');
                clearCardHighlightTimer(counterNum);
                card.classList.remove('tv-card-calling');
            });
        }

        function applyActiveQueues(activeQueues) {
            resetAllCounterCards();
            if (!Array.isArray(activeQueues)) return;
            activeQueues.forEach((q) => {
                if (!q.counter) return;
                const counterNum = q.counter.counter_number;
                const card = document.getElementById(`counter-card-${counterNum}`);
                const ticketEl = document.getElementById(`counter-ticket-${counterNum}`);
                const serviceEl = document.getElementById(`counter-service-${counterNum}`);
                const badgeEl = document.getElementById(`counter-badge-${counterNum}`);
                if (ticketEl) ticketEl.textContent = q.ticket_code || '---';
                if (serviceEl) serviceEl.textContent = q.service?.name || 'Layanan PST';
                setCounterBadge(badgeEl, q.status);
                if (q.status === 'calling') {
                    setCardCallingHighlight(card, counterNum, 0);
                } else {
                    clearCardHighlightTimer(counterNum);
                    card?.classList.remove('tv-card-calling');
                }
            });
        }

        function createRecentCallRow(ticketCode, serviceName, counterName, timeLabel) {
            const row = document.createElement('div');
            row.className = 'rounded-lg border border-[#1F2937] bg-[#090E17] px-3.5 py-3 flex items-center justify-between gap-3';

            const left = document.createElement('div');
            left.className = 'min-w-0';
            const codeEl = document.createElement('span');
            codeEl.className = 'text-xl font-mono font-bold text-[#F59E0B] tracking-wider tabular-nums-strict';
            codeEl.textContent = ticketCode;
            const srvEl = document.createElement('div');
            srvEl.className = 'text-xs text-slate-300 truncate';
            srvEl.textContent = serviceName;
            left.append(codeEl, srvEl);

            const right = document.createElement('div');
            right.className = 'text-right shrink-0';
            const counterEl = document.createElement('span');
            counterEl.className = 'text-xs font-semibold text-slate-200';
            counterEl.textContent = counterName;
            const timeEl = document.createElement('div');
            timeEl.className = 'text-[11px] font-mono text-slate-400 mt-0.5 tabular-nums-strict';
            timeEl.textContent = `${timeLabel} WIB`;
            right.append(counterEl, timeEl);

            row.append(left, right);
            return row;
        }

        function renderRecentCalls(recentQueues) {
            const list = document.getElementById('recent-calls-list');
            if (!list || !Array.isArray(recentQueues)) return;

            if (recentQueues.length === 0) {
                const empty = document.createElement('div');
                empty.id = 'empty-recent-notice';
                empty.className = 'py-16 text-center text-xs text-slate-400';
                empty.textContent = 'Belum ada riwayat antrean hari ini';
                list.replaceChildren(empty);
                return;
            }

            const nodes = recentQueues.slice(0, 5).map((rq) => {
                const dateObj = rq.updated_at ? new Date(rq.updated_at) : new Date();
                const timeStr = formatTimeHHMM(dateObj);
                return createRecentCallRow(
                    rq.ticket_code || '---',
                    rq.service?.name || 'Layanan PST',
                    rq.counter?.name || 'Loket',
                    timeStr
                );
            });
            list.replaceChildren(...nodes);
        }

        function prependRecentCall(data) {
            const list = document.getElementById('recent-calls-list');
            if (!list) return;
            document.getElementById('empty-recent-notice')?.remove();
            const timeNow = formatTimeHHMM(data.updated_at ? new Date(data.updated_at) : new Date());
            list.prepend(createRecentCallRow(
                data.ticket_code || '---',
                data.service_name || 'Layanan PST',
                data.counter_name || 'Loket',
                timeNow
            ));
            while (list.children.length > 5) {
                list.lastElementChild?.remove();
            }
        }

        function enqueueCall(data, shouldAnnounce = true, options = {}) {
            const { skipRecentPrepend = false, isTest = false } = options;
            const ticketCode = data.ticket_code || '---';
            const serviceName = data.service_name || 'Layanan PST';
            const counterName = data.counter_name || 'Loket';
            const callKey = `${ticketCode}-${data.counter_number}-${data.updated_at || (isTest ? Date.now() : '')}`;

            const card = document.getElementById(`counter-card-${data.counter_number}`);
            const ticketEl = document.getElementById(`counter-ticket-${data.counter_number}`);
            const serviceEl = document.getElementById(`counter-service-${data.counter_number}`);
            const badgeEl = document.getElementById(`counter-badge-${data.counter_number}`);

            if (ticketEl) ticketEl.textContent = ticketCode;
            if (serviceEl) serviceEl.textContent = serviceName;
            setCounterBadge(badgeEl, 'calling');
            setCardCallingHighlight(card, data.counter_number, isTest ? 7000 : 0);

            const isNewVisual = isTest || rememberKey(seenVisualKeys, callKey);
            if (!skipRecentPrepend && isNewVisual) {
                prependRecentCall(data);
            }

            if (shouldAnnounce && isAudioUnlocked && rememberKey(announcedCallKeys, callKey)) {
                audioQueue.push({
                    prefix: data.code_prefix,
                    number: data.ticket_number,
                    counterName,
                });
                processAudioQueue();
            }
        }

        function updateServiceWaitingCounts(services) {
            if (!Array.isArray(services)) return;
            services.forEach((s) => {
                const countEl = document.getElementById(`service-count-${s.id}`);
                if (countEl) countEl.textContent = String(s.queues_count ?? 0);
            });
        }

        function setConnectionStatus(mode, text) {
            if (!statusBadge) return;
            const styles = {
                sse: 'inline-flex items-center gap-2 text-xs font-semibold text-emerald-300',
                reconnecting: 'inline-flex items-center gap-2 text-xs font-semibold text-amber-300',
                polling: 'inline-flex items-center gap-2 text-xs font-semibold text-blue-300',
            };
            statusBadge.className = styles[mode] || styles.sse;
            const label = statusBadge.querySelector('span:last-child');
            if (label) label.textContent = text;
        }

        let eventSource = null;
        let sseFailCount = 0;
        let pollingInterval = null;

        function startFallbackPolling() {
            if (pollingInterval) return;
            setConnectionStatus('polling', 'Mode Polling LAN');

            pollingInterval = window.setInterval(async () => {
                try {
                    const res = await fetch("{{ route('display.poll') }}");
                    if (!res.ok) return;
                    const json = await res.json();
                    if (json.status !== 'success' || !json.data) return;

                    applyActiveQueues(json.data.active_queues);
                    renderRecentCalls(json.data.recent_queues);
                    updateServiceWaitingCounts(json.data.services);

                    if (json.data.latest_calling) {
                        const latest = json.data.latest_calling;
                        const callKey = `${latest.ticket_code}-${latest.counter_number}-${latest.updated_at || ''}`;
                        if (!announcedCallKeys.includes(callKey)) {
                            enqueueCall(latest, true, { skipRecentPrepend: true });
                        }
                    }
                } catch (e) {
                    console.warn('Polling fallback error:', e);
                }
            }, 2500);
        }

        function stopPolling() {
            if (pollingInterval) {
                window.clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        function connectSSE() {
            if (eventSource) eventSource.close();
            eventSource = new EventSource("{{ route('display.stream') }}");

            eventSource.addEventListener('initial-state', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    applyActiveQueues(data.active_queues);
                    updateServiceWaitingCounts(data.services);
                } catch (err) {
                    console.error('Initial state error:', err);
                }
            });

            eventSource.addEventListener('queue-called', (e) => {
                try {
                    enqueueCall(JSON.parse(e.data), true);
                } catch (err) {
                    console.error('Queue called error:', err);
                }
            });

            eventSource.onopen = () => {
                sseFailCount = 0;
                stopPolling();
                setConnectionStatus('sse', 'SSE Terhubung');
            };

            eventSource.onerror = () => {
                sseFailCount += 1;
                setConnectionStatus('reconnecting', 'Menghubungkan Ulang');
                if (sseFailCount >= 2 && !pollingInterval) {
                    startFallbackPolling();
                }
            };
        }

        function toggleDebugPanel() {
            if (!debugPanel || !debugToggleBtn) return;
            const willOpen = debugPanel.classList.contains('hidden');
            debugPanel.classList.toggle('hidden', !willOpen);
            debugToggleBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        }

        document.getElementById('btn-enable-audio')?.addEventListener('click', enableAudioEngine);
        debugToggleBtn?.addEventListener('click', toggleDebugPanel);
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.code === 'KeyD') {
                e.preventDefault();
                toggleDebugPanel();
            }
        });

        document.querySelectorAll('[data-test-announce]').forEach((btn) => {
            btn.addEventListener('click', () => {
                enableAudioEngine();
                const [prefix, numStr, counterStr] = btn.dataset.testAnnounce.split(',');
                const number = Number(numStr);
                const counterNum = Number(counterStr);
                enqueueCall({
                    ticket_code: `${prefix}-${String(number).padStart(3, '0')}`,
                    code_prefix: prefix,
                    ticket_number: number,
                    counter_number: counterNum,
                    counter_name: `Loket ${counterNum}`,
                    service_name: 'Uji Coba Pengumuman Suara',
                }, true, { isTest: true });
            });
        });

        document.getElementById('btn-test-chime')?.addEventListener('click', () => {
            enableAudioEngine();
            playChime(0.35);
        });

        connectSSE();
    });
</script>
@endpush
