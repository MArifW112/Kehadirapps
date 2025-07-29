@extends('layouts.admin')
@section('title', 'Pengaturan Jadwal Kerja')

@section('content')
<div class="max-w-5xl mx-auto mt-8">
    <div class="bg-white/95 shadow-xl rounded-2xl p-8 border border-blue-50 animate-fade-in-move">
        <h2 class="text-2xl font-extrabold text-blue-700 mb-7 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect width="16" height="16" x="4" y="4" rx="4" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
            </svg>
            Pengaturan Jadwal Kerja
        </h2>
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 shadow">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tambahkan bagian ini untuk menampilkan error validasi global --}}
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 shadow">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table class="w-full text-base text-gray-800 rounded-2xl shadow border border-blue-100 bg-white">
            <thead class="bg-blue-50 text-blue-700 uppercase text-left font-semibold">
                <tr>
                    <th class="px-5 py-4">Hari</th>
                    <th class="px-5 py-4">Jam Masuk</th>
                    <th class="px-5 py-4">Jam Pulang</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwal as $item)
                <tr class="border-b border-blue-50 hover:bg-blue-50/60 transition">
                    <td class="px-5 py-3 font-bold text-blue-900 whitespace-nowrap">{{ $item->hari }}</td>
                    <td class="px-5 py-3">
                        <input
                            type="time"
                            name="jam_masuk"
                            value="{{ $item->jam_masuk }}"
                            class="rounded-xl border-blue-200 px-4 py-2 w-36 font-semibold bg-blue-50 focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                            form="form-{{ $item->id }}"
                            required
                        >
                        {{-- Tambahkan ini untuk error spesifik jam_masuk --}}
                        @error('jam_masuk')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </td>
                    <td class="px-5 py-3">
                        <input
                            type="time"
                            name="jam_pulang"
                            value="{{ $item->jam_pulang }}"
                            class="rounded-xl border-blue-200 px-4 py-2 w-36 font-semibold bg-blue-50 focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                            form="form-{{ $item->id }}"
                            required
                        >
                        {{-- Tambahkan ini untuk error spesifik jam_pulang --}}
                        @error('jam_pulang')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </td>
                    <td class="px-5 py-3">
                        <select
                            name="aktif"
                            class="rounded-xl border-blue-200 px-4 py-2 font-bold shadow-sm bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 min-w-[130px] cursor-pointer transition"
                            form="form-{{ $item->id }}"
                        >
                            <option value="1" @if($item->aktif) selected @endif>Hari Kerja</option>
                            <option value="0" @if(!$item->aktif) selected @endif>Libur</option>
                        </select>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <form
                            id="form-{{ $item->id }}"
                            action="{{ route('admin.jadwal-kerja.update', $item->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-blue-400 text-white rounded-xl hover:scale-105 shadow font-bold text-base transition-all active:scale-95">
                                Simpan
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-7 text-gray-600 text-sm">Atur hari aktif/libur dan jam kerja sesuai kebutuhan. <b>Libur:</b> user tidak bisa absen di hari itu.</p>
    </div>
</div>
@endsection
