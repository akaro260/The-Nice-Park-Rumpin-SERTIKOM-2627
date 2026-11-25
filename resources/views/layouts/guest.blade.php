@props(['title' => 'The Nice Park Rumpin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-brand-charcoal antialiased">
        <div class="min-h-screen bg-brand-cream px-5 py-8 sm:px-8 sm:py-12">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl overflow-hidden border border-brand-cream/20 bg-brand-surface lg:grid-cols-[0.85fr_1.15fr]">
                <aside class="hidden flex-col justify-between bg-brand-forest p-10 text-brand-cream lg:flex">
                    <a href="/" class="flex w-fit items-center gap-3 rounded-sm focus:outline-none focus:ring-2 focus:ring-brand-sunshine">
                        <span class="grid size-11 place-items-center border border-brand-sunshine/70 text-brand-sunshine" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8">
                                <path d="M12 21V9" stroke-linecap="round" />
                                <path d="M12 13c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Z" stroke-linejoin="round" />
                                <path d="M12 17c4.5 0 7-2.5 7-7-4.5 0-7 2.5-7 7Z" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold tracking-[0.2em]">THE NICE PARK</span>
                            <span class="mt-1 block text-xs tracking-[0.32em] text-brand-sunshine">RUMPIN</span>
                        </span>
                    </a>
                    <p class="max-w-sm text-3xl font-semibold leading-tight">Masuk untuk melanjutkan rencana kunjungan Anda.</p>
                </aside>

                <main class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-16">
                    <div class="w-full max-w-md">
                        <a href="/" class="mb-10 flex items-center gap-3 rounded-sm text-brand-forest lg:hidden">
                            <span class="grid size-10 place-items-center border border-brand-forest text-brand-forest" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8">
                                    <path d="M12 21V9" stroke-linecap="round" />
                                    <path d="M12 13c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Z" stroke-linejoin="round" />
                                    <path d="M12 17c4.5 0 7-2.5 7-7-4.5 0-7 2.5-7 7Z" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="text-sm font-semibold tracking-[0.18em]">THE NICE PARK RUMPIN</span>
                        </a>
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
