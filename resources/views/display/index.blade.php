<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Monitor Antrean Ruang Tunggu - PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse-call {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { transform: scale(1.02); box-shadow: 0 0 0 16px rgba(239, 68, 68, 0); }
        }
        .anim-call {
            animation: pulse-call 1.4s infinite;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-none font-sans overflow-x-hidden select-none">
    <!-- Header TV Display -->
    <header class="bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-950 border-b-2 border-blue-800 py-3.5 px-8 flex justify-between items-center shadow-2xl">
        <div class="flex items-center space-x-5">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-blue-900 font-black text-3xl shadow-lg border-2 border-blue-300 hover:scale-105 transition-transform duration-150">
                BPS
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-wide uppercase text-white flex items-center space-x-3">
                    <span>BADAN PUSAT STATISTIK</span>
                    <span class="text-xs bg-emerald-600 text-white px-3 py-0.5 rounded-full font-bold uppercase tracking-wider shadow">PST KOTA TASIKMALAYA</span>
                </h1>
                <p class="text-blue-200 text-sm font-semibold mt-0.5">Pelayanan Statistik Terpadu - Informasi Antrean Ruang Tunggu</p>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Tombol Aktivasi Audio -->
            <button id="btn-enable-audio" type="button" onclick="enableAudioEngine()"
                class="bg-amber-500 hover:bg-amber-400 text-slate-900 font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-lg flex items-center space-x-2 animate-bounce transition cursor-pointer">
                <span class="text-base">🔊</span>
                <span>AKTIFKAN SUARA TV</span>
            </button>
            <div id="audio-active-badge" class="hidden text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-950 border border-emerald-500 text-emerald-300 items-center space-x-1.5 shadow">
                <span>🔊</span>
                <span>Audio Aktif</span>
            </div>

            <!-- Jam Digital Realtime -->
            <div class="text-right bg-blue-900/60 border border-blue-700/60 rounded-2xl px-5 py-2 shadow-inner">
                <div id="display-clock" class="text-3xl font-mono font-black tracking-widest text-emerald-400">--:--:--</div>
                <div id="display-date" class="text-blue-200 text-xs font-semibold tracking-wide">--</div>
            </div>
        </div>
    </header>

    <!-- Main Display Grid -->
    <main class="flex-1 max-w-[1700px] w-full mx-auto p-6 space-y-6">
        <!-- Banner Indikator Koneksi & Ringkasan -->
        <div class="flex justify-between items-center border-b border-slate-800 pb-2">
            <h2 class="text-xl font-black tracking-wide text-blue-300 uppercase flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Status Pemanggilan Meja Pelayanan</span>
            </h2>
            <div class="flex items-center space-x-3">
                <span id="connection-status" class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-950 border border-emerald-600 text-emerald-400 transition">
                    ● Realtime SSE Terhubung
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Kolom Kiri: Kartu Meja Loket (2/3 Layar) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($counters as $counter)
                        <div id="counter-card-{{ $counter->counter_number }}"
                            class="bg-slate-800/95 border-2 border-slate-700 rounded-3xl p-6 shadow-2xl relative overflow-hidden transition-all duration-300">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xl font-black tracking-wider text-slate-200 uppercase flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                    <span>{{ $counter->name }}</span>
                                </span>
                                <span id="counter-badge-{{ $counter->counter_number }}"
                                    class="px-3.5 py-1 rounded-full text-xs font-black uppercase bg-slate-700 text-slate-300 tracking-wider">
                                    Menunggu
                                </span>
                            </div>

                            <div class="text-center py-7 bg-slate-950/70 rounded-2xl border border-slate-700/60 shadow-inner">
                                <div class="text-xs uppercase tracking-widest text-slate-400 font-bold">Nomor Antrean</div>
                                <div id="counter-ticket-{{ $counter->counter_number }}"
                                    class="text-6xl md:text-7xl font-mono font-black text-amber-400 my-2 tracking-wider">
                                    ---
                                </div>
                                <div id="counter-service-{{ $counter->counter_number }}"
                                    class="text-sm font-bold text-blue-200">
                                    Belum ada antrean
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Baris Kartu Informasi Antrean Menunggu per Kategori Layanan -->
                <div class="bg-slate-800/80 border border-slate-700 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 flex items-center space-x-2">
                        <span>📊</span>
                        <span>Jumlah Antrean Menunggu per Layanan PST Hari Ini</span>
                    </h3>
                    <div id="service-waiting-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($services as $srv)
                            <div id="service-card-{{ $srv->id }}" class="bg-slate-900/80 border border-slate-700/80 rounded-2xl p-3.5 text-center shadow">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="w-6 h-6 rounded-lg bg-blue-900 text-blue-200 text-xs font-black flex items-center justify-center">
                                        {{ $srv->code_prefix }}
                                    </span>
                                    <span id="service-count-{{ $srv->id }}" class="text-xl font-mono font-black text-amber-400">
                                        {{ $srv->queues_count }}
                                    </span>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-300 truncate text-left" title="{{ $srv->name }}">
                                    {{ $srv->name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: 5 Riwayat Panggilan Terakhir (1/3 Layar) -->
            <div class="bg-slate-800/95 border-2 border-slate-700 rounded-3xl p-6 shadow-2xl flex flex-col h-full">
                <h3 class="text-lg font-black tracking-wide text-slate-200 uppercase mb-4 border-b border-slate-700 pb-3 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>🕒</span>
                        <span>Riwayat 5 Panggilan Terakhir</span>
                    </span>
                    <span class="text-[11px] font-bold text-slate-400">Realtime</span>
                </h3>

                <div id="recent-calls-list" class="space-y-3 flex-1">
                    @forelse($recentQueues as $rq)
                        <div class="bg-slate-900/80 border border-slate-700/80 rounded-2xl p-3.5 flex justify-between items-center shadow">
                            <div>
                                <span class="text-2xl font-mono font-black text-amber-400 tracking-wider">{{ $rq->ticket_code }}</span>
                                <div class="text-xs text-slate-400 font-medium">{{ $rq->service?->name ?? 'Layanan PST' }}</div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-xl bg-blue-900/80 border border-blue-600 text-blue-200 text-xs font-black uppercase">
                                    {{ $rq->counter?->name ?? 'Loket' }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">{{ $rq->updated_at->format('H:i') }} WIB</div>
                            </div>
                        </div>
                    @empty
                        <div id="empty-recent-notice" class="text-center py-16 text-slate-500 text-sm">
                            Belum ada riwayat antrean hari ini
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    <!-- Footer TV Running Text -->
    <footer class="bg-blue-950 border-t-2 border-blue-800 py-3 px-8 flex items-center overflow-hidden shadow-2xl">
        <span class="bg-amber-500 text-slate-950 text-xs font-black uppercase px-3 py-1 rounded-lg shrink-0 mr-4 shadow tracking-wider">
            INFO PST BPS
        </span>
        <div class="whitespace-nowrap overflow-hidden text-sm text-blue-100 font-semibold tracking-wide">
            Selamat datang di Pelayanan Statistik Terpadu Badan Pusat Statistik Kota Tasikmalaya. PST melayani Konsultasi Statistik, Rekomendasi Kegiatan Statistik, Permintaan Data Mikro, dan Perpustakaan Statistik. Harap persiapkan kartu identitas Anda saat dipanggil ke meja loket.
        </div>
    </footer>

    <!-- Panel Uji Coba Audio (Bisa Diciutkan / Disembunyikan) -->
    <div class="bg-slate-950 border-t border-slate-800 text-xs text-slate-400">
        <div class="px-6 py-2 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="toggleAudioPanel()" class="text-slate-400 hover:text-white font-bold flex items-center space-x-1">
                    <span id="panel-toggle-icon">▶</span>
                    <span>Panel Pengujian Audio (Uji Coba Pengembang)</span>
                </button>
                <span id="audio-engine-state" class="text-amber-400 font-semibold">● Menunggu Interaksi Pengguna</span>
                <span id="audio-now-playing" class="text-slate-400 italic"></span>
            </div>
            <div class="text-[11px] text-slate-500">
                Mode TV Produksi: Sembunyikan panel ini
            </div>
        </div>

        <div id="audio-debug-content" class="hidden px-6 py-3 border-t border-slate-800 bg-slate-900/90 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-slate-300 font-bold">Uji Kombinasi Suara:</span>
                <button onclick="testAnnounce('A', 1, 1)" class="bg-blue-800 hover:bg-blue-700 text-white px-3 py-1 rounded-lg font-bold transition">
                    Panggil A-001 (Loket 1)
                </button>
                <button onclick="testAnnounce('B', 15, 2)" class="bg-indigo-800 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg font-bold transition">
                    Panggil B-015 (Loket 2)
                </button>
                <button onclick="testAnnounce('C', 105, 1)" class="bg-amber-700 hover:bg-amber-600 text-white px-3 py-1 rounded-lg font-bold transition">
                    Panggil C-105 (Loket 1)
                </button>
                <button onclick="testAnnounce('D', 8, 2)" class="bg-emerald-800 hover:bg-emerald-700 text-white px-3 py-1 rounded-lg font-bold transition">
                    Panggil D-008 (Loket 2)
                </button>
            </div>
            <div>
                <button onclick="testChimeOnly()" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded-lg font-semibold transition">
                    🔔 Uji Ding-Dong Saja
                </button>
            </div>
        </div>
    </div>

    <script>
        // Jam digital realtime
        function updateDisplayClock() {
            const now = new Date();
            document.getElementById('display-clock').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('display-date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateDisplayClock, 1000);
        updateDisplayClock();

        // Status Audio Engine & Browser Autoplay Unlock
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

                // Perbarui tampilan tombol aktivasi
                document.getElementById('btn-enable-audio').classList.add('hidden');
                const badge = document.getElementById('audio-active-badge');
                badge.classList.remove('hidden');
                badge.classList.add('flex');

                document.getElementById('audio-engine-state').innerText = '● Audio Aktif (Siap Memutar)';
                document.getElementById('audio-engine-state').className = 'text-emerald-400 font-semibold';

                // Bunyikan chime tes lembut penanda audio aktif
                playChime(0.15);
            } catch (e) {
                console.warn('Audio activation error:', e);
            }
        }

        // Web Audio Synthesizer untuk Nada Chime (Ding-Dong)
        function playChime(volume = 0.3) {
            return new Promise((resolve) => {
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }

                    const now = audioCtx.currentTime;

                    // Nada 1: D5 (587.33 Hz)
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

                    // Nada 2: A5 (880.00 Hz)
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880.00, now + 0.4);
                    gain2.gain.setValueAtTime(volume, now + 0.4);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start(now + 0.4);
                    osc2.stop(now + 1.2);

                    setTimeout(resolve, 1100);
                } catch (e) {
                    console.warn('Audio Context Error:', e);
                    resolve();
                }
            });
        }

        // Web Speech Synthesizer Bahasa Indonesia
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

        // Antrean Audio FIFO (Mencegah benturan audio jika dua loket memanggil bersamaan)
        const audioQueue = [];
        let isPlayingAudio = false;
        const processedCallKeys = new Set();

        async function processAudioQueue() {
            if (isPlayingAudio || audioQueue.length === 0) return;
            isPlayingAudio = true;

            const item = audioQueue.shift();
            const textToSpeak = `Nomor antrean, ${item.prefix}, ${item.number}, menuju ${item.counterName}`;

            const playingEl = document.getElementById('audio-now-playing');
            if (playingEl) playingEl.innerText = `(${textToSpeak})`;

            // 1. Bunyikan nada pembuka Ding-Dong
            await playChime(0.3);

            // 2. Lafalkan nomor antrean dan loket tujuan dalam Bahasa Indonesia
            await speakText(textToSpeak);

            if (playingEl) playingEl.innerText = '';
            isPlayingAudio = false;

            // Jika masih ada antrean audio berikutnya, jalankan dengan jeda 500ms
            if (audioQueue.length > 0) {
                setTimeout(processAudioQueue, 500);
            }
        }

        function enqueueCall(data, shouldAnnounce = true) {
            const callKey = `${data.ticket_code}-${data.counter_number}-${data.updated_at || Date.now()}`;

            // Perbarui visual loket
            updateCounterCard(data);
            addToRecentList(data);

            // Periksa apakah audio perlu disuarakan
            if (shouldAnnounce && isAudioUnlocked && !processedCallKeys.has(callKey)) {
                processedCallKeys.add(callKey);
                audioQueue.push({
                    ticketCode: data.ticket_code,
                    prefix: data.code_prefix,
                    number: data.ticket_number,
                    counterNumber: data.counter_number,
                    counterName: data.counter_name
                });
                processAudioQueue();
            }
        }

        function updateCounterCard(data) {
            const card = document.getElementById(`counter-card-${data.counter_number}`);
            const ticketEl = document.getElementById(`counter-ticket-${data.counter_number}`);
            const serviceEl = document.getElementById(`counter-service-${data.counter_number}`);
            const badgeEl = document.getElementById(`counter-badge-${data.counter_number}`);

            if (ticketEl) ticketEl.innerText = data.ticket_code;
            if (serviceEl) serviceEl.innerText = data.service_name;
            if (badgeEl) {
                badgeEl.innerText = 'Dipanggil';
                badgeEl.className = 'px-3.5 py-1 rounded-full text-xs font-black uppercase bg-red-600 text-white tracking-wider animate-pulse';
            }
            if (card) {
                card.classList.add('anim-call', 'border-red-500');
                setTimeout(() => {
                    card.classList.remove('anim-call', 'border-red-500');
                }, 7000);
            }
        }

        function addToRecentList(data) {
            const list = document.getElementById('recent-calls-list');
            const emptyNotice = document.getElementById('empty-recent-notice');
            if (emptyNotice) emptyNotice.remove();

            const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const itemHtml = `
                <div class="bg-slate-900/80 border border-slate-700/80 rounded-2xl p-3.5 flex justify-between items-center shadow transition-all duration-300">
                    <div>
                        <span class="text-2xl font-mono font-black text-amber-400 tracking-wider">${data.ticket_code}</span>
                        <div class="text-xs text-slate-400 font-medium">${data.service_name}</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-xl bg-blue-900/80 border border-blue-600 text-blue-200 text-xs font-black uppercase">
                            ${data.counter_name}
                        </span>
                        <div class="text-[10px] text-slate-400 mt-1 font-mono">${timeNow} WIB</div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('afterbegin', itemHtml);

            // Batasi tampilan maksimal 5 panggilan terakhir
            while (list.children.length > 5) {
                list.removeChild(list.lastChild);
            }
        }

        function updateServiceWaitingCounts(services) {
            if (!services || !Array.isArray(services)) return;
            services.forEach(s => {
                const countEl = document.getElementById(`service-count-${s.id}`);
                if (countEl) {
                    countEl.innerText = s.queues_count ?? 0;
                }
            });
        }

        // SSE Realtime & Fallback HTTP Polling
        let eventSource = null;
        let sseFailCount = 0;
        let pollingInterval = null;
        const statusBadge = document.getElementById('connection-status');

        function connectSSE() {
            if (eventSource) {
                eventSource.close();
            }

            eventSource = new EventSource("{{ route('display.stream') }}");

            eventSource.addEventListener('initial-state', function(e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.active_queues) {
                        data.active_queues.forEach(q => {
                            if (q.counter) {
                                const ticketEl = document.getElementById(`counter-ticket-${q.counter.counter_number}`);
                                const serviceEl = document.getElementById(`counter-service-${q.counter.counter_number}`);
                                const badgeEl = document.getElementById(`counter-badge-${q.counter.counter_number}`);
                                if (ticketEl) ticketEl.innerText = q.ticket_code;
                                if (serviceEl) serviceEl.innerText = q.service?.name ?? '';
                                if (badgeEl) {
                                    badgeEl.innerText = q.status === 'calling' ? 'Dipanggil' : 'Melayani';
                                    badgeEl.className = q.status === 'calling'
                                        ? 'px-3.5 py-1 rounded-full text-xs font-black uppercase bg-red-600 text-white tracking-wider'
                                        : 'px-3.5 py-1 rounded-full text-xs font-black uppercase bg-blue-600 text-white tracking-wider';
                                }
                            }
                        });
                    }
                    if (data.services) {
                        updateServiceWaitingCounts(data.services);
                    }
                } catch (err) {
                    console.error('Initial state error:', err);
                }
            });

            eventSource.addEventListener('queue-called', function(e) {
                try {
                    const data = JSON.parse(e.data);
                    enqueueCall(data, true);
                } catch (err) {
                    console.error('Queue called error:', err);
                }
            });

            eventSource.onopen = function() {
                sseFailCount = 0;
                stopPolling();
                statusBadge.innerText = '● Realtime SSE Terhubung';
                statusBadge.className = 'text-xs font-bold px-3 py-1 rounded-full bg-emerald-950 border border-emerald-600 text-emerald-400 transition';
            };

            eventSource.onerror = function() {
                sseFailCount++;
                statusBadge.innerText = '● Menghubungkan Ulang...';
                statusBadge.className = 'text-xs font-bold px-3 py-1 rounded-full bg-amber-950 border border-amber-600 text-amber-400 transition';

                // Jika gagal lebih dari 2 kali, aktifkan HTTP fallback polling
                if (sseFailCount >= 2 && !pollingInterval) {
                    startFallbackPolling();
                }
            };
        }

        // Fallback HTTP Polling (2.5 detik)
        function startFallbackPolling() {
            if (pollingInterval) return;
            statusBadge.innerText = '● Mode Cadangan (Polling Aktif)';
            statusBadge.className = 'text-xs font-bold px-3 py-1 rounded-full bg-indigo-950 border border-indigo-500 text-indigo-300 transition';

            pollingInterval = setInterval(async () => {
                try {
                    const res = await fetch("{{ route('display.poll') }}");
                    if (res.ok) {
                        const json = await res.json();
                        if (json.status === 'success' && json.data) {
                            // Update active queues
                            if (json.data.active_queues) {
                                json.data.active_queues.forEach(q => {
                                    if (q.counter) {
                                        const ticketEl = document.getElementById(`counter-ticket-${q.counter.counter_number}`);
                                        const serviceEl = document.getElementById(`counter-service-${q.counter.counter_number}`);
                                        const badgeEl = document.getElementById(`counter-badge-${q.counter.counter_number}`);
                                        if (ticketEl) ticketEl.innerText = q.ticket_code;
                                        if (serviceEl) serviceEl.innerText = q.service?.name ?? '';
                                        if (badgeEl) {
                                            badgeEl.innerText = q.status === 'calling' ? 'Dipanggil' : 'Melayani';
                                            badgeEl.className = q.status === 'calling'
                                                ? 'px-3.5 py-1 rounded-full text-xs font-black uppercase bg-red-600 text-white tracking-wider'
                                                : 'px-3.5 py-1 rounded-full text-xs font-black uppercase bg-blue-600 text-white tracking-wider';
                                        }
                                    }
                                });
                            }
                            // Update waiting count per service
                            if (json.data.services) {
                                updateServiceWaitingCounts(json.data.services);
                            }
                            // Periksa apakah ada panggilan terbaru yang belum disuarakan
                            if (json.data.latest_calling) {
                                const callKey = `${json.data.latest_calling.ticket_code}-${json.data.latest_calling.counter_number}-${json.data.latest_calling.updated_at}`;
                                if (!processedCallKeys.has(callKey)) {
                                    enqueueCall(json.data.latest_calling, true);
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Polling fallback error:', e);
                }
            }, 2500);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Panel Debug Controls
        function toggleAudioPanel() {
            const panel = document.getElementById('audio-debug-content');
            const icon = document.getElementById('panel-toggle-icon');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                icon.innerText = '▼';
            } else {
                panel.classList.add('hidden');
                icon.innerText = '▶';
            }
        }

        function testAnnounce(prefix, number, counterNum) {
            enableAudioEngine();
            const code = `${prefix}-${String(number).padStart(3, '0')}`;
            enqueueCall({
                ticket_code: code,
                code_prefix: prefix,
                ticket_number: number,
                counter_number: counterNum,
                counter_name: `Loket ${counterNum}`,
                service_name: 'Uji Coba Pengumuman Suara'
            }, true);
        }

        function testChimeOnly() {
            enableAudioEngine();
            playChime(0.35);
        }

        document.addEventListener('DOMContentLoaded', connectSSE);
    </script>
</body>
</html>
