@extends('layouts.admin')

@section('title', 'Pengajuan Izin')

@section('content')
<div class="bg-white/95 p-8 rounded-2xl shadow-xl max-w-7xl mx-auto animate-fade-in-move border border-blue-50">
    <h2 class="text-2xl font-extrabold text-blue-700 mb-7 tracking-tight flex items-center gap-2">
        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect width="16" height="16" x="4" y="4" rx="4" stroke-width="2"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h6m-3-3v6"/>
        </svg>
        Daftar Pengajuan Izin
    </h2>

    <div class="overflow-x-auto rounded-xl border border-blue-100 bg-white shadow-lg">
        <table id="izinTable" class="min-w-full text-base text-gray-800">
            <thead class="bg-blue-50 text-blue-700 uppercase text-left font-semibold">
                <tr>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Tanggal</th>
                    <th class="px-5 py-4">Jenis</th>
                    <th class="px-5 py-4">Alasan</th>
                    <th class="px-5 py-4">Bukti</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($pengajuan_izins as $izin)
            <tr class="border-b border-blue-50 hover:bg-blue-50/70 transition">
                <td class="px-5 py-4 font-semibold text-blue-900">{{ $izin->karyawan->nama_karyawan }}</td>
                <td class="px-5 py-4">{{ $izin->tanggal }}</td>
                <td class="px-5 py-4">{{ $izin->jenis }}</td>
                <td class="px-5 py-4">{{ $izin->alasan }}</td>
                <td class="px-5 py-4">
                    @if ($izin->foto_bukti)
                        <a href="{{ asset('storage/' . $izin->foto_bukti) }}" target="_blank" class="text-blue-700 font-bold hover:underline">Lihat Bukti</a>
                    @else
                        <span class="italic text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $izin->status === 'Disetujui' ? 'bg-green-100 text-green-700' :
                        ($izin->status === 'Ditolak' ? 'bg-red-100 text-red-700' :
                        'bg-yellow-100 text-yellow-700') }}">
                        {{ $izin->status }}
                    </span>
                </td>
                <td class="px-5 py-4 text-center">
                    <form action="{{ route('admin.pengajuan_izin.update', $izin->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()"
                                class="border px-4 py-1 rounded-xl font-semibold bg-white shadow text-blue-700 hover:bg-blue-50 transition text-base">
                            <option value="Menunggu" {{ $izin->status == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="Disetujui" {{ $izin->status == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditolak" {{ $izin->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
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
/* DataTables flex style fix: kiri-kanan selalu rapi */
.dataTables_wrapper .dt-top {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    font-size: 1rem;
    font-weight: 500;
    gap: 0.5rem;
}
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    padding: 0.375rem 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #d1d5db;
    font-size: 1rem;
    background: #f8fafc;
    margin-left: 0.2rem;
}
.dataTables_wrapper .dataTables_length select { text-align: center; min-width: 60px;}
.dataTables_wrapper .dataTables_filter { margin-left: auto !important;}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        $('#izinTable').DataTable({
            pageLength: 10,
            responsive: true,
            dom:
                // FLEX layout: select left, search right, consistent with all other pages
                "<'dt-top'l<'flex-1 flex justify-end'f>>" +
                "rt" +
                "<'flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4'p<'text-sm text-gray-600'i>>",
            language: {
                search: "<span style='font-size:16px;'>🔎</span> Cari:",
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
    });
</script>
@endpush
