@extends('layouts.portal')

@section('title', '404 — Halaman Tidak Ditemukan | PST BPS')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full bg-[#0B4F9C]" aria-hidden="true"></div>
        <div class="p-6 sm:p-8 text-center">
            <div class="w-12 h-12 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center mx-auto mb-4">
                <x-icon name="search" class="w-6 h-6" />
            </div>
            <p class="font-mono text-4xl font-bold text-[#0F172A] tabular-nums-strict">404</p>
            <h1 class="text-lg font-bold text-[#0F172A] mt-2">Halaman Tidak Ditemukan</h1>
            <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                Mohon maaf, tautan atau halaman yang Anda tuju tidak ditemukan atau telah dipindahkan ke alamat lain.
            </p>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex flex-col gap-2.5">
                <a href="{{ route('home') }}"
                   class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] py-2.5 text-xs font-semibold text-white">
                    Kembali ke Beranda Utama
                </a>
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                            Buka Dashboard Admin
                        </a>
                    @else
                        <a href="{{ route('counter.index') }}"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white hover:bg-[#F4F6F9] py-2.5 text-xs font-semibold text-[#0F172A]">
                            Buka Meja Kerja Loket
                        </a>
                    @endif
                @endauth
                <a href="{{ route('kiosk.index') }}"
                   class="w-full rounded-md border border-[#DCE3EC] bg-[#F4F6F9] hover:bg-[#EDF1F6] py-2.5 text-xs font-semibold text-[#334155]">
                    Anjungan Kiosk Tiket
                </a>
            </div>
        </div>
    </div>
@endsection
