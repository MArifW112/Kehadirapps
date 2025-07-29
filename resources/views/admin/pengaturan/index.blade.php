@extends('layouts.admin')

@section('title', 'Pengaturan Admin')

@section('content')
<div class="bg-white/95 p-8 rounded-2xl shadow-xl max-w-3xl mx-auto animate-fade-in-move border border-blue-50">
    <h2 class="text-2xl font-extrabold text-blue-700 mb-7 tracking-tight flex items-center gap-2">
        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
        </svg>
        Pengaturan Sistem
    </h2>

    {{-- Card Ganti Password --}}
    <div class="mb-8 p-6 rounded-xl bg-blue-50 border border-blue-100 shadow-sm">
        <div class="flex items-center gap-4 mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 15v2m0-6v2m6 5V9a6 6 0 10-12 0v8a2 2 0 002 2h8a2 2 0 002-2z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-lg font-bold text-blue-700">Ganti Password Admin</span>
        </div>
        <p class="text-sm text-blue-700 mb-3">Pastikan gunakan password yang aman. Fitur ganti password admin secara langsung dari sini.</p>
        <a href="{{ route('admin.pengaturan.ganti-password') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-bold shadow transition text-sm">Ganti Password</a>
    </div>

    {{-- Card Export Data Karyawan --}}
    <div class="mb-4 p-6 rounded-xl bg-yellow-50 border border-yellow-100 shadow-sm">
        <div class="flex items-center gap-4 mb-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 17v1a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1m3 8l-4-4m0 0l4-4m-4 4h12" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-lg font-bold text-yellow-700">Export Data Karyawan</span>
        </div>
        <p class="text-sm text-yellow-700 mb-3">Download seluruh data karyawan ke format Excel atau CSV.</p>
        <a href="{{ route('admin.pengaturan.export_karyawan') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded-lg font-bold shadow transition text-sm">
            Export Data
        </a>
    </div>

    {{-- Card Export Data Absensi --}}
    <div class="mb-2 p-6 rounded-xl bg-green-50 border border-green-100 shadow-sm">
        <div class="flex items-center gap-4 mb-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5 5 5-5M12 4v12" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-lg font-bold text-green-700">Export Data Absensi</span>
        </div>
        <p class="text-sm text-green-700 mb-3">Download seluruh riwayat absensi ke format Excel atau CSV.</p>
        <a href="{{ route('admin.pengaturan.export_absensi') }}" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-bold shadow transition text-sm">
            Export Data
        </a>
    </div>
</div>
@endsection
