<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $ticket->description ?: 'Detail produk The Nice Park Rumpin.' }}">
        <title>{{ $ticket->name }} | The Nice Park Rumpin</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-cream font-sans text-brand-charcoal antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-sm focus:bg-brand-surface focus:px-4 focus:py-3 focus:text-brand-forest focus:ring-2 focus:ring-brand-forest">Lewati ke konten utama</a>
        <header class="bg-brand-forest text-brand-cream">
            <nav class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-5 px-5 sm:px-8 lg:px-12" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center rounded-sm text-sm font-semibold tracking-[0.14em] focus:outline-none focus:ring-2 focus:ring-brand-sunshine">THE NICE PARK RUMPIN</a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}#{{ $ticket->type === 'school' ? 'paket-sekolah' : 'tiket' }}" class="inline-flex min-h-11 items-center rounded-sm px-3 text-sm hover:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Kembali ke katalog</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center rounded-sm border border-brand-cream/50 px-3 text-sm hover:border-brand-sunshine hover:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Pesanan saya</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-sm border border-brand-cream/50 px-3 text-sm hover:border-brand-sunshine hover:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Masuk</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main id="main-content" class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12 lg:py-20">
            <div class="grid gap-10 lg:grid-cols-[1fr_0.8fr] lg:items-start lg:gap-20">
                <section aria-labelledby="product-heading">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-forest">{{ $ticket->type === 'school' ? 'Paket sekolah' : 'Tiket umum' }}</p>
                    <h1 id="product-heading" class="mt-4 max-w-2xl text-3xl font-semibold leading-tight tracking-tight text-brand-forest sm:text-4xl lg:text-5xl">{{ $ticket->name }}</h1>
                    @if ($ticket->description)
                        <p class="mt-6 max-w-2xl whitespace-pre-line text-base leading-7 text-brand-charcoal">{{ $ticket->description }}</p>
                    @endif

                    <div class="mt-8 border-y border-brand-border py-6">
                        <p class="text-sm text-brand-charcoal">{{ $ticket->type === 'school' ? 'Harga untuk satu pengajuan' : 'Harga per tiket' }}</p>
                        @if ($pricing['is_discounted'])
                            <p class="mt-2 text-sm text-brand-secondary line-through">Harga normal: Rp {{ number_format($pricing['normal_unit_amount'], 0, ',', '.') }}</p>
                            <p class="mt-1 inline-block bg-brand-sunshine px-2 py-1 text-2xl font-semibold text-brand-forest"><span class="mr-2 border border-brand-forest px-2 py-1 text-xs align-middle">Promo</span>Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }}</p>
                            @if ($ticket->type === 'school')
                                <p class="mt-2 text-sm leading-6 text-brand-secondary">Promo dihitung kembali saat checkout setelah pengajuan disetujui admin.</p>
                            @endif
                        @else
                            <p class="mt-1 inline-block bg-brand-sunshine px-2 py-1 text-2xl font-semibold text-brand-forest">Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }}</p>
                        @endif
                        <p class="mt-3 text-sm leading-6 text-brand-charcoal">Kuota awal per tanggal: {{ number_format($ticket->quota, 0, ',', '.') }} {{ $ticket->type === 'school' ? 'peserta' : 'tiket' }}. Ketersediaan akhir diperiksa kembali saat pemesanan.</p>
                    </div>

                    @if ($visitDate)
                        <div class="mt-6 border-l-2 border-brand-sunshine bg-brand-surface px-5 py-4" role="status" aria-live="polite">
                            <p class="font-semibold text-brand-forest">Ketersediaan {{ \Carbon\Carbon::parse($visitDate)->format('d/m/Y') }}</p>
                            @if ($availableQuota > 0)
                                <p class="mt-1 text-sm text-brand-charcoal">{{ number_format($availableQuota, 0, ',', '.') }} {{ $ticket->type === 'school' ? 'peserta' : 'tiket' }} masih tersedia.</p>
                            @else
                                <p class="mt-1 text-sm text-brand-charcoal">Kuota untuk tanggal ini sudah penuh. Pilih tanggal lain.</p>
                            @endif
                        </div>
                    @endif
                </section>

                <aside class="border border-brand-border bg-brand-surface p-5 sm:p-8" aria-labelledby="booking-heading">
                    <h2 id="booking-heading" class="text-2xl font-semibold text-brand-forest">{{ $ticket->type === 'school' ? 'Ajukan paket' : 'Pesan tiket' }}</h2>
                    <p class="mt-2 leading-7 text-brand-secondary">
                        {{ $ticket->type === 'school'
                            ? 'Isi data sekolah dan jumlah peserta. Pengajuan perlu disetujui admin sebelum pembayaran.'
                            : 'Pilih tanggal dan jumlah tiket. Pembayaran dilanjutkan melalui Midtrans.' }}
                    </p>
                    @error('payment')
                        <p class="mt-4 border-l-4 border-brand-forest bg-brand-surface px-4 py-3 text-sm text-brand-charcoal" role="alert">{{ $message }}</p>
                    @enderror

                    <form method="GET" action="{{ route('catalog.show', $ticket) }}" class="mt-6 border-t border-brand-border pt-6">
                        <label for="availability-date" class="block text-sm font-semibold text-brand-charcoal">Periksa kuota berdasarkan tanggal</label>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                            <input id="availability-date" type="date" name="date" value="{{ $visitDate }}" min="{{ now()->toDateString() }}" required class="min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                            <button type="submit" class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-cream active:bg-brand-border focus:outline-none focus:ring-2 focus:ring-brand-forest">Periksa kuota</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('bookings.store', $ticket) }}" class="mt-6 border-t border-brand-border pt-6">
                        @csrf
                        @if ($ticket->type === 'school')
                            <div>
                                <label for="school_name" class="block text-sm font-semibold text-brand-charcoal">Nama sekolah</label>
                                <input id="school_name" name="school_name" type="text" value="{{ old('school_name') }}" maxlength="255" required autocomplete="organization" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                                @error('school_name') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                            </div>
                            <div class="mt-4">
                                <label for="contact_person" class="block text-sm font-semibold text-brand-charcoal">Nama penanggung jawab</label>
                                <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}" maxlength="255" required autocomplete="name" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                                @error('contact_person') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="mt-4">
                            <label for="visit_date" class="block text-sm font-semibold text-brand-charcoal">Tanggal kunjungan</label>
                            <input id="visit_date" name="visit_date" type="date" value="{{ old('visit_date', $visitDate) }}" min="{{ now()->toDateString() }}" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                            @error('visit_date') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-4">
                            <label for="quantity" class="block text-sm font-semibold text-brand-charcoal">{{ $ticket->type === 'school' ? 'Jumlah peserta' : 'Jumlah tiket' }}</label>
                            <input id="quantity" name="quantity" type="number" value="{{ old('quantity', 1) }}" min="1" max="{{ $ticket->quota }}" step="1" required inputmode="numeric" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                            @error('quantity') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                        </div>

                        @if ($ticket->type === 'school')
                            <p class="mt-4 text-sm leading-6 text-brand-secondary">Harga paket Rp {{ number_format($pricing['unit_amount'], 0, ',', '.') }} untuk satu pengajuan, berapa pun jumlah peserta. Masukkan kode redeem saat membayar setelah pengajuan disetujui admin.</p>
                        @else
                            <div class="mt-4">
                                <label for="redeem_code" class="block text-sm font-semibold text-brand-charcoal">Kode redeem (opsional)</label>
                                <input id="redeem_code" name="redeem_code" type="text" value="{{ old('redeem_code') }}" maxlength="32" autocomplete="off" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface uppercase text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                                @error('redeem_code') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                                <p class="mt-2 text-sm text-brand-secondary">Kode redeem menggantikan promo produk dan hanya dapat dipakai satu kali total.</p>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-brand-secondary">Total akhir dihitung server dari harga dan promo yang berlaku saat pemesanan.</p>
                        @endif

                        @if ($visitDate && $availableQuota < 1)
                            <button type="submit" disabled class="mt-6 inline-flex min-h-12 w-full cursor-not-allowed items-center justify-center rounded-sm bg-brand-border px-5 text-sm font-semibold text-brand-charcoal">Kuota tanggal ini penuh</button>
                        @else
                            <button type="submit" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest focus:ring-offset-2 focus:ring-offset-brand-surface">
                                {{ $ticket->type === 'school' ? 'Kirim pengajuan paket' : 'Pesan dan lanjutkan pembayaran' }}
                            </button>
                        @endif
                    </form>

                    @guest
                        <p class="mt-5 border-t border-brand-border pt-5 text-sm leading-6 text-brand-secondary">Masuk atau buat akun sebelum mengirim pemesanan.</p>
                    @endguest
                </aside>
            </div>
        </main>

        <footer class="bg-brand-forest text-brand-cream">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-7 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 w-fit items-center rounded-sm text-sm font-semibold tracking-[0.14em] focus:outline-none focus:ring-2 focus:ring-brand-sunshine">THE NICE PARK RUMPIN</a>
                <a href="{{ route('home') }}#{{ $ticket->type === 'school' ? 'paket-sekolah' : 'tiket' }}" class="inline-flex min-h-11 w-fit items-center rounded-sm text-sm hover:text-brand-sunshine focus:outline-none focus:ring-2 focus:ring-brand-sunshine">Kembali ke katalog</a>
            </div>
        </footer>
    </body>
</html>
