<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kehadirapps - Admin</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    @keyframes fadeInMove {
        0% { opacity: 0; transform: translateY(-16px) scale(0.97);}
        100% { opacity: 1; transform: translateY(0) scale(1);}
    }
    .animate-fade-in-move {
        animation: fadeInMove 0.4s cubic-bezier(.42,0,.58,1.0) both;
    }
    /* Sidebar */
    .sidebar-floating {
        box-shadow: 0 8px 32px #2563eb18, 0 1.5px 8px #94a3b83a;
    }
    /* Menu sidebar active */
    .nav-active {
        background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 2px 8px #2563eb22;
        position: relative;
        overflow: hidden;
    }
    /* Sidebar hover animasi */
    .nav-anim {
        position: relative;
        transition: background 0.2s, color 0.2s, transform 0.18s;
        z-index: 1;
    }
    .nav-anim::after {
        content: '';
        position: absolute;
        left: 16px; right: 16px; bottom: 6px;
        height: 3px;
        background: linear-gradient(90deg, #38bdf8, #6366f1);
        border-radius: 4px;
        opacity: 0;
        transform: scaleX(0.6);
        transition: all 0.25s cubic-bezier(.65,0,.76,1.5);
        z-index: -1;
    }
    .nav-anim:hover, .nav-anim:focus-visible {
        background: linear-gradient(90deg, #f0f9ff 0%, #e0e7ff 100%);
        color: #2563eb !important;
        transform: scale(1.05) translateX(4px);
        box-shadow: 0 4px 16px #38bdf813;
    }
    .nav-anim:hover::after, .nav-anim:focus-visible::after {
        opacity: 1;
        transform: scaleX(1);
    }
    @media (max-width: 768px) {
        aside.w-64 {
            display: none;
        }
    }
    /* Notif badge animasi */
    .notif-glow {
        box-shadow: 0 0 6px 2px #2563eb77;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 6px 2px #2563eb77; }
        70% { box-shadow: 0 0 14px 5px #60a5fa66; }
        100% { box-shadow: 0 0 6px 2px #2563eb77; }
    }
</style>
@stack('styles')

</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 min-h-screen font-sans overflow-x-hidden">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white/95 sidebar-floating rounded-r-3xl shadow-lg hidden md:flex flex-col animate-fade-in-move transition-all">
            <div class="p-7 border-b flex flex-col items-center bg-gradient-to-b from-blue-600 to-blue-400 rounded-br-3xl shadow-md mb-2">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="w-16 h-16 mb-2 rounded-xl shadow-lg border-2 border-blue-100 bg-white">
                <h2 class="text-2xl font-extrabold text-white drop-shadow">Kehadirapps</h2>
                <span class="text-xs font-medium text-blue-100 mt-1 tracking-widest">Admin Panel</span>
            </div>
            <nav class="p-6 flex-1 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                    {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-gray-700' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.karyawan.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.karyawan.index') ? 'nav-active' : 'text-gray-700' }}">
                    Manajemen Karyawan
                </a>
                <a href="{{ route('admin.pengajuan_izin.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.pengajuan_izin.index') ? 'nav-active' : 'text-gray-700' }}">
                    Pengajuan Izin
                </a>
                <a href="{{ route('admin.lokasi-kantor.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.lokasi-kantor.index') ? 'nav-active' : 'text-gray-700' }}">
                    Lokasi Kantor
                </a>
                <a href="{{ route('admin.riwayat.absensi.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.riwayat.absensi.index') ? 'nav-active' : 'text-gray-700' }}">
                    Riwayat Absensi
                </a>
                <a href="{{ route('admin.jadwal-kerja.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.jadwal-kerja.index') ? 'nav-active' : 'text-gray-700' }}">
                    Jadwal Kerja
                </a>
                <a href="{{ route('admin.pengaturan.index') }}"
                   class="block px-5 py-3 rounded-xl nav-anim transition-all
                   {{ request()->routeIs('admin.pengaturan.index') ? 'nav-active' : 'text-gray-700' }}">
                    Pengaturan
                </a>
            </nav>
            <div class="text-center text-xs p-4 mt-auto text-blue-400">
                <span class="opacity-70">© {{ date('Y') }} Kehadirapps</span>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-6 md:ml-0 animate-fade-in-move">
            <!-- Topbar -->
            <div class="flex justify-between items-center mb-8 sticky top-0 z-40 bg-white/70 backdrop-blur-md rounded-2xl px-4 py-3 shadow-lg animate-fade-in-move">
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 drop-shadow-lg tracking-tight flex items-center gap-2">
                    @yield('title', 'Dashboard')
                </h1>
                <div class="flex items-center space-x-6">
                    <!-- Notifikasi -->
                    <a href="{{ route('admin.notifikasi') }}" class="relative group" id="notifBell">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-600 hover:text-blue-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @php
                            $jumlahNotifikasi = auth()->user()?->unreadNotifications()->count();
                        @endphp
                        @if($jumlahNotifikasi > 0)
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full notif-glow shadow animate-fade-in-move">
                            {{ $jumlahNotifikasi }}
                        </span>
                        @endif
                        <!-- Popup Notifikasi -->
                        <div id="notifPopup"
                        class="hidden absolute right-0 mt-3 w-96 bg-white shadow-2xl rounded-xl z-50 border border-gray-200 animate-fade-in-move"
                        style="min-width:260px; max-width:96vw;">
                        <div class="flex items-center gap-2 p-3 border-b bg-gradient-to-r from-blue-100 to-blue-50 rounded-t-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 00-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="font-bold text-blue-700">Notifikasi Baru</span>
                        </div>
                        <div id="notifPopupList" class="bg-white"></div>
                        <div class="text-right p-3 border-t bg-gray-50 rounded-b-xl">
                            <a href="{{ route('admin.notifikasi') }}" class="text-xs text-blue-600 hover:underline font-semibold">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                    </a>
                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button title="Logout" class="ml-1 text-gray-600 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-50 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a1 1 0 01-1 1H5a2 2 0 01-2-2V7a2 2 0 012-2h7a1 1 0 011 1v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <!-- Page content -->
            <div class="mt-2 animate-fade-in-move">
                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bell = document.getElementById('notifBell');
            const popup = document.getElementById('notifPopup');
            const popupList = document.getElementById('notifPopupList');
            let popupVisible = false;

            bell.addEventListener('click', function(e) {
                e.preventDefault();
                if (popupVisible) {
                    popup.classList.add('hidden');
                    popupVisible = false;
                    return;
                }
                fetch("{{ route('admin.notifikasi.popup') }}")
                    .then(res => res.json())
                    .then(res => {
                        popupList.innerHTML = res.html;
                        popup.classList.remove('hidden');
                        popupVisible = true;
                    });
            });
            document.addEventListener('click', function(event) {
                if (!bell.contains(event.target) && popupVisible) {
                    popup.classList.add('hidden');
                    popupVisible = false;
                }
            });
        });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
