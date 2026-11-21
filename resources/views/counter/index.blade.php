<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Operasional Loket - PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none">
    <!-- Navbar Petugas -->
    <header class="bg-blue-900 text-white shadow-md py-4 px-8 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-blue-900 font-black text-xl shadow hover:scale-105 transition-transform">
                BPS
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-wide">Meja Kerja Pelayanan Loket Terpadu</h1>
                <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}"
                    class="bg-blue-950 hover:bg-blue-800 text-blue-200 hover:text-white border border-blue-700 text-xs font-bold px-3 py-1.5 rounded-lg transition shadow">
                    ← Kembali ke Dashboard Admin
                </a>
            @endif
            <!-- Counter Selector -->
            <form action="{{ route('counter.index') }}" method="GET" class="flex items-center space-x-2">
                <label for="counter_id" class="text-xs font-semibold text-blue-200">Loket Bertugas:</label>
                <select name="counter_id" id="counter_id" onchange="this.form.submit()"
                    class="bg-blue-950 text-white border border-blue-700 text-xs font-bold rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}" {{ $selectedCounter?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="border-l border-blue-800 pl-6 flex items-center space-x-3">
                <div class="text-right">
                    <div class="text-sm font-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-emerald-300 font-semibold uppercase">{{ Auth::user()->role }}</div>
                </div>
                <a href="{{ route('password.change') }}" class="bg-blue-800 hover:bg-blue-700 text-white text-xs px-2.5 py-1.5 rounded-lg font-semibold transition shadow">
                    Kata Sandi
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-700/80 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Kolom Kiri: Pengendali Antrean Loket -->
        <div class="space-y-6">
            <!-- Kartu Info Loket -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kategori Dilayani</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                        {{ $waitingCount }} Menunggu
                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($selectedCounter?->services ?? [] as $srv)
                        <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                        </span>
                    @empty
                        <span class="text-xs text-red-500 italic">Loket belum memiliki penugasan layanan</span>
                    @endforelse
                </div>
            </div>

            <!-- Kartu Status Antrean Aktif -->
            <div class="bg-white rounded-3xl shadow-md border-2 border-slate-200 p-6 text-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Antrean Sedang Ditangani</span>
                <div id="active-ticket-code" class="text-6xl font-mono font-black text-blue-900 my-4">
                    {{ $activeQueue?->ticket_code ?? '---' }}
                </div>
                <div id="active-service-name" class="text-sm font-bold text-slate-700">
                    {{ $activeQueue?->service?->name ?? 'Belum ada antrean yang dipanggil' }}
                </div>
                <div class="mt-3 space-y-1">
                    <span id="active-status-badge" class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $activeQueue ? ($activeQueue->status === 'serving' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') : 'bg-slate-100 text-slate-500' }}">
                        {{ $activeQueue ? ($activeQueue->status === 'serving' ? 'Sedang Dilayani' : 'Sedang Dipanggil') : 'Kosong' }}
                    </span>
                    @if($activeQueue && $activeQueue->status === 'calling')
                        <div class="text-[11px] font-semibold text-slate-500">
                            Panggilan Ulang: <span class="font-bold text-blue-900">{{ $activeQueue->recall_count }}</span> / 3
                        </div>
                    @endif
                </div>

                <!-- Tombol Aksi Loket -->
                <div class="mt-8 space-y-3">
                    <button type="button" onclick="callNextQueue()" {{ $activeQueue ? 'disabled' : '' }}
                        id="btn-call-next"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:pointer-events-none text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-emerald-900/20 active:scale-[0.99] transition flex items-center justify-center space-x-2">
                        <span>📢</span>
                        <span>PANGGIL BERIKUTNYA</span>
                    </button>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="recallQueue()" {{ (!$activeQueue || $activeQueue->status !== 'calling' || $activeQueue->recall_count >= 3) ? 'disabled' : '' }}
                            id="btn-recall"
                            class="bg-blue-800 hover:bg-blue-900 disabled:opacity-40 disabled:pointer-events-none text-white font-bold py-2.5 rounded-xl shadow transition text-sm">
                            Panggil Ulang ({{ $activeQueue?->recall_count ?? 0 }}/3)
                        </button>
                        <button type="button" onclick="skipQueue()" {{ (!$activeQueue || $activeQueue->status !== 'calling' || !$canSkip) ? 'disabled' : '' }}
                            id="btn-skip"
                            title="{{ !$canSkip ? 'Tombol aktif setelah pemanggilan ulang ke-3' : 'Lewati antrean' }}"
                            class="bg-slate-200 hover:bg-red-100 hover:text-red-700 disabled:opacity-40 disabled:pointer-events-none text-slate-700 font-bold py-2.5 rounded-xl transition text-sm border border-slate-300">
                            Lewati (Skip)
                        </button>
                    </div>

                    @if($activeQueue && $activeQueue->status === 'calling' && !$canSkip)
                        <p class="text-[11px] text-amber-700 bg-amber-50 rounded-lg p-1.5 border border-amber-200">
                            ⚠️ Tombol Lewati aktif setelah panggilan ke-3
                        </p>
                    @endif

                    <button type="button" onclick="startServing()" {{ (!$activeQueue || $activeQueue->status !== 'calling') ? 'disabled' : '' }}
                        id="btn-start-serve"
                        class="w-full bg-indigo-800 hover:bg-indigo-900 disabled:opacity-40 disabled:pointer-events-none text-white font-bold py-3 rounded-xl shadow transition text-sm flex items-center justify-center space-x-2">
                        <span>✍️</span>
                        <span>Mulai Melayani (Buka Buku Tamu)</span>
                    </button>
                </div>
            </div>

            <!-- Kartu Antrean Dilewati (Skipped) Hari Ini -->
            @if(isset($skippedQueues) && $skippedQueues->count() > 0)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Antrean Terlewat Hari Ini</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                            {{ $skippedQueues->count() }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-3">Dapat dipanggil kembali sekali ke urutan paling depan:</p>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($skippedQueues as $sq)
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                                <div>
                                    <span class="font-mono font-bold text-blue-900">{{ $sq->ticket_code }}</span>
                                    <div class="text-[10px] text-slate-500">{{ $sq->service?->name }}</div>
                                </div>
                                <button type="button" onclick="recallSkippedQueue({{ $sq->id }}, '{{ $sq->ticket_code }}')"
                                    class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-2.5 py-1 rounded-lg text-[11px] transition shadow">
                                    Panggil Kembali
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Formulir Buku Tamu Digital Terpadu (2/3 Lebar) -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
            <div class="flex justify-between items-center border-b border-slate-200 pb-4 mb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Formulir Buku Tamu Digital PST</h2>
                    <p class="text-slate-500 text-sm mt-0.5">Wajib diisi dan disimpan sebelum antrean ditandai selesai</p>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                    SKF-05 BPS Terpadu
                </span>
            </div>

            <form id="form-guestbook" onsubmit="submitComplete(event)" class="space-y-6">
                <input type="hidden" id="form-queue-id" value="{{ $activeQueue?->id ?? '' }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Nama Lengkap Pengunjung <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name" required {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                            placeholder="Contoh: Dedi Mulyadi"
                            value="{{ $activeQueue?->guestBook?->full_name ?? '' }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">
                    </div>

                    <div>
                        <label for="phone_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Nomor WhatsApp / HP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="phone_number" required {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                            placeholder="08xxxxxxxxxx"
                            value="{{ $activeQueue?->guestBook?->phone_number ?? '' }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Alamat Email (Opsional)
                        </label>
                        <input type="email" id="email" {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                            placeholder="nama@instansi.go.id"
                            value="{{ $activeQueue?->guestBook?->email ?? '' }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">
                    </div>

                    <div>
                        <label for="institution_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Kategori Pemohon / Asal <span class="text-red-500">*</span>
                        </label>
                        <select id="institution_type" required {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">
                            <option value="pendidikan" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pendidikan' ? 'selected' : '' }}>Lembaga Pendidikan / Mahasiswa / Peneliti</option>
                            <option value="pemerintah" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pemerintah' ? 'selected' : '' }}>Instansi Pemerintah (K/L/Pemda)</option>
                            <option value="swasta" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'swasta' ? 'selected' : '' }}>Sektor Swasta / Bisnis</option>
                            <option value="lsm" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'lsm' ? 'selected' : '' }}>LSM / Organisasi Masyarakat / Media</option>
                            <option value="umum" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'umum' ? 'selected' : '' }}>Individu / Masyarakat Umum</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="institution_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Nama Instansi / Universitas / Lembaga Asal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="institution_name" required {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                        placeholder="Contoh: Universitas Siliwangi / Dinas Kesehatan Kota Tasikmalaya"
                        value="{{ $activeQueue?->guestBook?->institution_name ?? '' }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">
                </div>

                <div>
                    <label for="data_needs_description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Rincian Kebutuhan Data / Maksud Konsultasi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="data_needs_description" rows="3" required {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                        placeholder="Tuliskan data statistik yang dibutuhkan atau topik konsultasi teknis..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:bg-slate-50 disabled:text-slate-400 text-sm">{{ $activeQueue?->guestBook?->data_needs_description ?? '' }}</textarea>
                </div>

                <!-- Pernyataan Persetujuan UU Perlindungan Data Pribadi (UU PDP) -->
                <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-2xl">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" id="pdp_consent" name="pdp_consent" required value="1"
                            {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer">
                        <span class="text-xs text-slate-700 leading-relaxed">
                            <strong class="text-blue-900 font-bold">Pernyataan UU Pelindungan Data Pribadi (UU PDP):</strong>
                            Saya menyatakan bahwa data identitas dan kontak yang diberikan di atas adalah benar, serta menyetujui penggunaannya secara kedinasan oleh Badan Pusat Statistik untuk pencatatan buku tamu dan evaluasi pelayanan statistik publik sesuai ketentuan perundang-undangan yang berlaku.
                        </span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" id="btn-submit-complete" {{ (!$activeQueue || $activeQueue->status !== 'serving') ? 'disabled' : '' }}
                        class="bg-blue-900 hover:bg-blue-800 disabled:opacity-40 disabled:pointer-events-none text-white font-extrabold px-8 py-3.5 rounded-xl shadow-lg transition active:scale-[0.99] flex items-center space-x-2">
                        <span>✓</span>
                        <span>SIMPAN BUKU TAMU & SELESAIKAN ANTREAN</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const selectedCounterId = {{ $selectedCounter?->id ?? 1 }};

        async function callNextQueue() {
            try {
                const res = await fetch("{{ route('counter.callNext') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ counter_id: selectedCounterId })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    window.location.reload();
                } else {
                    alert(json.message || 'Tidak ada antrean menunggu.');
                }
            } catch (e) {
                console.error(e);
                alert('Gagal memanggil antrean berikutnya.');
            }
        }

        async function recallQueue() {
            const queueId = document.getElementById('form-queue-id').value;
            if (!queueId) return;
            try {
                const res = await fetch("{{ route('counter.recall') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ queue_id: queueId })
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    alert(json.message || 'Panggilan ulang dikirim ke layar Display TV.');
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal memanggil ulang antrean.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat memanggil ulang.');
            }
        }

        async function startServing() {
            const queueId = document.getElementById('form-queue-id').value;
            if (!queueId) return;
            try {
                const res = await fetch("{{ route('counter.startServing') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ queue_id: queueId })
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal memulai pelayanan.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi.');
            }
        }

        async function skipQueue() {
            if (!confirm('Apakah Anda yakin ingin melewati nomor antrean ini karena pengunjung tidak hadir?')) return;
            const queueId = document.getElementById('form-queue-id').value;
            if (!queueId) return;
            try {
                const res = await fetch("{{ route('counter.skip') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ queue_id: queueId })
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    alert(json.message || 'Antrean berhasil dilewati.');
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal melewati antrean.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi saat melewati antrean.');
            }
        }

        async function recallSkippedQueue(queueId, ticketCode) {
            if (!confirm(`Kembalikan antrean ${ticketCode} ke urutan paling depan untuk dipanggil berikutnya?`)) return;
            try {
                const res = await fetch("{{ route('counter.recallSkipped') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ queue_id: queueId })
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    alert(json.message || `Antrean ${ticketCode} berhasil dikembalikan ke urutan terdepan.`);
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal memanggil kembali antrean terlewat.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat memproses antrean terlewat.');
            }
        }

        async function submitComplete(e) {
            e.preventDefault();
            const queueId = document.getElementById('form-queue-id').value;
            if (!queueId) return;

            const pdpConsent = document.getElementById('pdp_consent');
            if (pdpConsent && !pdpConsent.checked) {
                alert('Pernyataan persetujuan UU Perlindungan Data Pribadi (UU PDP) wajib disetujui.');
                return;
            }

            const payload = {
                queue_id: queueId,
                full_name: document.getElementById('full_name').value,
                phone_number: document.getElementById('phone_number').value,
                email: document.getElementById('email').value,
                institution_type: document.getElementById('institution_type').value,
                institution_name: document.getElementById('institution_name').value,
                data_needs_description: document.getElementById('data_needs_description').value,
                pdp_consent: true,
            };

            try {
                const res = await fetch("{{ route('counter.complete') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.status === 'success') {
                    alert('Data buku tamu tersimpan dan antrean telah diselesaikan.');
                    window.location.reload();
                } else {
                    alert(json.message || 'Gagal menyimpan buku tamu.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kendala saat menyimpan data.');
            }
        }
    </script>
</body>
</html>
