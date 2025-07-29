@if($notifikasi->count())
    <ul class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
        @foreach($notifikasi as $notif)
        <li class="flex items-start gap-3 p-3 hover:bg-blue-50 transition-all duration-150 group
            {{ $notif->read_at ? '' : 'bg-blue-100/50' }}">
            <div>
                <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800 text-sm {{ $notif->read_at ? '' : 'font-bold' }}">
                        {{ $notif->data['message'] ?? '-' }}
                    </span>
                    @if(!$notif->read_at)
                        <span class="ml-2 inline-block bg-blue-500 rounded-full w-2 h-2"></span>
                    @endif
                </div>
                <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
        </li>
        @endforeach
    </ul>
@else
    <div class="flex flex-col items-center justify-center p-6 text-center text-sm text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Tidak ada notifikasi baru.
    </div>
@endif
