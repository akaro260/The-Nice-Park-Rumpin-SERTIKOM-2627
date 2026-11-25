<x-guest-layout title="Masuk | The Nice Park Rumpin">
    <title>Login</title>
    <h1 class="text-3xl font-semibold tracking-tight text-brand-forest">Masuk ke akun</h1>
    <p class="mt-3 mb-8 leading-7 text-brand-secondary">Gunakan email dan kata sandi yang telah Anda daftarkan.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata sandi" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-brand-secondary text-brand-forest shadow-sm focus:ring-brand-forest"
                    name="remember">
                <span class="ms-2 text-sm text-brand-secondary ">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">

            @if (Route::has('password.request'))
                <a class="underline text-sm text-brand-secondary hover:text-brand-charcoal rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-forest"
                    href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button class="ms-3 min-w-32">
                Masuk
            </x-primary-button>

        </div>

    </form>
    <div class="mt-8 border-t border-brand-border pt-6 text-center">
        <p class="text-sm text-brand-secondary">Belum punya akun?</p>
        <a href="{{ route('register') }}"
            class="mt-3 inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream">Daftar</a>
    </div>

</x-guest-layout>