<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-200 text-center">
        <div class="w-20 h-20 bg-red-100 text-red-600 rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-6 shadow-inner">
            ⛔
        </div>
        <h1 class="text-4xl font-black text-slate-800 tracking-tight">403</h1>
        <h2 class="text-xl font-bold text-slate-800 mt-2">Akses Ditolak</h2>
        <p class="text-slate-500 text-sm mt-3 leading-relaxed">
            {{ $exception->getMessage() ?: 'Mohon maaf, Anda tidak memiliki hak akses atau wewenang untuk membuka halaman ini.' }}
        </p>

        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col space-y-3">
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-xl transition text-sm shadow">
                        Kembali ke Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('counter.index') }}"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-xl transition text-sm shadow">
                        Kembali ke Meja Kerja Loket
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-xl transition text-sm shadow">
                    Masuk ke Sistem
                </a>
            @endauth
            <a href="{{ route('home') }}"
                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                Kembali ke Beranda Utama
            </a>
        </div>
    </div>
</body>
</html>
