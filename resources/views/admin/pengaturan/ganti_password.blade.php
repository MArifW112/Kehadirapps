@extends('layouts.admin')

@section('title', 'Ganti Password Admin')

@section('content')
<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100 animate-fade-in-move">
        <h2 class="text-2xl font-bold mb-7 text-blue-700 flex items-center gap-2">
            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11V17M12 7V7.01M21 12c0-4.97-4.03-9-9-9S3 7.03 3 12s4.03 9 9 9 9-4.03 9-9z" />
            </svg>
            Ganti Password Admin
        </h2>
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg border border-red-200">
                {{ session('error') }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.pengaturan.ganti-password.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-semibold mb-1" for="current_password">Password Lama</label>
                <input type="password" id="current_password" name="current_password"
                       class="w-full rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-200"
                       required autocomplete="current-password">
                @error('current_password')
                    <div class="text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password"
                       class="w-full rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-200"
                       required autocomplete="new-password">
                @error('new_password')
                    <div class="text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="new_password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                       class="w-full rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-200"
                       required autocomplete="new-password">
                @error('new_password_confirmation')
                    <div class="text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit"
                    class="bg-blue-600 text-white px-7 py-2 rounded-lg shadow hover:bg-blue-700 font-bold transition">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
