<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-white to-blue-100">
        <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-xl">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Kehadirapps"class="w-24 mx-auto mb-3 shadow-none">
                    <h1 class="text-2xl font-bold text-blue-700">Selamat Datang</h1>
                <p class="text-sm text-gray-500">Silakan masuk untuk mengakses dashboard admin</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required autofocus
                        class="mt-1 block w-full px-4 py-2 border rounded-xl focus:ring-blue-400 focus:border-blue-400 border-gray-300 shadow-sm">
                    @error('email')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="mt-1 block w-full px-4 py-2 border rounded-xl focus:ring-blue-400 focus:border-blue-400 border-gray-300 shadow-sm">
                    @error('password')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4 flex items-center">
                    <input id="remember_me" name="remember" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                </div>

                <!-- Submit Button -->
                <div class="mb-4">
                    <button type="submit"
                        class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition">
                        Login
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center">
                        <a class="text-sm text-blue-600 hover:underline" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>
