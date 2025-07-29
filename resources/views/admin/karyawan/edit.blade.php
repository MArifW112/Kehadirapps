@extends('layouts.admin')

@section('title', 'Edit Karyawan')

@section('content')
<div class="bg-white/95 p-8 rounded-2xl shadow-xl max-w-3xl mx-auto animate-fade-in-move border border-blue-50">
    <form action="{{ route('admin.karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
            <!-- Nama -->
            <div>
                <label for="nama_karyawan" class="block text-base font-semibold text-blue-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_karyawan" id="nama_karyawan"
                    value="{{ old('nama_karyawan', $karyawan->nama_karyawan) }}"
                    class="mt-1 block w-full border-blue-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-base py-2 px-3">
                @error('nama_karyawan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-base font-semibold text-blue-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $karyawan->email) }}"
                    class="mt-1 block w-full border-blue-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-base py-2 px-3">
                @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Jabatan -->
            <div>
                <label for="jabatan" class="block text-base font-semibold text-blue-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}"
                    class="mt-1 block w-full border-blue-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-base py-2 px-3">
                @error('jabatan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- No HP -->
            <div>
                <label for="no_hp" class="block text-base font-semibold text-blue-700 mb-1">Nomor HP</label>
                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}"
                    class="mt-1 block w-full border-blue-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-base py-2 px-3">
                @error('no_hp') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Alamat -->
            <div class="md:col-span-2">
                <label for="alamat" class="block text-base font-semibold text-blue-700 mb-1">Alamat</label>
                <textarea name="alamat" id="alamat" rows="3"
                    class="mt-1 block w-full border-blue-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-base py-2 px-3">{{ old('alamat', $karyawan->alamat) }}</textarea>
                @error('alamat') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Upload Foto Baru -->
            <div class="md:col-span-2">
                <label for="foto" class="block text-base font-semibold text-blue-700 mb-1">
                    Tambah Foto Baru <span class="font-normal text-gray-500">(boleh lebih dari 1)</span>
                </label>
                <input type="file" name="foto[]" id="foto" multiple accept="image/*"
                    class="mt-1 block w-full text-sm text-blue-700 file:bg-blue-50 file:border file:border-blue-300 file:rounded-md file:mr-4 file:py-2 file:px-4 file:font-semibold">
                @error('foto.*') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <!-- Preview Foto Lama -->
                @if ($karyawan->fotos->count())
                <div class="mt-6">
                    <div class="font-semibold text-blue-600 mb-2">Foto Lama</div>
                    <div id="listFotoLama" class="flex flex-wrap gap-4">
                        @foreach ($karyawan->fotos as $foto)
                        <div class="relative w-24 h-24 rounded-xl shadow border bg-blue-50 overflow-hidden group transition-all duration-150">
                            <img src="{{ asset('storage/' . $foto->path) }}" alt="Foto Karyawan"
                                class="w-full h-full object-contain transition-all duration-200">
                            <button type="button"
                                onclick="deleteFoto('{{ route('admin.karyawan.foto.destroy', $foto->id) }}')"
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow hover:bg-red-700 hover:scale-110 transition opacity-0 group-hover:opacity-100"
                                title="Hapus">&times;</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Preview Foto Baru -->
                <div id="preview" class="flex flex-wrap gap-4 mt-5"></div>
            </div>
        </div>

        <div class="mt-7 text-right flex flex-wrap gap-2 justify-end">
            <a href="{{ route('admin.karyawan.index') }}"
                class="inline-block bg-gray-100 text-gray-700 px-5 py-2 rounded-xl shadow hover:bg-gray-200 transition font-bold mr-1">Batal</a>
            <button type="submit"
                class="inline-block bg-gradient-to-r from-blue-600 to-blue-400 text-white px-7 py-2 rounded-xl font-bold shadow-lg hover:scale-105 hover:bg-blue-700 transition-all active:scale-95">
                Simpan Perubahan
            </button>
        </div>
    </form>

    <!-- Form delete foto global -->
    <form id="deleteFotoForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('styles')
<style>
#listFotoLama .group:hover {
    box-shadow: 0 6px 24px #2563eb33;
    background: linear-gradient(135deg, #dbeafe 80%, #fef9c3 100%);
    transform: scale(1.07) rotate(-2deg);
}
#listFotoLama button {
    opacity: 0;
    transition: opacity .15s, transform .16s;
}
#listFotoLama .group:hover button {
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
    // Hapus foto lama
    function deleteFoto(actionUrl) {
        if (confirm('Hapus foto ini?')) {
            const form = document.getElementById('deleteFotoForm');
            form.action = actionUrl;
            form.submit();
        }
    }

    // Preview foto baru
    const input = document.getElementById('foto');
    const preview = document.getElementById('preview');
    if (input) {
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    const container = document.createElement('div');
                    container.className = 'relative w-24 h-24 rounded-xl shadow border bg-blue-50 overflow-hidden';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-full object-contain';
                    container.appendChild(img);
                    preview.appendChild(container);
                };
                reader.readAsDataURL(file);
            });
        });
    }
</script>
@endpush
