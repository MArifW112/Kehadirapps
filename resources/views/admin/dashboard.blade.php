@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<!-- Quick Action Buttons -->
<div class="mb-10 animate-fade-in-move">
    <h2 class="text-lg font-extrabold text-blue-700 mb-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Aksi Cepat
    </h2>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('admin.karyawan.create') }}"
           class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-400 text-white rounded-xl hover:scale-105 hover:shadow-lg transition-all text-base font-semibold shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
        <a href="{{ route('admin.pengajuan_izin.index') }}"
           class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white rounded-xl hover:scale-105 hover:shadow-lg transition-all text-base font-semibold shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            Lihat Semua Izin
        </a>
        <a href="{{ route('admin.riwayat.absensi.index') }}"
           class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-green-400 text-white rounded-xl hover:scale-105 hover:shadow-lg transition-all text-base font-semibold shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17v-1a4 4 0 014-4h0a4 4 0 014 4v1M5 21h14M9 7a4 4 0 116 0"/></svg>
            Riwayat Absensi
        </a>
    </div>
</div>

<!-- Row: Rekap Status Hari Ini dan Pengajuan Izin Terbaru -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 mb-8 animate-fade-in-move">
    <!-- Rekap Status Hari Ini -->
    <div class="bg-white/90 p-7 rounded-2xl shadow-lg hover:shadow-2xl transition-all">
        <h3 class="text-lg font-extrabold text-blue-700 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
            Rekap Status Hari Ini
        </h3>
        <ul class="space-y-3 text-base text-gray-700">
            <li class="flex justify-between items-center">
                <span>✅ Masuk</span><span class="font-bold">{{ $absensiHariIni ?? 0 }} orang</span>
            </li>
            <li class="flex justify-between items-center">
                <span>📝 Izin Cuti</span><span class="font-bold">{{ $izinCuti ?? 0 }} orang</span>
            </li>
            <li class="flex justify-between items-center">
                <span>📄 Izin Lainnya</span><span class="font-bold">{{ $izinLain ?? 0 }} orang</span>
            </li>
            <li class="flex justify-between items-center">
                <span>❌ Tanpa Keterangan</span><span class="font-bold">{{ $tidakHadir ?? 0 }} orang</span>
            </li>
        </ul>
    </div>
    <!-- Pengajuan Izin Terbaru -->
    <div class="bg-white/90 p-7 rounded-2xl shadow-lg hover:shadow-2xl transition-all">
        <h3 class="text-lg font-extrabold text-blue-700 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect width="16" height="16" x="4" y="4" rx="4" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h6m-3-3v6"/>
            </svg>
            Pengajuan Izin Terbaru
        </h3>
        <ul class="divide-y divide-blue-50">
            @forelse ($pengajuanIzinTerbaru as $izin)
                <li class="py-3 flex items-center justify-between gap-2">
                    <div>
                        <div class="font-semibold text-blue-800">{{ $izin->karyawan->nama_karyawan ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $izin->tanggal }} • {{ $izin->jenis }} <br> <span class="italic text-gray-400">{{ $izin->alasan }}</span></div>
                    </div>
                    <div class="flex items-center gap-1">
                        <form action="{{ route('admin.pengajuan_izin.update', $izin->id) }}" method="POST" class="flex gap-2 items-center">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="rounded-md border text-xs py-1 px-2 font-semibold text-blue-700 bg-white shadow hover:bg-blue-50 transition">
                                <option value="Menunggu" {{ $izin->status == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="Disetujui" {{ $izin->status == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="Ditolak" {{ $izin->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            @if ($izin->foto_bukti)
                                <a href="{{ asset('storage/' . $izin->foto_bukti) }}" target="_blank" class="ml-1 underline text-blue-600 text-xs">Bukti</a>
                            @endif
                        </form>
                    </div>
                </li>
            @empty
                <li class="py-4 text-base text-gray-400 text-center">Belum ada pengajuan izin terbaru.</li>
            @endforelse
        </ul>
    </div>
</div>

<!-- Log Absensi Terbaru -->
<div class="mt-12 animate-fade-in-move">
    <h2 class="text-lg font-extrabold text-blue-700 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
        Log Absensi Terbaru
    </h2>
    <div class="bg-white rounded-2xl shadow-lg overflow-x-auto hover:shadow-2xl transition-all">
        <table class="min-w-full divide-y divide-blue-100">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-7 py-4 text-left text-xs font-bold text-blue-600 uppercase">Nama</th>
                    <th class="px-7 py-4 text-left text-xs font-bold text-blue-600 uppercase">Tanggal</th>
                    <th class="px-7 py-4 text-left text-xs font-bold text-blue-600 uppercase">Masuk</th>
                    <th class="px-7 py-4 text-left text-xs font-bold text-blue-600 uppercase">Keluar</th>
                    <th class="px-7 py-4 text-left text-xs font-bold text-blue-600 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-blue-50">
                @forelse ($logAbsensi ?? [] as $log)
                <tr class="hover:bg-blue-50 transition-all">
                    <td class="px-7 py-4 whitespace-nowrap text-base text-gray-700">{{ $log->karyawan->nama_karyawan ?? '-' }}</td>
                    <td class="px-7 py-4 text-base text-gray-500">{{ $log->tanggal ?? '-' }}</td>
                    <td class="px-7 py-4 text-base text-gray-500">{{ $log->jam_masuk ?? '-' }}</td>
                    <td class="px-7 py-4 text-base text-gray-500">{{ $log->jam_pulang ?? '-' }}</td>
                    <td class="px-7 py-4 text-base">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                            {{ strtolower($log->status) == 'hadir' ? 'bg-green-100 text-green-700' :
                               (strtolower($log->status) == 'izin' ? 'bg-yellow-100 text-yellow-700' :
                               'bg-red-100 text-red-700') }} shadow-sm">
                            {{ ucfirst($log->status ?? '-') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-base text-gray-400">Belum ada data absensi terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
