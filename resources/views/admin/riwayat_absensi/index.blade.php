@extends('layouts.admin')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="bg-white/95 p-8 rounded-2xl shadow-xl max-w-7xl mx-auto animate-fade-in-move border border-blue-50">
    <h2 class="text-2xl font-extrabold text-blue-700 mb-7 tracking-tight flex items-center gap-2">
        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-9 0a3 3 0 100 6 3 3 0 100-6zM3 11a9 9 0 1118 0 9 9 0 01-18 0z"/>
        </svg>
        Riwayat Absensi
    </h2>

    <form method="get" class="mb-5 flex flex-wrap gap-3 items-center">
        <label for="tanggal" class="font-medium text-gray-700">Tanggal:</label>
        <input type="date"
            id="tanggal"
            name="tanggal"
            value="{{ $tanggal }}"
            class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-200"
            max="{{ \Carbon\Carbon::now()->toDateString() }}"
            onchange="this.form.submit()"
        >
        <span class="text-gray-400 ml-2 text-xs">(Otomatis filter)</span>
    </form>

    <div class="overflow-x-auto rounded-xl border border-blue-100 bg-white shadow-lg">
        <table id="absensiTable" class="min-w-full text-base text-gray-800">
            <thead class="bg-blue-50 text-blue-700 uppercase text-left font-semibold">
                <tr>
                    <th class="px-5 py-4">No</th>
                    <th class="px-5 py-4">Nama Karyawan</th>
                    <th class="px-5 py-4">Tanggal</th>
                    <th class="px-5 py-4">Jam Masuk</th>
                    <th class="px-5 py-4">Foto Masuk</th>
                    <th class="px-5 py-4">Jam Pulang</th>
                    <th class="px-5 py-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absensis as $index => $absen)
                <tr class="border-b border-blue-50 hover:bg-blue-50/60 transition">
                    <td class="px-5 py-4">{{ $index + 1 }}</td>
                    <td class="px-5 py-4 font-semibold text-blue-900">{{ $absen['nama_karyawan'] }}</td>
                    <td class="px-5 py-4">{{ \Carbon\Carbon::parse($absen['tanggal'])->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-4">{{ $absen['jam_masuk'] ?? '-' }}</td>
                    <td class="px-5 py-4">
                        @if($absen['foto'])
                            <img src="{{ asset('storage/' . $absen['foto']) }}" alt="Foto Absen"
                                class="rounded-lg shadow border bg-blue-50 cursor-pointer hover:scale-110 hover:ring-2 hover:ring-blue-300 transition"
                                width="60" height="60" style="object-fit:cover"
                                onclick="openFotoModal('{{ asset('storage/' . $absen['foto']) }}')">
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">{{ $absen['jam_pulang'] ?? '-' }}</td>
                    <td class="px-5 py-4">
                        @php
                            $status = $absen['status'];
                            $color = match($status) {
                                'Hadir' => 'bg-green-100 text-green-700',
                                'Telat' => 'bg-orange-100 text-orange-700',
                                'Alpha' => 'bg-red-100 text-red-700',
                                'Izin'  => 'bg-yellow-100 text-yellow-700',
                                'Belum Absen' => 'bg-gray-100 text-gray-600',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $color }}">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Foto Absen -->
<div id="modalFoto" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
    <div class="relative bg-white p-4 rounded-xl shadow-xl max-w-xl w-full">
        <button onclick="closeFotoModal()" class="absolute top-2 right-3 text-2xl text-gray-600 hover:text-black">&times;</button>
        <img id="modalImg" src="" alt="Preview Foto" class="max-w-full max-h-[70vh] mx-auto rounded-lg">
    </div>
</div>
@endsection

@push('styles')
<style>
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
    // Paksa input tanggal tidak bisa lebih dari hari ini (antisipasi inspect element)
    document.addEventListener("DOMContentLoaded", function() {
        var inputTanggal = document.getElementById('tanggal');
        if (inputTanggal) {
            var today = new Date().toISOString().split('T')[0];
            inputTanggal.setAttribute('max', today);
            inputTanggal.addEventListener('change', function(){
                if (this.value > today) {
                    alert("Tidak bisa filter ke tanggal masa depan!");
                    this.value = today;
                    this.form.submit();
                }
            });
        }
    });

    $(document).ready(function () {
        $('#absensiTable').DataTable({
            pageLength: 10,
            responsive: true,
            dom:
                "<'dt-top'l<'flex-1 flex justify-end' f>>" +
                "rt" +
                "<'flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4'p<'text-sm text-gray-600'i>>",
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                },
                zeroRecords: "Tidak ada data yang cocok",
                infoEmpty: "Tidak ada data",
            }
        });
    });

    // Modal foto
    function openFotoModal(src) {
        document.getElementById('modalImg').src = src;
        document.getElementById('modalFoto').classList.remove('hidden');
    }
    function closeFotoModal() {
        document.getElementById('modalFoto').classList.add('hidden');
        document.getElementById('modalImg').src = '';
    }
</script>
@endpush
