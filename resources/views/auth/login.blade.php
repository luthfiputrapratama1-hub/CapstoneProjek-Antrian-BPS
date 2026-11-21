<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-200">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-900 text-white rounded-2xl flex items-center justify-center font-black text-2xl mx-auto shadow-md mb-4">
                BPS
            </div>
            <h1 class="text-2xl font-black text-slate-800">Pelayanan Statistik Terpadu</h1>
            <p class="text-slate-500 text-sm mt-1">Masuk untuk mengoperasikan Loket atau Dashboard Admin</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Nama Pengguna (Username)</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    placeholder="Contoh: petugas1 atau admin"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kata Sandi (Password)</label>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm">
            </div>

            <button type="submit"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-blue-950/20 active:scale-[0.99]">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            Gunakan nama pengguna dan kata sandi yang telah didaftarkan oleh Administrator PST.
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('home') }}" class="text-xs font-bold text-blue-900 hover:text-blue-700 hover:underline">
                ← Kembali ke Beranda Portal PST
            </a>
        </div>
    </div>
</body>
</html>
