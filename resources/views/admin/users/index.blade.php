<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna Sistem - Administrator PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none">
    <!-- Navbar Admin -->
    <header class="bg-blue-950 text-white shadow-md py-4 px-8 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-blue-900 font-black text-xl shadow hover:scale-105 transition-transform">
                BPS
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-wide">Pengelolaan Akun Pengguna & Petugas Loket</h1>
                <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <nav class="flex space-x-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="text-blue-200 hover:text-white transition pb-1">Dashboard</a>
                <a href="{{ route('admin.reports.index') }}" class="text-blue-200 hover:text-white transition pb-1">Rekap Laporan</a>
                <a href="{{ route('admin.services.index') }}" class="text-blue-200 hover:text-white transition pb-1">Layanan</a>
                <a href="{{ route('admin.counters.index') }}" class="text-blue-200 hover:text-white transition pb-1">Loket</a>
                <a href="{{ route('admin.users.index') }}" class="text-emerald-400 border-b-2 border-emerald-400 pb-1">Pengguna</a>
                <a href="{{ route('counter.index') }}" class="text-blue-200 hover:text-white transition pb-1">Buka Loket</a>
            </nav>

            <div class="border-l border-blue-800 pl-6 flex items-center space-x-3">
                <span class="text-xs text-blue-200">Admin: <strong>{{ Auth::user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition shadow">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <span class="flex items-center space-x-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm shadow-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-800">Daftar Akun Pengguna Sistem</h2>
                <p class="text-slate-500 text-sm mt-0.5">Tambah akun petugas loket baru, kelola status aktif/nonaktif, dan atur ulang kata sandi dinas.</p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-4 py-2.5 rounded-xl shadow transition text-sm flex items-center space-x-2">
                <span>+</span>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <!-- Tabel Pengguna -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5">Nama Pegawai</th>
                            <th class="py-3.5 px-5">Username</th>
                            <th class="py-3.5 px-5">Email Dinas</th>
                            <th class="py-3.5 px-5 text-center">Peran (Role)</th>
                            <th class="py-3.5 px-5 text-center">Status Akun</th>
                            <th class="py-3.5 px-5 text-center">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                    @if($u->id === Auth::id())
                                        <span class="text-[10px] text-blue-600 font-extrabold uppercase">(Akun Anda)</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 font-mono font-semibold text-slate-700">{{ $u->username }}</td>
                                <td class="py-4 px-5 text-xs text-slate-600">{{ $u->email }}</td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button"
                                            onclick="openResetModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->username }}')"
                                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                            Reset Password
                                        </button>

                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="text-xs font-bold px-3 py-1.5 rounded-lg transition {{ $u->is_active ? 'bg-rose-100 hover:bg-rose-200 text-rose-800' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800' }}">
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
            </div>
        </div>
    </main>

    <!-- Modal Tambah Pengguna -->
    <div id="create-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
            <h3 class="text-xl font-black text-slate-800 mb-4">Tambah Akun Pegawai / Petugas Baru</h3>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap Pegawai</label>
                    <input type="text" name="name" required placeholder="Contoh: Rina Melati, S.Tr.Stat."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Pengguna (Username)</label>
                    <input type="text" name="username" required placeholder="Contoh: petugas2"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Alamat Email Dinas</label>
                    <input type="email" name="email" required placeholder="petugas2@bps.go.id"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Peran Akses</label>
                    <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="petugas" selected>Petugas Loket Pelayanan</option>
                        <option value="admin">Administrator PST</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kata Sandi Awal</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="pt-3 flex justify-end space-x-2 border-t">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-800 font-bold text-xs text-white shadow">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div id="reset-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
            <h3 class="text-xl font-black text-slate-800 mb-2">Atur Ulang Kata Sandi</h3>
            <p id="reset-modal-desc" class="text-xs text-slate-500 mb-4"></p>
            <form id="reset-form" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kata Sandi Baru</label>
                    <input type="password" name="new_password" required placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div class="pt-3 flex justify-end space-x-2 border-t">
                    <button type="button" onclick="closeResetModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-800 font-bold text-xs text-white shadow">Atur Ulang Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('create-modal').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('create-modal').classList.add('hidden');
        }
        function openResetModal(id, name, username) {
            document.getElementById('reset-form').action = `/admin/users/${id}/reset-password`;
            document.getElementById('reset-modal-desc').innerText = `Masukkan kata sandi baru untuk akun ${name} (${username}).`;
            document.getElementById('reset-modal').classList.remove('hidden');
        }
        function closeResetModal() {
            document.getElementById('reset-modal').classList.add('hidden');
        }
    </script>
</body>
</html>
