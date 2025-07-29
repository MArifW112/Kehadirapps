@extends('layouts.admin')

@section('title', 'Detail Karyawan')

@section('content')
<div class="max-w-4xl mx-auto bg-white/90 p-8 rounded-2xl shadow-2xl animate-fade-in-move border border-blue-50">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h2 class="text-3xl font-extrabold text-blue-700 tracking-tight flex items-center gap-2">
            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75M12 12a4 4 0 014 4H8a4 4 0 014-4z"/>
            </svg>
            Detail Karyawan
        </h2>
        <a href="{{ route('admin.karyawan.index') }}"
           class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 font-bold px-4 py-2 rounded-lg shadow hover:scale-105 hover:bg-blue-200 transition-all text-sm active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="grid md:grid-cols-2 gap-7 text-[1.09rem] text-blue-950 font-[500]">
        <div>
            <p class="font-semibold text-blue-600 mb-1">Nama Lengkap</p>
            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-100 font-bold text-blue-800 shadow-sm">{{ $karyawan->nama_karyawan }}</div>
        </div>
        <div>
            <p class="font-semibold text-blue-600 mb-1">Email</p>
            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-100 select-all">{{ $karyawan->email }}</div>
        </div>
        <div>
            <p class="font-semibold text-blue-600 mb-1">Jabatan</p>
            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-100">{{ $karyawan->jabatan }}</div>
        </div>
        <div>
            <p class="font-semibold text-blue-600 mb-1">Nomor HP</p>
            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-100">{{ $karyawan->no_hp ?? '-' }}</div>
        </div>
        <div class="md:col-span-2">
            <p class="font-semibold text-blue-600 mb-1">Alamat</p>
            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-100">{{ $karyawan->alamat ?? '-' }}</div>
        </div>

        <div class="md:col-span-2">
            <p class="font-semibold text-blue-600 mb-2">Foto</p>
            @if ($karyawan->fotos->isNotEmpty())
                <div class="flex flex-wrap gap-4 mt-2">
                    @foreach ($karyawan->fotos as $foto)
                        <img src="{{ asset('storage/' . $foto->path) }}"
                             alt="Foto Karyawan"
                             class="w-20 h-20 object-cover rounded-xl border border-blue-200 shadow cursor-pointer hover:ring-4 hover:ring-blue-200 hover:scale-110 transition-all duration-200"
                             onclick="openModal('{{ asset('storage/' . $foto->path) }}')">
                    @endforeach
                </div>
            @else
                <div class="italic text-gray-500 mt-2">Belum ada foto</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div id="photoModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
    <div class="relative bg-white p-4 rounded-2xl shadow-2xl max-w-3xl w-full animate-fade-in-move">
        <button onclick="closeModal()"
                class="absolute top-3 right-4 text-2xl text-blue-400 hover:text-blue-700 hover:scale-125 transition-all duration-150 font-bold bg-blue-50 rounded-full w-9 h-9 flex items-center justify-center shadow">
            &times;
        </button>
        <img id="modalImage" src="" alt="Preview"
             class="max-w-full max-h-[75vh] mx-auto rounded-lg shadow-xl border-2 border-blue-100 transition-all duration-300">
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modal fade-in animation */
#photoModal.animate-fade-in-move {
    animation: fadeInMove 0.33s cubic-bezier(.42,0,.58,1.0) both;
}
@keyframes fadeInMove {
    0% { opacity: 0; transform: translateY(-24px) scale(0.97);}
    100% { opacity: 1; transform: translateY(0) scale(1);}
}
</style>
@endpush

@push('scripts')
<script>
    function openModal(src) {
        document.getElementById('modalImage').src = src;
        const modal = document.getElementById('photoModal');
        modal.classList.remove('hidden');
        modal.classList.add('animate-fade-in-move');
        setTimeout(() => modal.classList.remove('animate-fade-in-move'), 400);
    }

    function closeModal() {
        const modal = document.getElementById('photoModal');
        modal.classList.add('hidden');
        document.getElementById('modalImage').src = '';
    }

    // Tutup modal jika klik di luar gambar/modal
    document.getElementById('photoModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    // Escape key untuk tutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape") closeModal();
    });
</script>
@endpush
