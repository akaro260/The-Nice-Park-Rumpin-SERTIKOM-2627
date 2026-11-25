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
                        <a href="#tentang" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tentang</a>
                        <a href="#tiket" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tiket</a>
                        <a href="#paket-sekolah" class="flex min-h-11 items-center rounded-sm px-1 text-sm hover:text-brand-sunshine active:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Paket sekolah</a>
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
                        <a @click="open = false" href="#tentang" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tentang</a>
                        <a @click="open = false" href="#tiket" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Tiket</a>
                        <a @click="open = false" href="#paket-sekolah" class="flex min-h-11 items-center rounded-sm px-2 hover:bg-brand-leaf/70 active:bg-brand-leaf/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Paket sekolah</a>
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
            <section id="utama" class="overflow-hidden bg-brand-forest text-brand-cream">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 pb-16 pt-12 sm:px-8 sm:pb-20 sm:pt-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16 lg:px-12 lg:py-24">
                    <div class="max-w-2xl">
                        <p class="mb-5 text-xs font-semibold uppercase tracking-[0.18em] text-brand-sunshine">Rumpin, Bogor</p>
                        <h1 class="max-w-xl text-4xl font-semibold leading-[1.06] tracking-tight sm:text-5xl lg:text-6xl">Nikmati waktu yang terasa lebih lapang.</h1>
                        <p class="mt-6 max-w-lg text-base leading-7 text-brand-cream sm:text-lg">Lihat pilihan tiket kunjungan dan paket sekolah, lalu pesan melalui website.</p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <a href="#tiket" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 focus:ring-offset-brand-forest">Lihat tiket umum</a>
                            <a href="#paket-sekolah" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-cream/50 px-5 text-sm font-semibold text-brand-cream hover:border-brand-sunshine hover:text-brand-sunshine active:bg-brand-leaf/70 focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 focus:ring-offset-brand-forest">Lihat paket sekolah</a>
                        </div>
                    </div>

                    <figure class="relative isolate min-h-72 overflow-hidden border border-brand-cream/25 bg-brand-leaf/70 sm:min-h-80 lg:min-h-[25rem]" aria-labelledby="hero-visual-caption">
                        <div class="absolute inset-x-0 bottom-0 h-[44%] bg-brand-forest/90" aria-hidden="true"></div>
                        <div class="absolute -left-14 bottom-[25%] size-64 rounded-full bg-brand-leaf/80" aria-hidden="true"></div>
                        <div class="absolute left-[24%] bottom-[16%] size-80 rounded-full bg-brand-leaf" aria-hidden="true"></div>
                        <div class="absolute -right-16 bottom-[20%] size-72 rounded-full bg-brand-leaf/90" aria-hidden="true"></div>
                        <div class="absolute left-[50%] top-12 h-24 w-px bg-brand-sunshine/70" aria-hidden="true"></div>
                        <div class="absolute left-[50%] top-36 size-4 -translate-x-1/2 rounded-full bg-brand-adventure" aria-hidden="true"></div>
                        <div class="absolute inset-x-5 top-5 flex items-start justify-between bg-brand-forest/90 px-3 py-2 text-xs font-medium text-brand-cream">
                            <span>The Nice Park Rumpin</span>
                            <span>Ilustrasi lanskap</span>
                        </div>
                        <figcaption id="hero-visual-caption" class="absolute bottom-5 left-5 max-w-56 bg-brand-forest/90 p-3 text-sm leading-6 text-brand-cream">Ilustrasi sementara, foto lokasi belum tersedia.</figcaption>
                    </figure>
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
                <div class="mx-auto grid max-w-7xl gap-8 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20 lg:px-12 lg:py-24">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-forest">Informasi kunjungan</p>
                        <h2 id="tickets-heading" class="mt-4 max-w-md text-3xl font-semibold leading-tight tracking-tight text-brand-forest sm:text-4xl">Tiket umum</h2>
                        <p class="mt-4 max-w-sm leading-7 text-brand-secondary">Pilih tanggal kunjungan untuk melihat kuota yang tersedia.</p>
                    </div>

                    <div>
                        @forelse ($generalTickets as $ticket)
                            @php($pricing = $ticketPricing[$ticket->id])
                            <article class="grid gap-4 border-b border-brand-border py-6 first:pt-0 sm:grid-cols-[1fr_auto] sm:items-start sm:gap-8">
                                <div>
                                    <h3 class="text-xl font-semibold text-brand-forest">{{ $ticket->name }}</h3>
                                    @if ($ticket->description)
                                        <p class="mt-2 leading-7 text-brand-secondary">{{ $ticket->description }}</p>
                                    @endif
                                    <p class="mt-3 text-sm text-brand-secondary">Kuota per tanggal: {{ number_format($ticket->quota, 0, ',', '.') }} tiket</p>
                                </div>
                                <div class="sm:text-right">
                                    @if ($pricing['is_discounted'])
                                        <p class="text-sm text-brand-secondary line-through">Harga normal: Rp {{ number_format($pricing['normal_unit_amount'], 0, ',', '.') }}</p>
                                        <p class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest"><span class="mr-1 border border-brand-forest px-1.5 py-0.5 text-xs">Promo</span> Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ tiket</span></p>
                                    @else
                                        <p class="inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal">/ tiket</span></p>
                                    @endif
                                    <a href="{{ route('catalog.show', $ticket) }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-cream active:bg-brand-border focus:outline-none focus:ring-2 focus:ring-brand-forest">Lihat detail dan pesan</a>
                                </div>
                            </article>
                        @empty
                            <div class="border-y border-brand-border py-8">
                                <h3 class="text-xl font-semibold text-brand-forest">Belum ada tiket umum aktif.</h3>
                                <p class="mt-3 max-w-lg leading-7 text-brand-secondary">Tiket yang tersedia akan muncul di halaman ini setelah pengelola mengaktifkannya. Silakan periksa kembali katalog nanti.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="paket-sekolah" class="bg-brand-forest text-brand-cream" aria-labelledby="school-heading">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20 lg:px-12 lg:py-24">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-sunshine">Untuk rombongan</p>
                        <h2 id="school-heading" class="mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Paket sekolah</h2>
                        <p class="mt-5 leading-7">Ajukan kunjungan dengan mengisi nama sekolah, penanggung jawab, tanggal, dan jumlah peserta. Admin akan meninjau pengajuan sebelum pembayaran.</p>
                    </div>
                    <div>
                        @forelse ($schoolPackages as $ticket)
                            @php($pricing = $ticketPricing[$ticket->id])
                            <article class="border-t border-brand-cream/30 py-6 first:pt-0">
                                <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-start sm:gap-8">
                                    <div>
                                        <h3 class="text-xl font-semibold">{{ $ticket->name }}</h3>
                                        @if ($ticket->description)
                                            <p class="mt-2 leading-7 text-brand-cream">{{ $ticket->description }}</p>
                                        @endif
                                        <p class="mt-3 text-sm text-brand-cream">Kuota per tanggal: {{ number_format($ticket->quota, 0, ',', '.') }} peserta</p>
                                        <p class="mt-1 text-sm text-brand-cream">Harga tetap untuk satu pengajuan, tidak dikalikan jumlah peserta.</p>
                                    </div>
                                    <div class="sm:text-right">
                                        @if ($pricing['is_discounted'])
                                            <p class="text-sm text-brand-cream line-through">Harga normal: Rp {{ number_format($pricing['normal_unit_amount'], 0, ',', '.') }}</p>
                                            <p class="mt-1 font-semibold text-brand-sunshine"><span class="mr-1 border border-brand-sunshine px-1.5 py-0.5 text-xs">Promo</span> Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal text-brand-cream">/ pengajuan</span></p>
                                            <p class="mt-2 max-w-56 text-sm leading-6 text-brand-cream sm:ml-auto">Promo dinilai kembali saat checkout setelah persetujuan admin.</p>
                                        @else
                                            <p class="font-semibold text-brand-sunshine">Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} <span class="text-sm font-normal text-brand-cream">/ pengajuan</span></p>
                                        @endif
                                        <a href="{{ route('catalog.show', $ticket) }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-sunshine focus:ring-offset-2 focus:ring-offset-brand-forest">Lihat detail dan ajukan</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="border-y border-brand-cream/30 py-8">
                                <h3 class="text-xl font-semibold">Belum ada paket sekolah aktif.</h3>
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
