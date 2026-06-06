<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang kembali</h1>
        <p class="mt-1 text-sm text-gray-500">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-indigo-600 hover:text-indigo-800 font-medium" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            <label for="remember_me" class="ms-2 text-sm text-gray-600">Ingat saya</label>
        </div>

        <button type="submit" class="w-full flex justify-center items-center px-4 py-2.5 bg-indigo-900 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Masuk
        </button>

        <p class="text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                Daftar sekarang
            </a>
        </p>
    </form>
</x-guest-layout>
