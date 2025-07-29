@extends('layouts.admin')

@section('title', 'Set Lokasi Kantor')

@section('content')
<div class="max-w-3xl mx-auto mt-10 animate-fade-in-move">
    <div class="bg-white/95 rounded-2xl shadow-xl border border-blue-50 p-8">
        <div class="flex items-center gap-3 mb-7">
            <span class="inline-flex items-center justify-center rounded-full bg-blue-100 p-2">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21C7.03 21 2.73 17.17 1.45 12.01A2.4 2.4 0 014.13 9.4l1.2-.26A8.38 8.38 0 0112 3.08a8.38 8.38 0 016.67 6.06l1.2.26a2.4 2.4 0 012.68 2.6C21.27 17.17 16.97 21 12 21z"/>
                    <circle cx="12" cy="12" r="3.5" />
                </svg>
            </span>
            <h2 class="text-2xl font-extrabold text-blue-700 tracking-tight">Pengaturan Lokasi Kantor</h2>
        </div>

        @if (session('success'))
            <div class="mb-5 p-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-base font-bold shadow flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.lokasi-kantor.update') }}" method="POST" class="space-y-6" id="formLokasiKantor">
            @csrf

            <div>
                <label class="block text-base font-semibold text-blue-700 mb-1">Nama Lokasi/Kantor</label>
                <input type="text" name="nama_kantor" value="{{ old('nama_kantor', $lokasi->nama_kantor ?? '') }}"
                    class="w-full rounded-xl border-blue-200 px-4 py-2.5 text-base focus:ring-2 focus:ring-blue-100 focus:border-blue-400 shadow-sm"
                    required autocomplete="off" placeholder="Contoh: Politeknik Negeri Indramayu">
                @error('nama_kantor')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-base font-semibold text-blue-700 mb-1">Cari Nama Tempat (opsional)</label>
                <input id="cariTempat" type="text"
                    class="w-full rounded-xl border-blue-200 px-4 py-2.5 text-base focus:ring-2 focus:ring-blue-100 focus:border-blue-400 mb-2 shadow-sm"
                    placeholder="Cari tempat, misal: Politeknik Negeri Indramayu">
                <div id="map" class="rounded-xl border border-blue-100 shadow-sm" style="width:100%;height:320px"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-base mb-1 font-semibold text-blue-700">Latitude</label>
                    <input id="latInput" type="text" name="latitude"
                        value="{{ old('latitude', $lokasi->latitude ?? '') }}"
                        class="w-full rounded-xl border-blue-200 px-3 py-2 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 shadow-sm text-base" required>
                </div>
                <div>
                    <label class="block text-base mb-1 font-semibold text-blue-700">Longitude</label>
                    <input id="lngInput" type="text" name="longitude"
                        value="{{ old('longitude', $lokasi->longitude ?? '') }}"
                        class="w-full rounded-xl border-blue-200 px-3 py-2 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 shadow-sm text-base" required>
                </div>
                <div>
                    <label class="block text-base mb-1 font-semibold text-blue-700">Radius Area (meter)</label>
                    <input type="number" min="10" max="1000" name="radius_meter"
                        value="{{ old('radius_meter', $lokasi->radius_meter ?? 100) }}"
                        class="w-full rounded-xl border-blue-200 px-3 py-2 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 shadow-sm text-base" required>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-8 py-2.5 rounded-xl shadow-lg font-bold text-base hover:scale-105 hover:bg-blue-700 transition-all active:scale-95 focus:ring-2 focus:ring-blue-300">
                    Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Nominatim for geocoding -->
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Default center (Indramayu)
    var lat = {{ $lokasi->latitude ?? -6.326273 }};
    var lng = {{ $lokasi->longitude ?? 108.319036 }};
    var radius = {{ $lokasi->radius_meter ?? 100 }};
    var map = L.map('map').setView([lat, lng], 16);

    // Add OpenStreetMap tile
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Add draggable marker
    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    // Circle (radius kantor)
    var circle = L.circle([lat, lng], {radius: radius, color: '#2563eb', fillOpacity: 0.16}).addTo(map);

    // Update input fields on marker drag
    marker.on('drag', function(e) {
        var position = e.target.getLatLng();
        document.getElementById('latInput').value = position.lat.toFixed(7);
        document.getElementById('lngInput').value = position.lng.toFixed(7);
        circle.setLatLng(position);
    });

    // Update circle on radius change
    document.querySelector('[name="radius_meter"]').addEventListener('input', function() {
        circle.setRadius(this.value);
    });

    // Update marker if input changed manual
    document.getElementById('latInput').addEventListener('input', function() {
        var lat = parseFloat(this.value);
        var lng = parseFloat(document.getElementById('lngInput').value);
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        map.panTo([lat, lng]);
    });
    document.getElementById('lngInput').addEventListener('input', function() {
        var lng = parseFloat(this.value);
        var lat = parseFloat(document.getElementById('latInput').value);
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        map.panTo([lat, lng]);
    });

    // Simple geocoder/cari tempat
    document.getElementById('cariTempat').addEventListener('change', function() {
        var query = this.value;
        if(!query) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if(data.length > 0) {
                    var d = data[0];
                    var lat = parseFloat(d.lat), lng = parseFloat(d.lon);
                    marker.setLatLng([lat, lng]);
                    circle.setLatLng([lat, lng]);
                    map.setView([lat, lng], 17);
                    document.getElementById('latInput').value = lat.toFixed(7);
                    document.getElementById('lngInput').value = lng.toFixed(7);
                } else {
                    alert('Lokasi tidak ditemukan!');
                }
            });
    });
});
</script>
@endpush
