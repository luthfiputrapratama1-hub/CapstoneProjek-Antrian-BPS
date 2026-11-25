@extends('layouts.workspace', [
    'activeNav' => 'loket',
    'pageTitle' => 'Meja Kerja Pelayanan Loket',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    @php
        $status = $activeQueue?->status;
        $isServing = $status === 'serving';
        $isCalling = $status === 'calling';
        $stepState = $isServing ? 2 : ($isCalling ? 1 : 0);
    @endphp

    <div class="space-y-6">
        {{-- Counter Toolbar & 3-Step Workflow Indicator --}}
        <section class="bg-white rounded-xl border border-[#DCE3EC] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form action="{{ route('counter.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <label for="counter_id" class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Loket Bertugas:</label>
                <select name="counter_id" id="counter_id" onchange="this.form.submit()"
                        class="rounded-md border border-[#CBD5E1] bg-white px-3 py-2 text-sm font-semibold text-[#0F172A]">
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}" {{ $selectedCounter?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                <x-badge tone="cobalt" class="tabular-nums-strict">{{ $waitingCount }} Menunggu</x-badge>
            </form>

            <nav aria-label="Tahapan Layanan Loket">
                <ol class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 {{ $stepState === 0 ? 'border-[#0B4F9C] bg-blue-50 text-[#0B4F9C]' : 'border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]' }}" @if($stepState === 0) aria-current="step" @endif>
                        <span>1. Panggil</span>
                    </li>
                    <li aria-hidden="true" class="text-[#64748B]">→</li>
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 {{ $stepState === 1 || $stepState === 2 ? 'border-[#0B4F9C] bg-blue-50 text-[#0B4F9C]' : 'border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]' }}" @if($stepState === 1 || $stepState === 2) aria-current="step" @endif>
                        <span>2. Layani &amp; Buku Tamu</span>
                    </li>
                    <li aria-hidden="true" class="text-[#64748B]">→</li>
                    <li class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 border-[#DCE3EC] bg-[#F8FAFC] text-[#475569]">
                        <span>3. Selesai</span>
                    </li>
                </ol>
            </nav>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Left Column (1/3): Queue Control --}}
            <div class="space-y-5">
                {{-- Assigned Services --}}
                <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Kategori Layanan Loket</h2>
                        <span class="text-xs font-semibold text-[#0F172A]">{{ $selectedCounter?->name }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($selectedCounter?->services ?? [] as $srv)
                            <x-badge tone="neutral">
                                <span class="font-mono font-bold text-[#0B4F9C]">{{ $srv->code_prefix }}</span>
                                <span>{{ $srv->name }}</span>
                            </x-badge>
                        @empty
                            <span class="text-xs text-red-700">Loket belum memiliki penugasan layanan.</span>
                        @endforelse
                    </div>
                </section>

                {{-- Active Ticket Card --}}
                <section class="bg-white rounded-xl border border-[#DCE3EC] p-6 text-center">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Antrean Sedang Ditangani</span>
                    <div id="active-ticket-code" class="text-5xl font-mono font-bold text-[#0A2540] my-3 tracking-wider tabular-nums-strict">
                        {{ $activeQueue?->ticket_code ?? '---' }}
                    </div>
                    <div id="active-service-name" class="text-sm font-semibold text-[#0F172A]">
                        {{ $activeQueue?->service?->name ?? 'Belum ada antrean yang dipanggil' }}
                    </div>
                    <div class="mt-3 flex flex-col items-center gap-1.5">
                        <span id="active-status-badge">
                            @if($isServing)
                                <x-badge tone="green">Sedang Dilayani</x-badge>
                            @elseif($isCalling)
                                <x-badge tone="orange">Sedang Dipanggil</x-badge>
                            @else
                                <x-badge tone="neutral">Kosong</x-badge>
                            @endif
                        </span>
                        @if($isCalling)
                            <div class="text-xs font-medium text-[#475569] tabular-nums-strict">
                                Panggilan Ulang: <strong class="text-[#0F172A]">{{ $activeQueue->recall_count }} / 3</strong>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 space-y-2.5">
                        <button type="button"
                                id="btn-call-next"
                                {{ $activeQueue ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-[#15803D] hover:bg-emerald-800 disabled:opacity-40 disabled:pointer-events-none px-4 py-3 text-xs font-bold uppercase tracking-wider text-white transition-colors">
                            <x-icon name="megaphone" class="w-4 h-4" />
                            <span>Panggil Berikutnya</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button"
                                    id="btn-recall"
                                    {{ (!$isCalling || $activeQueue->recall_count >= 3) ? 'disabled' : '' }}
                                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] disabled:opacity-40 disabled:pointer-events-none px-3 py-2.5 text-xs font-semibold text-white tabular-nums-strict transition-colors">
                                <x-icon name="refresh" class="w-3.5 h-3.5" />
                                <span>Ulang ({{ $activeQueue?->recall_count ?? 0 }}/3)</span>
                            </button>

                            <button type="button"
                                    id="btn-skip"
                                    {{ (!$isCalling || !$canSkip) ? 'disabled' : '' }}
                                    title="{{ !$canSkip ? 'Tombol aktif setelah pemanggilan ulang ke-3' : 'Lewati antrean' }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-[#CBD5E1] bg-white hover:bg-red-50 hover:text-red-700 hover:border-red-300 disabled:opacity-40 disabled:pointer-events-none px-3 py-2.5 text-xs font-semibold text-[#334155] transition-colors">
                                <span>Lewati (Skip)</span>
                            </button>
                        </div>

                        @if($isCalling && !$canSkip)
                            <p class="flex items-center justify-center gap-1.5 rounded-md border border-orange-200 bg-orange-50 px-3 py-1.5 text-[11px] font-medium text-[#C2410C]">
                                <x-icon name="warning" class="w-3.5 h-3.5 shrink-0" />
                                <span>Tombol Lewati aktif setelah panggilan ke-3</span>
                            </p>
                        @endif

                        <button type="button"
                                id="btn-start-serve"
                                {{ !$isCalling ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-[#0A2540] hover:bg-[#0F172A] disabled:opacity-40 disabled:pointer-events-none px-4 py-2.5 text-xs font-bold text-white transition-colors">
                            <x-icon name="edit" class="w-4 h-4" />
                            <span>Mulai Melayani (Buka Buku Tamu)</span>
                        </button>
                    </div>
                </section>

                {{-- Skipped Queues Today --}}
                @if(isset($skippedQueues) && $skippedQueues->count() > 0)
                    <section class="bg-white rounded-xl border border-[#DCE3EC] p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-[#475569]">Antrean Terlewat Hari Ini</h2>
                            <x-badge tone="orange" class="tabular-nums-strict">{{ $skippedQueues->count() }}</x-badge>
                        </div>
                        <p class="text-[11px] text-[#64748B] mb-3">Dapat dipanggil kembali satu kali ke urutan paling depan:</p>
                        <div class="space-y-2 max-h-52 overflow-y-auto">
                            @foreach($skippedQueues as $sq)
                                <div class="flex items-center justify-between gap-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] px-3 py-2 text-xs">
                                    <div class="min-w-0">
                                        <span class="font-mono font-bold text-[#0A2540] tabular-nums-strict">{{ $sq->ticket_code }}</span>
                                        <div class="text-[11px] text-[#475569] truncate">{{ $sq->service?->name }}</div>
                                    </div>
                                    <button type="button"
                                            data-recall-skipped-id="{{ $sq->id }}"
                                            data-recall-skipped-code="{{ $sq->ticket_code }}"
                                            class="btn-recall-skipped shrink-0 rounded-md bg-[#C2410C] hover:bg-orange-800 px-2.5 py-1 text-[11px] font-semibold text-white">
                                        Panggil Kembali
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Right Column (2/3): Guest Book Form --}}
            <section class="lg:col-span-2 bg-white rounded-xl border border-[#DCE3EC] p-6 sm:p-7" aria-labelledby="guestbook-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#DCE3EC] pb-4 mb-5">
                    <div>
                        <h2 id="guestbook-heading" class="text-lg font-bold text-[#0F172A]">Formulir Buku Tamu Digital PST</h2>
                        <p class="text-xs text-[#475569] mt-0.5">Wajib diisi dan disimpan saat melayani pengunjung sebelum menyelesaikan antrean</p>
                    </div>
                    <x-badge tone="neutral">SKF-05 BPS Terpadu</x-badge>
                </div>

                {{-- Focusable Validation Error Summary --}}
                <div id="guestbook-error-summary"
                     role="alert"
                     tabindex="-1"
                     class="hidden mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-xs text-red-900">
                    <div class="flex items-center gap-2 font-bold">
                        <x-icon name="warning" class="w-4 h-4 text-red-700" />
                        <span>Periksa kembali isian formulir buku tamu:</span>
                    </div>
                    <ul data-error-list class="mt-2 list-disc list-inside space-y-1"></ul>
                </div>

                <form id="form-guestbook" class="space-y-5" novalidate>
                    <input type="hidden" id="form-queue-id" value="{{ $activeQueue?->id ?? '' }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Nama Lengkap Pengunjung <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="full_name"
                                   name="full_name"
                                   required
                                   aria-invalid="false"
                                   data-error="full_name-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="Contoh: Dedi Mulyadi"
                                   value="{{ $activeQueue?->guestBook?->full_name ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="full_name-error" class="field-error-text"></p>
                        </div>

                        <div>
                            <label for="phone_number" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Nomor WhatsApp / HP <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="phone_number"
                                   name="phone_number"
                                   inputmode="tel"
                                   required
                                   aria-invalid="false"
                                   data-error="phone_number-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ $activeQueue?->guestBook?->phone_number ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="phone_number-error" class="field-error-text"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Alamat Email (Opsional)
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   aria-invalid="false"
                                   data-error="email-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   placeholder="nama@instansi.go.id"
                                   value="{{ $activeQueue?->guestBook?->email ?? '' }}"
                                   class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                            <p id="email-error" class="field-error-text"></p>
                        </div>

                        <div>
                            <label for="institution_type" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                                Kategori Pemohon / Asal <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <select id="institution_type"
                                    name="institution_type"
                                    required
                                    aria-invalid="false"
                                    data-error="institution_type-error"
                                    {{ !$isServing ? 'disabled' : '' }}
                                    class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                                <option value="pendidikan" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pendidikan' ? 'selected' : '' }}>Lembaga Pendidikan / Mahasiswa / Peneliti</option>
                                <option value="pemerintah" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'pemerintah' ? 'selected' : '' }}>Instansi Pemerintah (K/L/Pemda)</option>
                                <option value="swasta" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'swasta' ? 'selected' : '' }}>Sektor Swasta / Bisnis</option>
                                <option value="lsm" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'lsm' ? 'selected' : '' }}>LSM / Organisasi Masyarakat / Media</option>
                                <option value="umum" {{ ($activeQueue?->guestBook?->institution_type ?? '') == 'umum' ? 'selected' : '' }}>Individu / Masyarakat Umum</option>
                            </select>
                            <p id="institution_type-error" class="field-error-text"></p>
                        </div>
                    </div>

                    <div>
                        <label for="institution_name" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                            Nama Instansi / Universitas / Lembaga Asal <span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="institution_name"
                               name="institution_name"
                               required
                               aria-invalid="false"
                               data-error="institution_name-error"
                               {{ !$isServing ? 'disabled' : '' }}
                               placeholder="Contoh: Universitas Siliwangi / Dinas Kesehatan Kota Tasikmalaya"
                               value="{{ $activeQueue?->guestBook?->institution_name ?? '' }}"
                               class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">
                        <p id="institution_name-error" class="field-error-text"></p>
                    </div>

                    <div>
                        <label for="data_needs_description" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                            Rincian Kebutuhan Data / Maksud Konsultasi <span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <textarea id="data_needs_description"
                                  name="data_needs_description"
                                  rows="3"
                                  required
                                  aria-invalid="false"
                                  data-error="data_needs_description-error"
                                  {{ !$isServing ? 'disabled' : '' }}
                                  placeholder="Tuliskan data statistik yang dibutuhkan atau topik konsultasi teknis..."
                                  class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] disabled:bg-[#F4F6F9] disabled:text-[#64748B]">{{ $activeQueue?->guestBook?->data_needs_description ?? '' }}</textarea>
                        <p id="data_needs_description-error" class="field-error-text"></p>
                    </div>

                    <div class="rounded-lg border border-blue-200 bg-blue-50/60 p-4">
                        <label for="pdp_consent" class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox"
                                   id="pdp_consent"
                                   name="pdp_consent"
                                   value="1"
                                   required
                                   aria-invalid="false"
                                   data-error="pdp_consent-error"
                                   {{ !$isServing ? 'disabled' : '' }}
                                   class="mt-0.5 h-4 w-4 rounded border-[#CBD5E1] text-[#0B4F9C] accent-[#0B4F9C]">
                            <span class="text-xs text-[#334155] leading-relaxed">
                                <strong class="font-bold text-[#0A2540]">Pernyataan UU Pelindungan Data Pribadi (UU PDP):</strong>
                                Pengunjung menyatakan data identitas dan kontak di atas adalah benar, serta menyetujui penggunaannya secara kedinasan oleh BPS untuk pencatatan buku tamu dan evaluasi pelayanan statistik publik.
                            </span>
                        </label>
                        <p id="pdp_consent-error" class="field-error-text"></p>
                    </div>

                    <div class="pt-4 border-t border-[#DCE3EC] flex justify-end">
                        <button type="submit"
                                id="btn-submit-complete"
                                {{ !$isServing ? 'disabled' : '' }}
                                class="inline-flex items-center gap-2 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] disabled:opacity-40 disabled:pointer-events-none px-6 py-3 text-xs font-bold uppercase tracking-wider text-white transition-colors">
                            <x-icon name="check" class="w-4 h-4" />
                            <span>Simpan Buku Tamu &amp; Selesaikan Antrean</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const selectedCounterId = {{ $selectedCounter?->id ?? 1 }};
        const queueIdInput = document.getElementById('form-queue-id');
        const guestbookForm = document.getElementById('form-guestbook');
        const errorSummary = document.getElementById('guestbook-error-summary');
        const { showToast, showConfirm, renderFormErrors, clearFormErrors } = window.PstUi;

        async function postAction(url, payload, triggerBtn) {
            if (triggerBtn) triggerBtn.disabled = true;
            let keepDisabled = false;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    keepDisabled = true;
                }
                return { res, json };
            } finally {
                if (triggerBtn && !keepDisabled) triggerBtn.disabled = false;
            }
        }

        const btnCallNext = document.getElementById('btn-call-next');
        btnCallNext?.addEventListener('click', async () => {
            try {
                const { res, json } = await postAction("{{ route('counter.callNext') }}", { counter_id: selectedCounterId }, btnCallNext);
                if (res.ok && json.status === 'success') {
                    window.location.reload();
                } else {
                    showToast(json.message || 'Tidak ada antrean menunggu.', 'info');
                }
            } catch (e) {
                console.error(e);
                showToast('Gagal memanggil antrean berikutnya.', 'error');
            }
        });

        const btnRecall = document.getElementById('btn-recall');
        btnRecall?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            try {
                const { res, json } = await postAction("{{ route('counter.recall') }}", { queue_id: queueId }, btnRecall);
                if (res.ok && json.status === 'success') {
                    showToast(json.message || 'Panggilan ulang dikirim ke layar Display TV.', 'success');
                    window.setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast(json.message || 'Gagal memanggil ulang antrean.', 'warning');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan saat memanggil ulang.', 'error');
            }
        });

        const btnStartServe = document.getElementById('btn-start-serve');
        btnStartServe?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            try {
                const { res, json } = await postAction("{{ route('counter.startServing') }}", { queue_id: queueId }, btnStartServe);
                if (res.ok && json.status === 'success') {
                    window.location.reload();
                } else {
                    showToast(json.message || 'Gagal memulai pelayanan.', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan koneksi.', 'error');
            }
        });

        const btnSkip = document.getElementById('btn-skip');
        btnSkip?.addEventListener('click', async () => {
            const queueId = queueIdInput?.value;
            if (!queueId) return;
            const confirmed = await showConfirm({
                title: 'Lewati Nomor Antrean?',
                message: 'Antrean ini akan ditandai terlewat karena pengunjung tidak hadir di loket.',
                confirmLabel: 'Ya, Lewati Antrean',
                cancelLabel: 'Batal',
                tone: 'danger',
            });
            if (!confirmed) return;

            try {
                const { res, json } = await postAction("{{ route('counter.skip') }}", { queue_id: queueId }, btnSkip);
                if (res.ok && json.status === 'success') {
                    showToast(json.message || 'Antrean berhasil dilewati.', 'success');
                    window.setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(json.message || 'Gagal melewati antrean.', 'warning');
                }
            } catch (e) {
                console.error(e);
                showToast('Terjadi kesalahan koneksi saat melewati antrean.', 'error');
            }
        });

        document.querySelectorAll('.btn-recall-skipped').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const queueId = btn.dataset.recallSkippedId;
                const ticketCode = btn.dataset.recallSkippedCode;
                const confirmed = await showConfirm({
                    title: `Panggil Kembali ${ticketCode}?`,
                    message: `Antrean ${ticketCode} akan dikembalikan ke urutan paling depan layanannya (maksimal 1 kali).`,
                    confirmLabel: 'Kembalikan ke Antrean',
                    cancelLabel: 'Batal',
                    tone: 'primary',
                });
                if (!confirmed) return;

                try {
                    const { res, json } = await postAction("{{ route('counter.recallSkipped') }}", { queue_id: queueId }, btn);
                    if (res.ok && json.status === 'success') {
                        showToast(json.message || `Antrean ${ticketCode} dikembalikan ke antrean terdepan.`, 'success');
                        window.setTimeout(() => window.location.reload(), 600);
                    } else {
                        showToast(json.message || 'Gagal memanggil kembali antrean terlewat.', 'warning');
                    }
                } catch (e) {
                    console.error(e);
                    showToast('Terjadi kesalahan saat memproses antrean terlewat.', 'error');
                }
            });
        });

        guestbookForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const queueId = queueIdInput?.value;
            if (!queueId) return;

            clearFormErrors(guestbookForm, errorSummary);

            const clientErrors = {};
            const fullName = document.getElementById('full_name').value.trim();
            const phone = document.getElementById('phone_number').value.trim();
            const email = document.getElementById('email').value.trim();
            const instType = document.getElementById('institution_type').value;
            const instName = document.getElementById('institution_name').value.trim();
            const needsDesc = document.getElementById('data_needs_description').value.trim();
            const pdpConsent = document.getElementById('pdp_consent').checked;

            if (!fullName) clientErrors.full_name = ['Nama lengkap pengunjung wajib diisi.'];
            if (!phone) clientErrors.phone_number = ['Nomor WhatsApp / HP pengunjung wajib diisi.'];
            if (!instName) clientErrors.institution_name = ['Nama instansi asal wajib diisi.'];
            if (!needsDesc) clientErrors.data_needs_description = ['Rincian kebutuhan data wajib diisi.'];
            if (!pdpConsent) clientErrors.pdp_consent = ['Pernyataan persetujuan UU PDP wajib disetujui.'];

            if (Object.keys(clientErrors).length > 0) {
                renderFormErrors(guestbookForm, errorSummary, clientErrors);
                return;
            }

            const submitBtn = document.getElementById('btn-submit-complete');
            try {
                const { res, json } = await postAction(
                    "{{ route('counter.complete') }}",
                    {
                        queue_id: queueId,
                        full_name: fullName,
                        phone_number: phone,
                        email: email || null,
                        institution_type: instType,
                        institution_name: instName,
                        data_needs_description: needsDesc,
                        pdp_consent: true,
                    },
                    submitBtn
                );

                if (res.ok && json.status === 'success') {
                    showToast('Buku tamu tersimpan dan antrean selesai.', 'success');
                    window.setTimeout(() => window.location.reload(), 500);
                } else {
                    renderFormErrors(guestbookForm, errorSummary, json.errors || {}, json.message || 'Gagal menyimpan buku tamu.');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kendala saat menyimpan data buku tamu.', 'error');
            }
        });
    });
</script>
@endpush
