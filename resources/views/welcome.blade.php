<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Lihat tiket kunjungan umum dan paket sekolah The Nice Park Rumpin.">
        <title>The Nice Park Rumpin</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-cream font-sans text-brand-charcoal antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-sm focus:bg-brand-surface focus:px-4 focus:py-3 focus:text-brand-forest focus:ring-2 focus:ring-brand-forest">Lewati ke konten utama</a>
        <header class="bg-brand-forest text-brand-cream">
            <nav x-data="{ open: false }" @keydown.escape.window="open = false" class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12" aria-label="Navigasi utama">
                <div class="flex min-h-20 items-center justify-between gap-5">
                    <a href="#utama" class="flex min-h-11 shrink-0 items-center gap-3 rounded-sm focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 focus:ring-offset-brand-forest">
                        <span class="grid size-10 place-items-center border border-brand-sunshine/70 text-brand-sunshine" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8">
                                <path d="M12 21V9" stroke-linecap="round" />
                                <path d="M12 13c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Z" stroke-linejoin="round" />
                                <path d="M12 17c4.5 0 7-2.5 7-7-4.5 0-7 2.5-7 7Z" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="leading-none">
                            <span class="block text-sm font-semibold tracking-[0.16em]">THE NICE PARK</span>
                            <span class="mt-1 block text-xs tracking-[0.24em] text-brand-sunshine">RUMPIN</span>
                        </span>
                    </a>

                    <div class="hidden items-center gap-5 xl:flex">
                        <a href="#utama" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Beranda</a>
                        <a href="#tiket" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tiket</a>
                        <a href="#paket-sekolah" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Paket sekolah</a>
                        <a href="#tentang" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tentang</a>
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('admin.products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Dashboard admin</a>
                            @else
                                <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Pesanan saya</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Masuk</a>
                            <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80">Daftar</a>
                        @endauth
                    </div>

                    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="mobile-navigation" :aria-label="open ? 'Tutup navigasi' : 'Buka navigasi'" class="grid size-11 shrink-0 place-items-center rounded-sm border border-brand-cream/50 hover:border-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine xl:hidden">
                        <svg x-show="! open" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                        <svg x-show="open" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div id="mobile-navigation" :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t border-brand-cream/20 py-4 xl:hidden">
                    <div class="flex flex-col gap-1">
                        <a @click="open = false" href="#utama" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Beranda</a>
                        <a @click="open = false" href="#tiket" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tiket</a>
                        <a @click="open = false" href="#paket-sekolah" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Paket sekolah</a>
                        <a @click="open = false" href="#tentang" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tentang</a>
                        <div class="mt-3 grid gap-2 border-t border-brand-cream/20 pt-4 sm:grid-cols-2">
                            @auth
                                @if (auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.products.index') }}" class="flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Dashboard admin</a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Pesanan saya</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="flex min-h-11 items-center justify-center rounded-sm border border-brand-cream/50 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Masuk</a>
                                <a href="{{ route('register') }}" class="flex min-h-11 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80">Daftar</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <main id="main-content">
            <section id="utama" class="bg-brand-cream">
                <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12 lg:px-12 lg:py-16">
                    <div class="grid overflow-hidden border border-brand-border bg-brand-forest text-brand-cream lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="flex flex-col items-start justify-center px-6 py-10 sm:px-10 sm:py-14 lg:px-12">
                            <p class="text-sm font-semibold text-brand-sunshine">The Nice Park Rumpin</p>
                            <h1 class="mt-4 max-w-xl text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Rencanakan kunjunganmu.</h1>
                            <p class="mt-5 max-w-lg text-base leading-7 text-brand-cream sm:text-lg">Pilih tiket umum atau ajukan kunjungan sekolah. Harga dan ketersediaan ditampilkan di katalog.</p>
                            <div class="mt-7 flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                                <a href="#tiket" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-sunshine bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-cream focus:ring-offset-2 focus:ring-offset-brand-forest">Pilih tiket umum</a>
                                <a href="#paket-sekolah" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-cream/60 px-5 text-sm font-semibold text-brand-cream hover:border-brand-sunshine hover:text-brand-sunshine active:bg-brand-leaf/70 focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 focus:ring-offset-brand-forest">Paket sekolah</a>
                            </div>
                        </div>

                        <figure role="img" aria-label="Tempat foto utama The Nice Park. Foto sementara belum tersedia." class="relative isolate flex aspect-[4/3] min-h-64 items-end overflow-hidden bg-brand-leaf sm:aspect-[16/9] lg:aspect-auto lg:min-h-[26rem]">
                            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-brand-forest" aria-hidden="true"></div>
                            <div class="absolute -left-12 bottom-[18%] h-48 w-64 rounded-t-full bg-brand-leaf/80 sm:h-64 sm:w-80" aria-hidden="true"></div>
                            <div class="absolute left-[28%] bottom-[12%] h-56 w-72 rounded-t-full bg-brand-forest/80 sm:h-72 sm:w-96" aria-hidden="true"></div>
                            <div class="absolute -right-12 bottom-[14%] h-52 w-64 rounded-t-full bg-brand-leaf/90 sm:h-64 sm:w-80" aria-hidden="true"></div>
                            <div class="absolute right-[18%] top-[18%] size-16 rounded-full bg-brand-sunshine sm:size-20" aria-hidden="true"></div>
                            <figcaption class="relative z-10 m-4 border border-brand-cream/50 bg-brand-forest px-4 py-3 text-sm font-medium text-brand-cream sm:m-6">Tempat foto utama. Gambar sementara, ganti dengan foto lokasi.</figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section id="tentang" class="mx-auto grid max-w-7xl gap-8 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20 lg:px-12 lg:py-24">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-forest">Tentang</p>
                    <h2 class="mt-4 max-w-md text-3xl font-semibold leading-tight tracking-tight text-brand-forest sm:text-4xl">Ruang untuk menikmati waktu bersama.</h2>
                </div>
                <div class="max-w-2xl border-l-2 border-brand-sunshine pl-5 sm:pl-8">
                    <p class="text-lg leading-8 text-brand-charcoal">The Nice Park Rumpin adalah tujuan wisata di Rumpin, Bogor, untuk kunjungan bersama keluarga, teman, maupun rombongan sekolah.</p>
                    <p class="mt-5 leading-7 text-brand-charcoal">Harga dan status produk mengikuti informasi terbaru yang dikelola pengelola di katalog ini.</p>
                </div>
            </section>

            <section id="tiket" class="border-y border-brand-border bg-brand-surface" aria-labelledby="tickets-heading">
                <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-12 lg:py-24">
                    <div class="mb-8 max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-forest">Informasi kunjungan</p>
                        <h2 id="tickets-heading" class="mt-4 max-w-md text-3xl font-semibold leading-tight tracking-tight text-brand-forest sm:text-4xl">Tiket umum</h2>
                        <p class="mt-4 max-w-sm leading-7 text-brand-secondary">Pilih tanggal kunjungan untuk melihat kuota yang tersedia.</p>
                    </div>

                    <div class="catalog-card-grid">
                        @forelse ($generalTickets as $ticket)
                            @php($pricing = $ticketPricing[$ticket->id])
                            <article class="overflow-hidden border border-brand-border bg-brand-cream">
                                <div role="img" aria-label="Tempat foto untuk {{ $ticket->name }}. Foto belum tersedia." class="relative flex aspect-[16/9] items-end overflow-hidden bg-brand-leaf">
                                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-brand-forest" aria-hidden="true"></div>
                                    <div class="absolute -left-5 bottom-[15%] h-24 w-32 rounded-t-full bg-brand-leaf/80" aria-hidden="true"></div>
                                    <div class="absolute left-[30%] bottom-[10%] h-32 w-40 rounded-t-full bg-brand-forest/80" aria-hidden="true"></div>
                                    <p class="relative z-10 m-3 border border-brand-cream/50 bg-brand-forest px-3 py-2 text-xs font-medium text-brand-cream">Foto tiket belum tersedia</p>
                                </div>
                                <div class="flex min-h-64 flex-col p-5">
                                    <h3 class="text-xl font-semibold text-brand-forest">{{ $ticket->name }}</h3>
                                    @if ($ticket->description)
                                        <p class="mt-2 line-clamp-3 leading-7 text-brand-secondary">{{ $ticket->description }}</p>
                                    @endif
                                    <p class="mt-3 text-sm text-brand-secondary">Kuota per tanggal: {{ number_format($ticket->quota, 0, ',', '.') }} tiket</p>
                                    <div class="mt-auto pt-5">
                                        @if ($pricing['is_discounted'])
                                            <p class="text-sm text-brand-secondary line-through">Harga normal: Rp {{ number_format($pricing['normal_unit_amount'], 0, ',', '.') }}</p>
                                            <p class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest"><span class="mr-1 border border-brand-forest px-1.5 py-0.5 text-xs">Promo</span> Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ tiket</span></p>
                                        @else
                                            <p class="inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ tiket</span></p>
                                        @endif
                                        <a href="{{ route('catalog.show', $ticket) }}" class="mt-4 inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest">Lihat detail dan pesan</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="border-y border-brand-border py-8 sm:col-span-2 xl:col-span-3">
                                <h3 class="text-xl font-semibold text-brand-forest">Belum ada tiket umum aktif.</h3>
                                <p class="mt-3 max-w-lg leading-7 text-brand-secondary">Tiket yang tersedia akan muncul di halaman ini setelah pengelola mengaktifkannya. Silakan periksa kembali katalog nanti.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="paket-sekolah" class="bg-brand-forest text-brand-cream" aria-labelledby="school-heading">
                <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-12 lg:py-24">
                    <div class="mb-8 max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-sunshine">Untuk rombongan</p>
                        <h2 id="school-heading" class="mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Paket sekolah</h2>
                        <p class="mt-5 leading-7">Ajukan kunjungan dengan mengisi nama sekolah, penanggung jawab, tanggal, dan jumlah peserta. Admin akan meninjau pengajuan sebelum pembayaran.</p>
                    </div>
                    <div class="catalog-card-grid">
                        @forelse ($schoolPackages as $ticket)
                            @php($pricing = $ticketPricing[$ticket->id])
                            <article class="overflow-hidden border border-brand-border bg-brand-cream text-brand-charcoal">
                                <div role="img" aria-label="Tempat foto untuk {{ $ticket->name }}. Foto belum tersedia." class="relative flex aspect-[16/9] items-end overflow-hidden bg-brand-leaf">
                                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-brand-forest" aria-hidden="true"></div>
                                    <div class="absolute -left-5 bottom-[15%] h-24 w-32 rounded-t-full bg-brand-leaf/80" aria-hidden="true"></div>
                                    <div class="absolute left-[30%] bottom-[10%] h-32 w-40 rounded-t-full bg-brand-forest/80" aria-hidden="true"></div>
                                    <p class="relative z-10 m-3 border border-brand-cream/50 bg-brand-forest px-3 py-2 text-xs font-medium text-brand-cream">Foto paket belum tersedia</p>
                                </div>
                                <div class="flex min-h-72 flex-col p-5">
                                    <h3 class="text-xl font-semibold text-brand-forest">{{ $ticket->name }}</h3>
                                    @if ($ticket->description)
                                        <p class="mt-2 line-clamp-3 leading-7 text-brand-secondary">{{ $ticket->description }}</p>
                                    @endif
                                    <p class="mt-3 text-sm text-brand-secondary">Kuota per tanggal: {{ number_format($ticket->quota, 0, ',', '.') }} peserta</p>
                                    <p class="mt-1 text-sm text-brand-secondary">Harga tetap untuk satu pengajuan, tidak dikalikan jumlah peserta.</p>
                                    <div class="mt-auto pt-5">
                                        @if ($pricing['is_discounted'])
                                            <p class="text-sm text-brand-secondary line-through">Harga normal: Rp {{ number_format($pricing['normal_unit_amount'], 0, ',', '.') }}</p>
                                            <p class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest"><span class="mr-1 border border-brand-forest px-1.5 py-0.5 text-xs">Promo</span> Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ pengajuan</span></p>
                                            <p class="mt-2 text-sm leading-6 text-brand-secondary">Promo dinilai kembali saat checkout setelah persetujuan admin.</p>
                                        @else
                                            <p class="inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ pengajuan</span></p>
                                        @endif
                                        <a href="{{ route('catalog.show', $ticket) }}" class="mt-4 inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest">Lihat detail dan ajukan</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="border-y border-brand-cream/30 py-8 sm:col-span-2 xl:col-span-3">
                                <h3 class="text-xl font-semibold text-brand-cream">Belum ada paket sekolah aktif.</h3>
                                <p class="mt-3 max-w-lg leading-7 text-brand-cream">Paket sekolah akan muncul di halaman ini setelah pengelola menambahkannya dan mengaktifkan statusnya.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-brand-forest/90 text-brand-cream">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
                <a href="#utama" class="inline-flex min-h-11 w-fit items-center rounded-sm text-sm font-semibold tracking-[0.14em] active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">THE NICE PARK RUMPIN</a>
                <nav class="flex flex-wrap gap-x-5 gap-y-2 text-sm" aria-label="Navigasi footer">
                    <a href="#tentang" class="inline-flex min-h-11 items-center rounded-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tentang</a>
                    <a href="#tiket" class="inline-flex min-h-11 items-center rounded-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tiket umum</a>
                    <a href="#paket-sekolah" class="inline-flex min-h-11 items-center rounded-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Paket sekolah</a>
                </nav>
                <p class="border-t border-brand-cream/20 pt-5 text-sm sm:border-0 sm:pt-0">© {{ now()->year }} The Nice Park Rumpin</p>
            </div>
        </footer>
    </body>
</html>
