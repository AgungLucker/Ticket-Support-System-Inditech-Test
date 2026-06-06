<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Buat akun baru</h1>
        <p class="mt-1 text-sm text-gray-500">Daftar untuk membuat dan melacak tiket support Anda</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full flex justify-center items-center px-4 py-2.5 bg-indigo-900 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Buat akun
        </button>

        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                Masuk di sini
            </a>
        </p>
    </form>
</x-guest-layout>
