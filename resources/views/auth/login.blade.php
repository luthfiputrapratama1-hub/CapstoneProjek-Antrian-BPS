@extends('layouts.portal')

@section('title', 'Masuk Sistem — PST BPS Kota Tasikmalaya')
@section('hide-header', '1')
@section('main-class', 'max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center')

@section('content')
    <div class="bg-white rounded-xl border border-[#DCE3EC] overflow-hidden shadow-sm">
        <div class="h-1 w-full flex" aria-hidden="true">
            <span class="flex-1 bg-[#0B4F9C]"></span>
            <span class="flex-1 bg-[#15803D]"></span>
            <span class="flex-1 bg-[#EA580C]"></span>
        </div>

        <div class="p-6 sm:p-8">
            <div class="mb-6">
                <x-bps-brand
                    title="Pelayanan Statistik Terpadu"
                    subtitle="BPS Kota Tasikmalaya"
                    :href="route('home')"
                />
                <h1 class="mt-6 text-xl font-bold text-[#0F172A]">Masuk ke Sistem Operasional</h1>
                <p class="mt-1 text-xs text-[#475569]">Gunakan akun petugas loket atau administrator yang terdaftar.</p>
            </div>

            @if($errors->any())
                <div role="alert" class="mb-5 flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-xs text-red-800">
                    <x-icon name="warning" class="w-4 h-4 shrink-0 mt-0.5 text-red-600" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Nama Pengguna (Username)
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           autocomplete="username"
                           autocorrect="off"
                           autocapitalize="none"
                           spellcheck="false"
                           required
                           autofocus
                           placeholder="Contoh: petugas1 atau admin"
                           class="w-full rounded-md border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-sm text-[#0F172A] placeholder:text-[#64748B]">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#0F172A] mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               autocomplete="current-password"
                               required
                               placeholder="Masukkan kata sandi"
                               class="w-full rounded-md border border-[#CBD5E1] bg-white pl-3.5 pr-11 py-2.5 text-sm text-[#0F172A] placeholder:text-[#64748B]">
                        <button type="button"
                                data-password-toggle="password"
                                aria-pressed="false"
                                aria-label="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-[#64748B] hover:text-[#0F172A]">
                            <span data-eye-show><x-icon name="eye" class="w-4 h-4" /></span>
                            <span data-eye-hide class="hidden"><x-icon name="eye-off" class="w-4 h-4" /></span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-[#0B4F9C] hover:bg-[#083D7A] px-4 py-2.5 text-sm font-semibold text-white transition-colors">
                    Masuk ke Sistem
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-[#EDF1F6] flex items-center justify-between text-xs">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-semibold text-[#0B4F9C] hover:underline">
                    <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                    <span>Kembali ke Beranda</span>
                </a>
                <span class="text-[#64748B]">Akses Terotentikasi</span>
            </div>
        </div>
    </div>
@endsection
