@extends('layouts.workspace', [
    'activeNav' => 'services',
    'pageTitle' => 'Pengelolaan Kategori Layanan PST',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        <x-flash-alerts />

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="services-heading" class="text-lg font-bold text-[#0F172A]">Daftar Layanan Statistik Terpadu</h1>
                <p class="text-xs text-[#475569] mt-0.5">Kelola nama kategori layanan, kode prefix tiket, dan urutan tampilan anjungan Kiosk.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-service-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Layanan Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="services-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Prefix</th>
                            <th scope="col" class="py-3 px-4">Nama Layanan</th>
                            <th scope="col" class="py-3 px-4">Deskripsi</th>
                            <th scope="col" class="py-3 px-4 text-center">Urutan</th>
                            <th scope="col" class="py-3 px-4 text-center">Total Kunjungan</th>
                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($services as $s)
                            @php
                                $editPayload = [
                                    'action' => route('admin.services.update', $s->id),
                                    'name' => $s->name,
                                    'code_prefix' => $s->code_prefix,
                                    'description' => $s->description ?? '',
                                    'order_priority' => $s->order_priority,
                                    'is_active' => (bool) $s->is_active,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <span class="w-8 h-8 rounded-md bg-blue-50 border border-blue-200 text-[#0B4F9C] font-mono font-bold text-sm flex items-center justify-center">
                                        {{ $s->code_prefix }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#0F172A]">{{ $s->name }}</td>
                                <td class="py-3.5 px-4 text-xs text-[#475569] max-w-sm">{{ $s->description ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-center font-mono text-xs font-semibold tabular-nums-strict">{{ $s->order_priority }}</td>
                                <td class="py-3.5 px-4 text-center font-mono text-xs font-bold text-[#0A2540] tabular-nums-strict">{{ $s->queues_count }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$s->is_active ? 'green' : 'neutral'">
                                        {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-edit-service
                                                data-payload="{{ json_encode($editPayload) }}"
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.services.toggle', $s->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $s->is_active ? 'border border-orange-200 bg-orange-50 hover:bg-orange-100 text-[#C2410C]' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                {{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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

    <x-modal id="create-service-modal" title="Tambah Layanan PST Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-service-prefix" class="block text-xs font-semibold text-[#0F172A] mb-1">Kode Prefix Tiket (1 Huruf)</label>
                <input type="text" id="create-service-prefix" name="code_prefix" required maxlength="4" autofocus placeholder="Misal: E"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono uppercase font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="create-service-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Layanan</label>
                <input type="text" id="create-service-name" name="name" required placeholder="Contoh: Pojok Statistik Desa"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-service-desc" class="block text-xs font-semibold text-[#0F172A] mb-1">Deskripsi Singkat</label>
                <textarea id="create-service-desc" name="description" rows="2" placeholder="Uraian layanan..."
                          class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]"></textarea>
            </div>
            <div>
                <label for="create-service-order" class="block text-xs font-semibold text-[#0F172A] mb-1">Urutan Tampilan</label>
                <input type="number" id="create-service-order" name="order_priority" value="5" min="1" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-service-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Layanan</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="edit-service-modal" title="Edit Data Layanan PST" max-width="max-w-md">
        <form id="edit-service-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit-service-prefix" class="block text-xs font-semibold text-[#0F172A] mb-1">Kode Prefix Tiket</label>
                <input type="text" id="edit-service-prefix" name="code_prefix" required maxlength="4" autofocus
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono uppercase font-bold text-[#0F172A]">
            </div>
            <div>
                <label for="edit-service-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Layanan</label>
                <input type="text" id="edit-service-name" name="name" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="edit-service-desc" class="block text-xs font-semibold text-[#0F172A] mb-1">Deskripsi</label>
                <textarea id="edit-service-desc" name="description" rows="2"
                          class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]"></textarea>
            </div>
            <div>
                <label for="edit-service-order" class="block text-xs font-semibold text-[#0F172A] mb-1">Urutan Prioritas</label>
                <input type="number" id="edit-service-order" name="order_priority" min="1" required
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <label for="edit-service-active" class="inline-flex items-center gap-2 text-xs font-semibold text-[#0F172A] cursor-pointer">
                <input type="checkbox" id="edit-service-active" name="is_active" value="1" class="h-4 w-4 rounded border-[#CBD5E1] accent-[#0B4F9C]">
                <span>Layanan Aktif</span>
            </label>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="edit-service-modal"
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
        document.querySelectorAll('[data-edit-service]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('edit-service-form').action = data.action || '';
                document.getElementById('edit-service-prefix').value = data.code_prefix || '';
                document.getElementById('edit-service-name').value = data.name || '';
                document.getElementById('edit-service-desc').value = data.description || '';
                document.getElementById('edit-service-order').value = data.order_priority || 1;
                document.getElementById('edit-service-active').checked = Boolean(data.is_active);
                window.PstUi.openModal('edit-service-modal');
            });
        });
    });
</script>
@endpush
