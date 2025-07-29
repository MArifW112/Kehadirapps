@extends('layouts.admin')

@section('title', 'Tambah Karyawan')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow max-w-3xl mx-auto">
        <form action="{{ route('admin.karyawan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Jabatan -->
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('jabatan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- No HP -->
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('no_hp') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('alamat') }}</textarea>
                    @error('alamat') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Foto -->
                <div class="md:col-span-2">
                    <label for="foto" class="block text-sm font-medium text-gray-700">Foto Wajah (boleh lebih dari 1)</label>
                    <input type="file" name="foto[]" id="foto" multiple accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-700 file:bg-blue-50 file:border file:border-gray-300 file:rounded-md file:mr-4 file:py-2 file:px-4">
                    @error('foto.*') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <!-- Preview -->
                    <div id="preview" class="flex flex-wrap mt-4 gap-4"></div>
                </div>
            </div>

            <div class="mt-6 text-right">
                <a href="{{ route('admin.karyawan.index') }}" class="text-gray-600 hover:underline mr-4">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Simpan</button>
            </div>
            @push('scripts')
                <script>
                    const input = document.getElementById('foto');
                    const preview = document.getElementById('preview');

                    input.addEventListener('change', function () {
                        preview.innerHTML = ''; // Clear old preview
                        Array.from(this.files).forEach(file => {
                            if (!file.type.startsWith('image/')) return;

                            const reader = new FileReader();
                            reader.onload = function (e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.classList.add('w-24', 'h-24', 'rounded-lg', 'object-cover', 'shadow');
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        });
                    });
                </script>
                @endpush
        </form>
    </div>
@endsection
