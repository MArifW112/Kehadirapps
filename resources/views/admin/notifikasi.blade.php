@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">📢 Notifikasi</h2>

    @forelse ($notifikasi as $notif)
        <div class="mb-4 border-b pb-3">
            <p class="text-sm text-gray-800">{{ $notif->data['message'] }}</p>
            <p class="text-xs text-gray-500">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
    @empty
        <p class="text-sm text-gray-500">Belum ada notifikasi baru.</p>
    @endforelse
</div>
@endsection
