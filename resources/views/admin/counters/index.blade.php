<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Loket & Penugasan Layanan - Administrator PST BPS</title>
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
                <h1 class="text-xl font-bold tracking-wide">Pengelolaan Meja Loket & Penugasan Layanan</h1>
                <p class="text-xs text-blue-200">Badan Pusat Statistik Kota Tasikmalaya</p>
            </div>
        </div>

        <div class="flex items-center space-x-6">
            <nav class="flex space-x-4 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="text-blue-200 hover:text-white transition pb-1">Dashboard</a>
                <a href="{{ route('admin.reports.index') }}" class="text-blue-200 hover:text-white transition pb-1">Rekap Laporan</a>
                <a href="{{ route('admin.services.index') }}" class="text-blue-200 hover:text-white transition pb-1">Layanan</a>
                <a href="{{ route('admin.counters.index') }}" class="text-emerald-400 border-b-2 border-emerald-400 pb-1">Loket</a>
                <a href="{{ route('admin.users.index') }}" class="text-blue-200 hover:text-white transition pb-1">Pengguna</a>
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
                <h2 class="text-2xl font-black text-slate-800">Daftar Meja Loket Pelayanan</h2>
                <p class="text-slate-500 text-sm mt-0.5">Atur nama loket, nomor fisik loket, dan tentukan kategori layanan yang ditugaskan.</p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-4 py-2.5 rounded-xl shadow transition text-sm flex items-center space-x-2">
                <span>+</span>
                <span>Tambah Loket Baru</span>
            </button>
        </div>

        <!-- Tabel Loket -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="py-3.5 px-5">Nomor Loket</th>
                            <th class="py-3.5 px-5">Nama Loket</th>
                            <th class="py-3.5 px-5">Kategori Layanan Ditugaskan</th>
                            <th class="py-3.5 px-5 text-center">Status</th>
                            <th class="py-3.5 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($counters as $c)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-5">
                                    <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-900 font-mono font-black text-base flex items-center justify-center shadow-inner">
                                        {{ $c->counter_number }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 font-bold text-slate-900">{{ $c->name }}</td>
                                <td class="py-4 px-5">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($c->services as $srv)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                <span class="w-2 h-2 rounded-full bg-blue-600 mr-1.5"></span>
                                                <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-red-500 italic">Belum ada penugasan layanan</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $c->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button"
                                            onclick="openEditModal({{ $c->id }}, '{{ addslashes($c->name) }}', {{ $c->counter_number }}, {{ json_encode($c->services->pluck('id')->all()) }}, {{ $c->is_active ? 'true' : 'false' }})"
                                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                            Edit Penugasan
                                        </button>
                                        <form action="{{ route('admin.counters.toggle', $c->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-xs font-bold px-3 py-1.5 rounded-lg transition {{ $c->is_active ? 'bg-amber-100 hover:bg-amber-200 text-amber-800' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800' }}">
                                                {{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Loket -->
    <div id="create-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
            <h3 class="text-xl font-black text-slate-800 mb-4">Tambah Meja Loket Baru</h3>
            <form action="{{ route('admin.counters.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nomor Fisik Loket</label>
                    <input type="number" name="counter_number" required min="1" placeholder="Contoh: 3"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Loket</label>
                    <input type="text" name="name" required placeholder="Contoh: Loket 3"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Penugasan Layanan PST</label>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-200 max-h-40 overflow-y-auto">
                        @foreach($services as $srv)
                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" checked class="h-4 w-4 text-blue-600 rounded">
                                <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="pt-3 flex justify-end space-x-2 border-t">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-800 font-bold text-xs text-white shadow">Simpan Loket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Loket & Penugasan Layanan -->
    <div id="edit-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
            <h3 class="text-xl font-black text-slate-800 mb-4">Edit Meja Loket & Penugasan</h3>
            <form id="edit-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nomor Fisik Loket</label>
                    <input type="number" id="edit-counter-number" name="counter_number" required min="1"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Loket</label>
                    <input type="text" id="edit-name" name="name" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Penugasan Kategori Layanan</label>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-200 max-h-40 overflow-y-auto">
                        @foreach($services as $srv)
                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" id="edit-service-{{ $srv->id }}" class="edit-service-cb h-4 w-4 text-blue-600 rounded">
                                <span>{{ $srv->name }} ({{ $srv->code_prefix }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="h-4 w-4 text-blue-600 rounded">
                    <label for="edit-is-active" class="text-xs font-bold text-slate-700">Loket Siap Operasional (Aktif)</label>
                </div>
                <div class="pt-3 flex justify-end space-x-2 border-t">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-800 font-bold text-xs text-white shadow">Simpan Perubahan</button>
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
        function openEditModal(id, name, counterNumber, assignedServiceIds, isActive) {
            document.getElementById('edit-form').action = `/admin/counters/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-counter-number').value = counterNumber;
            document.getElementById('edit-is-active').checked = isActive;

            document.querySelectorAll('.edit-service-cb').forEach(cb => {
                cb.checked = assignedServiceIds.includes(parseInt(cb.value));
            });

            document.getElementById('edit-modal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
    </script>
</body>
</html>
