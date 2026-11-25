@extends('layouts.portal')

@section('title', 'Ubah Kata Sandi — PST BPS Kota Tasikmalaya')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-10 flex-1 flex flex-col justify-center')

@section('header-actions')
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-semibold text-white">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>Kembali ke Dashboard</span>
        </a>
    @else
        <a href="{{ route('counter.index') }}"
           class="inline-flex items-center gap-1.5 rounded-md border border-white/20 bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-semibold text-white">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>Kembali ke Meja Loket</span>
        </a>
    @endif
@endsection

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-[#EDF1F6]">
                <span class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 text-[#0B4F9C] flex items-center justify-center shrink-0">
                    <x-icon name="lock" class="w-5 h-5" />
                </span>
                <div>
                    <h1 class="text-lg font-bold text-[#0F172A]">Ubah Kata Sandi</h1>
                    <p class="text-xs text-[#475569]">Akun aktif: <strong class="font-mono text-[#0F172A]">{{ Auth::user()->username }}</strong></p>
                </div>
            </div>

            <x-flash-alerts class="mt-5" />

            <form action="{{ route('password.update') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi Saat Ini <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           autocomplete="current-password"
                           required
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi Baru <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           autocomplete="new-password"
                           minlength="6"
                           required
                           aria-describedby="new-password-help"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                    <p id="new-password-help" class="mt-1 text-[11px] text-[#64748B]">Minimal 6 karakter.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Konfirmasi Kata Sandi Baru <span class="text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           autocomplete="new-password"
                           minlength="6"
                           required
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A]">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white transition-colors">
                        Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
