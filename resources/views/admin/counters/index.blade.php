@extends('layouts.workspace', [
    'activeNav' => 'counters',
    'pageTitle' => 'Pengelolaan Meja Loket & Penugasan Layanan',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        <x-flash-alerts />

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="counters-heading" class="text-lg font-bold text-[#0F172A]">Daftar Meja Loket Pelayanan</h1>
                <p class="text-xs text-[#475569] mt-0.5">Atur nama loket, nomor fisik loket, dan kategori layanan yang ditugaskan.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-counter-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Loket Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="counters-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Nomor</th>
                            <th scope="col" class="py-3 px-4">Nama Loket</th>
                            <th scope="col" class="py-3 px-4">Kategori Layanan Ditugaskan</th>
                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($counters as $c)
                            @php
                                $editPayload = [
                                    'action' => route('admin.counters.update', $c->id),
                                    'name' => $c->name,
                                    'counter_number' => $c->counter_number,
                                    'service_ids' => $c->services->pluck('id')->all(),
                                    'is_active' => (bool) $c->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <span class="w-8 h-8 rounded-md bg-blue-50 border border-blue-200 text-[#0B4F9C] font-mono font-bold text-sm flex items-center justify-center tabular-nums-strict">
                                        {{ $c->counter_number }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#0F172A]">{{ $c->name }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($c->services as $srv)
                                            <x-badge tone="neutral">
                                                <span class="font-mono font-bold text-[#0B4F9C]">{{ $srv->code_prefix }}</span>
                                                <span>{{ $srv->name }}</span>
                                            </x-badge>
                                        @empty
                                            <span class="text-xs text-red-700">Belum ada penugasan layanan</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$c->is_active ? 'green' : 'neutral'">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-edit-counter
                                                data-payload="{{ json_encode($editPayload) }}"
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Edit Penugasan
                                        </button>
                                        <form action="{{ route('admin.counters.toggle', $c->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $c->is_active ? 'border border-orange-200 bg-orange-50 hover:bg-orange-100 text-[#C2410C]' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>

    <x-modal id="create-counter-modal" title="Tambah Meja Loket Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.counters.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-counter-number" class="block text-xs font-semibold text-[#0F172A] mb-1">Nomor Fisik Loket</label>
                <input type="number" id="create-counter-number" name="counter_number" required min="1" autofocus placeholder="Contoh: 3"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="create-counter-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Loket</label>
                <input type="text" id="create-counter-name" name="name" required placeholder="Contoh: Loket 3"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <fieldset>
                <legend class="text-xs font-semibold text-[#0F172A] mb-1.5">Penugasan Layanan PST</legend>
                <div class="space-y-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] p-3 max-h-40 overflow-y-auto">
                    @foreach($services as $srv)
                        <label class="flex items-center gap-2 text-xs font-medium text-[#334155] cursor-pointer">
                            <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" checked class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                            <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-counter-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Loket</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-counter-modal" title="Edit Meja Loket & Penugasan" max-width="max-w-md">
        <form id="edit-counter-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit-counter-number" class="block text-xs font-semibold text-[#0F172A] mb-1">Nomor Fisik Loket</label>
                <input type="number" id="edit-counter-number" name="counter_number" required min="1" autofocus
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="edit-counter-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Loket</label>
                <input type="text" id="edit-counter-name" name="name" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <fieldset>
                <legend class="text-xs font-semibold text-[#0F172A] mb-1.5">Penugasan Kategori Layanan</legend>
                <div class="space-y-2 rounded-lg border border-[#DCE3EC] bg-[#F8FAFC] p-3 max-h-40 overflow-y-auto">
                    @foreach($services as $srv)
                        <label class="flex items-center gap-2 text-xs font-medium text-[#334155] cursor-pointer">
                            <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" class="edit-counter-service-cb h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                            <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <label for="edit-counter-active" class="inline-flex items-center gap-2 text-xs font-semibold text-[#0F172A] cursor-pointer">
                <input type="checkbox" id="edit-counter-active" name="is_active" value="1" class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                <span>Loket Siap Operasional (Aktif)</span>
            </label>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="edit-counter-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Perubahan</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-edit-counter]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('edit-counter-form').action = data.action || '';
                document.getElementById('edit-counter-name').value = data.name || '';
                document.getElementById('edit-counter-number').value = data.counter_number ?? 1;
                document.getElementById('edit-counter-active').checked = Boolean(data.is_active);
                const ids = Array.isArray(data.service_ids) ? data.service_ids.map(Number) : [];
                document.querySelectorAll('.edit-counter-service-cb').forEach((cb) => {
                    cb.checked = ids.includes(Number(cb.value));
                });
                window.PstUi.openModal('edit-counter-modal');
            });
        });
    });
</script>
@endpush
