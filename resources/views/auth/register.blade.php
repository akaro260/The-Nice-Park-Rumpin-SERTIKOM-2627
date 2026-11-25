<x-guest-layout title="Daftar | The Nice Park Rumpin">
    <h1 class="text-3xl font-semibold tracking-tight text-brand-forest">Daftar akun</h1>
    <p class="mt-3 mb-8 leading-7 text-brand-secondary">Buat akun untuk menyimpan rencana kunjungan.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata sandi" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 flex justify-end">
            <x-primary-button class="ms-4">
                Daftar
            </x-primary-button>
        </div>
    </form>

    <div class="mt-8 border-t border-brand-border pt-6 text-center">
        <p class="text-sm text-brand-secondary">Sudah punya akun?</p>
        <a href="{{ route('login') }}"
            class="mt-3 inline-flex min-h-12 w-42 items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream">Masuk</a>
    </div>

</x-guest-layout>
