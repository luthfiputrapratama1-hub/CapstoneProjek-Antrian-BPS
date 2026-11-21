<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi - PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between selection:bg-none">
    <!-- Header -->
    <header class="bg-blue-900 text-white shadow-md py-4 px-8 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" title="Kembali ke Beranda Portal PST"
                class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-blue-900 font-black text-xl shadow hover:scale-105 transition-transform">
                BPS
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-wide">Pengaturan Keamanan Akun</h1>
                <p class="text-xs text-blue-200">Pelayanan Statistik Terpadu BPS</p>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="text-xs bg-blue-800 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl transition">
                    ← Kembali ke Dashboard
                </a>
            @else
                <a href="{{ route('counter.index') }}" class="text-xs bg-blue-800 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl transition">
                    ← Kembali ke Meja Loket
                </a>
            @endif
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-md w-full mx-auto p-6 flex-1 flex flex-col justify-center">
        <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-200">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-blue-100 text-blue-900 rounded-2xl flex items-center justify-center font-bold text-2xl mx-auto mb-3">
                    🔒
                </div>
                <h2 class="text-2xl font-black text-slate-800">Ubah Kata Sandi</h2>
                <p class="text-slate-500 text-sm mt-1">Perbarui kata sandi akun <strong>{{ Auth::user()->username }}</strong> untuk menjaga keamanan sistem.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center space-x-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="current_password" name="current_password" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm">
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-xl transition shadow-md active:scale-[0.99] text-sm">
                        Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500">
        Badan Pusat Statistik - Pelayanan Statistik Terpadu © 2026
    </footer>
</body>
</html>
