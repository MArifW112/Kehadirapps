@extends('layouts.admin')

@section('title', 'Data Karyawan')

@section('content')
<div class="bg-white/90 p-7 rounded-2xl shadow-xl max-w-7xl mx-auto animate-fade-in-move">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4">
        <h2 class="text-2xl font-extrabold text-blue-700 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75M12 12a4 4 0 014 4H8a4 4 0 014-4z"/></svg>
            Daftar Karyawan
        </h2>
        <a href="{{ route('admin.karyawan.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-2.5 rounded-xl shadow-lg font-bold hover:scale-105 hover:shadow-2xl transition-all text-base active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
    </div>
    <div class="mb-6">
        <label for="jabatanFilter" class="block text-sm font-semibold text-gray-700 mb-1">Filter Berdasarkan Jabatan</label>
        <select id="jabatanFilter" class="w-full md:w-1/3 border border-blue-200 rounded-xl px-4 py-2 text-base bg-blue-50 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
            <option value="">Semua Jabatan</option>
            @foreach ($karyawans->pluck('jabatan')->unique() as $jabatan)
                <option value="{{ $jabatan }}">{{ $jabatan }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-lg">
        <table id="karyawanTable" class="min-w-full text-base text-gray-800">
            <thead class="bg-blue-50 text-blue-700 uppercase text-left font-semibold">
                <tr>
                    <th class="px-5 py-4">No</th>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Email</th>
                    <th class="px-5 py-4">Jabatan</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($karyawans as $index => $karyawan)
                    <tr class="border-b border-blue-50 hover:bg-blue-50/70 transition-all">
                        <td class="px-5 py-4">{{ $index + 1 }}</td>
                        <td class="px-5 py-4 font-semibold text-blue-900">{{ $karyawan->nama_karyawan }}</td>
                        <td class="px-5 py-4">{{ $karyawan->email }}</td>
                        <td class="px-5 py-4">{{ $karyawan->jabatan }}</td>
                        <td class="px-5 py-4 text-center space-x-2">
                            <a href="{{ route('admin.karyawan.show', $karyawan->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs shadow-sm hover:bg-indigo-100 hover:scale-105 transition-all"
                               title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12h.01M12 12h.01M9 12h.01M12 20c4.418 0 8-4.03 8-9s-3.582-9-8-9-8 4.03-8 9 3.582 9 8 9z"/></svg>
                                Detail
                            </a>
                            <a href="{{ route('admin.karyawan.edit', $karyawan->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs shadow-sm hover:bg-blue-100 hover:scale-105 transition-all"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13.5V19h5.5l8.036-8.036a2.5 2.5 0 00-3.536-3.536z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('admin.karyawan.destroy', $karyawan->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Yakin ingin menghapus?')"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-red-50 text-red-600 font-bold text-xs shadow-sm hover:bg-red-100 hover:scale-105 transition-all"
                                        title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                         viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    gap: 0.7rem;
    margin-bottom: 1rem;
}
.dataTables_wrapper .dataTables_length label {
    display: flex !important;
    align-items: center !important;
    gap: 0.6rem;
    font-size: 1.08rem;
    font-weight: 500;
    margin-bottom: 0 !important;
    margin-top: 0 !important;
    line-height: 1.8;
}
.dataTables_wrapper .dataTables_length select {
    padding: 0.34rem 0.85rem;
    border-radius: 0.7rem;
    border: 1px solid #bcd2f6;
    font-size: 1.08rem;
    font-weight: 600;
    text-align: center;
    background: #f1f5fa;
    outline: none;
    transition: border 0.18s;
    box-shadow: 0 1px 2px #2563eb07;
    margin-bottom: 0 !important;
    margin-top: 0 !important;
    height: 2.35rem;
    min-width: 3.8rem;
    /* align baseline */
    vertical-align: middle;
}

.dataTables_wrapper .dataTables_filter label {
    display: flex !important;
    align-items: center !important;
    gap: 0.6rem;
    font-size: 1.08rem;
    margin-bottom: 0 !important;
    margin-top: 0 !important;
    line-height: 1.8;
}
.dataTables_wrapper .dataTables_filter input {
    padding: 0.42rem 1rem;
    border-radius: 0.8rem;
    border: 1px solid #c2d5f7;
    font-size: 1.08rem;
    margin-left: 0.1rem;
    background: #f8fafc;
    transition: border 0.18s;
    outline: none;
    box-shadow: 0 1px 2px #60a5fa11;
}

.dataTables_wrapper select:focus,
.dataTables_wrapper input:focus {
    border: 1.5px solid #2563eb;
    box-shadow: 0 2px 8px #60a5fa22;
}

@media (max-width: 600px) {
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        font-size: 0.97rem;
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.3rem;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        gap: 0.3rem;
    }
}
.dataTables_wrapper .dataTables_filter input {
    max-width: 320px;
    width: 100%;
    box-sizing: border-box;
}
@media (max-width: 600px) {
    .dataTables_wrapper .dataTables_filter input {
        max-width: 100%;
    }
}

/* Agar info & pagination tetap dalam box tabel */
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    padding-left: 1.4rem;
    padding-right: 1.4rem;
    word-break: break-word;
    box-sizing: border-box;
}
@media (max-width: 600px) {
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding-left: .5rem;
        padding-right: .5rem;
        font-size: 0.95rem;
    }
}
/* Jika masih keluar, wrap .dataTables_wrapper */
.dataTables_wrapper {
    width: 100%;
    overflow-x: auto;
    border-radius: 1.2rem;
}

</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#karyawanTable').DataTable({
        pageLength: 10,
        responsive: true,
        dom:
          "<'flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4'<'flex items-center gap-4'l><'flex-1 flex justify-end'f>>" +
          "rt" +
          "<'flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4'p<'text-sm text-gray-600'i>>",
        language: {
            search: `<span style='font-size:16px;'>🔎</span> Cari:`,
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "›",
                previous: "‹"
            },
            zeroRecords: "Tidak ada data ditemukan",
            infoEmpty: "Tidak ada data",
        }
    });

    // Inisialisasi filter jabatan
    $('#jabatanFilter').on('change', function() {
        var jabatan = $(this).val();
        table.column(3).search(jabatan).draw(); // Kolom 'Jabatan' adalah indeks ke-3 (0-indexed)
    });
});
</script>
@endpush
