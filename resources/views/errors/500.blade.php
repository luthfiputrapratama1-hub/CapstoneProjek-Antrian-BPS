@extends('layouts.portal')

@section('title', '500 — Terjadi Kesalahan Sistem | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] shadow-sm">
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center justify-center mx-auto mb-4">
                <x-icon name="warning" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">500</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Terjadi Kesalahan Sistem</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                Sistem mendeteksi kendala pada server internal saat memproses permintaan Anda. Silakan muat ulang halaman atau hubungi administrator PST BPS.
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                <button type="button" onclick="window.location.reload()"
                        class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                    Muat Ulang Halaman
                </button>
                <a href="{{ route('home') }}"
                   class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                    Kembali ke Beranda Utama
                </a>
            </div>
        </div>
    </div>
@endsection
