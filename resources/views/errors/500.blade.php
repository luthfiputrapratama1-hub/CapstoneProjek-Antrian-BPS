<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kendala Server | PST BPS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-200 text-center">
        <div class="w-20 h-20 bg-rose-100 text-rose-700 rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-6 shadow-inner">
            ⚠️
        </div>
        <h1 class="text-5xl font-black text-slate-900 tracking-tight">500</h1>
        <h2 class="text-xl font-bold text-slate-800 mt-2">Terjadi Kesalahan Sistem</h2>
        <p class="text-slate-500 text-sm mt-3 leading-relaxed">
            Sistem mendeteksi kendala pada server internal saat memproses permintaan Anda. Silakan muat ulang halaman atau hubungi administrator PST BPS.
        </p>

        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col space-y-3">
            <button onclick="window.location.reload()"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-xl transition text-sm shadow">
                Muat Ulang Halaman
            </button>
            <a href="{{ route('home') }}"
                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                Kembali ke Beranda Utama
            </a>
        </div>
    </div>
</body>
</html>
