@extends('layouts.workspace', [
    'activeNav' => 'users',
    'pageTitle' => 'Pengelolaan Akun Pengguna & Petugas Loket',
    'pageSubtitle' => 'Badan Pusat Statistik Kota Tasikmalaya',
])

@section('content')
    <div class="space-y-6">
        <x-flash-alerts />

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 id="users-heading" class="text-lg font-bold text-[#0F172A]">Daftar Akun Pengguna Sistem</h1>
                <p class="text-xs text-[#475569] mt-0.5">Tambah akun petugas loket baru, kelola status aktif/nonaktif, dan atur ulang kata sandi dinas.</p>
            </div>
            <button type="button"
                    data-dialog-open="create-user-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <section class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden">
            <x-table-wrapper labelled-by="users-heading">
                <table class="w-full text-left text-sm text-[#334155]">
                    <thead class="bg-[#EDF1F6] border-b border-[#DCE3EC] text-[11px] font-semibold uppercase tracking-wider text-[#475569]">
                        <tr>
                            <th scope="col" class="py-3 px-4">Nama Pegawai</th>
                            <th scope="col" class="py-3 px-4">Username</th>
                            <th scope="col" class="py-3 px-4">Email Dinas</th>
                            <th scope="col" class="py-3 px-4 text-center">Peran</th>
                            <th scope="col" class="py-3 px-4 text-center">Status Akun</th>
                            <th scope="col" class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EDF1F6]">
                        @foreach($users as $u)
                            @php
                                $resetPayload = [
                                    'action' => route('admin.users.resetPassword', $u->id),
                                    'name' => $u->name,
                                    'username' => $u->username,
                                ];
                            @endphp
                            <tr class="hover:bg-[#F8FAFC]">
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-[#0F172A]">{{ $u->name }}</div>
                                    @if($u->id === Auth::id())
                                        <span class="text-[10px] font-bold uppercase text-[#0B4F9C]">Akun Anda</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs font-semibold text-[#334155]">{{ $u->username }}</td>
                                <td class="py-3.5 px-4 text-xs text-[#475569]">{{ $u->email }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$u->role === 'admin' ? 'teal' : 'cobalt'">
                                        {{ strtoupper($u->role) }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <x-badge :tone="$u->is_active ? 'green' : 'danger'">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <button type="button"
                                                data-reset-user
                                                data-payload="{{ json_encode($resetPayload) }}"
                                                class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#EDF1F6] px-2.5 py-1 text-xs font-semibold text-[#0F172A]">
                                            Reset Password
                                        </button>

                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $u->is_active ? 'border border-red-200 bg-red-50 hover:bg-red-100 text-red-700' : 'border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-[#15803D]' }}">
                                                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>
        </section>
    </div>

    <x-modal id="create-user-modal" title="Tambah Akun Pegawai / Petugas Baru" :reset-on-close="true" max-width="max-w-md">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="create-user-name" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Lengkap Pegawai</label>
                <input type="text" id="create-user-name" name="name" required autofocus placeholder="Contoh: Rina Melati, S.Tr.Stat."
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-username" class="block text-xs font-semibold text-[#0F172A] mb-1">Nama Pengguna (Username)</label>
                <input type="text" id="create-user-username" name="username" required autocorrect="off" autocapitalize="none" spellcheck="false" placeholder="Contoh: petugas2"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm font-mono text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-email" class="block text-xs font-semibold text-[#0F172A] mb-1">Alamat Email Dinas</label>
                <input type="email" id="create-user-email" name="email" required placeholder="petugas2@bps.go.id"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div>
                <label for="create-user-role" class="block text-xs font-semibold text-[#0F172A] mb-1">Peran Akses</label>
                <select id="create-user-role" name="role" required class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
                    <option value="petugas" selected>Petugas Loket Pelayanan</option>
                    <option value="admin">Administrator PST</option>
                </select>
            </div>
            <div>
                <label for="create-user-password" class="block text-xs font-semibold text-[#0F172A] mb-1">Kata Sandi Awal</label>
                <input type="password" id="create-user-password" name="password" required minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="create-user-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Simpan Pengguna</button>
            </div>
        </form>
    </x-modal>

    <x-modal id="reset-user-modal" title="Atur Ulang Kata Sandi" :reset-on-close="true" max-width="max-w-md">
        <p id="reset-modal-desc" class="text-xs text-[#475569] mb-4"></p>
        <form id="reset-user-form" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="reset-new-password" class="block text-xs font-semibold text-[#0F172A] mb-1">Kata Sandi Baru</label>
                <input type="password" id="reset-new-password" name="new_password" required minlength="6" autofocus autocomplete="new-password" placeholder="Minimal 6 karakter"
                       class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2 text-sm text-[#0F172A]">
            </div>
            <div class="pt-3 flex justify-end gap-2 border-t border-[#DCE3EC]">
                <button type="button" data-dialog-close="reset-user-modal"
                        class="rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] px-4 py-2 text-xs font-semibold text-[#334155]">Batal</button>
                <button type="submit"
                        class="rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2 text-xs font-semibold text-white">Atur Ulang Password</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-reset-user]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.dataset.payload || '{}');
                document.getElementById('reset-user-form').action = data.action || '';
                document.getElementById('reset-modal-desc').textContent =
                    `Masukkan kata sandi baru untuk akun ${data.name || ''} (${data.username || ''}).`;
                window.PstUi.openModal('reset-user-modal');
            });
        });
    });
</script>
@endpush
