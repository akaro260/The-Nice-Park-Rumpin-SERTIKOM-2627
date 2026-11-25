<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-secondary">Area pelanggan</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-brand-forest sm:text-4xl">Pesanan Anda</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-12 lg:py-14">
        @if (session('success'))
            <div class="mb-6 border-l-4 border-brand-leaf bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-6 border-l-4 border-brand-forest bg-brand-sunshine px-5 py-4 text-sm text-brand-charcoal" role="status">
                {{ session('info') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">
                <p class="font-semibold">Tindakan belum dapat diselesaikan.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($registrations->isEmpty())
            <section class="border border-brand-border bg-brand-surface px-6 py-12 sm:px-10" aria-labelledby="empty-orders">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-forest">Belum ada pesanan</p>
                <h2 id="empty-orders" class="mt-3 max-w-xl text-2xl font-semibold leading-tight text-brand-forest">Pesanan dan pengajuan Anda akan muncul di sini.</h2>
                <p class="mt-4 max-w-xl leading-7 text-brand-secondary">Pilih tiket umum untuk memesan atau paket sekolah untuk mengirim pengajuan kunjungan.</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('home') }}#tiket" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 focus:outline-none focus:ring-2 focus:ring-brand-forest">Lihat katalog</a>
                    <a href="{{ route('profile.edit') }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Perbarui profil</a>
                </div>
            </section>
        @else
            <section class="space-y-4" aria-label="Daftar pesanan kunjungan">
                @foreach ($registrations as $registration)
                    @php
                        $payment = $registration->payments->first();
                        $displayTotal = $payment?->gross_amount ?? $registration->total_price;
                    @endphp
                    <article class="border border-brand-border bg-brand-surface p-5 sm:p-6">
                        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-start">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-secondary">{{ $registration->ticket?->type === 'school' ? 'Paket sekolah' : 'Tiket umum' }}</p>
                                <h2 class="mt-2 text-xl font-semibold text-brand-forest">{{ $registration->ticket?->name ?? 'Produk tidak tersedia' }}</h2>
                                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                    <div><dt class="text-brand-secondary">Kode pesanan</dt><dd class="mt-1 break-all font-semibold text-brand-charcoal">{{ $registration->registration_code }}</dd></div>
                                    <div><dt class="text-brand-secondary">Tanggal kunjungan</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $registration->visit_date?->format('d/m/Y') ?? 'Belum ditentukan' }}</dd></div>
                                    <div><dt class="text-brand-secondary">{{ $registration->ticket?->type === 'school' ? 'Jumlah peserta' : 'Jumlah tiket' }}</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ number_format($registration->quantity, 0, ',', '.') }}</dd></div>
                                    <div><dt class="text-brand-secondary">Total</dt><dd class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format((float) $displayTotal, 0, ',', '.') }}</dd></div>
                                    @if ($payment && $payment->discount_amount > 0)
                                        <div><dt class="text-brand-secondary">Harga normal</dt><dd class="mt-1 font-semibold text-brand-charcoal">Rp {{ number_format((float) $payment->subtotal_amount, 0, ',', '.') }}</dd></div>
                                        <div>
                                            <dt class="text-brand-secondary">Diskon</dt>
                                            <dd class="mt-1 font-semibold text-brand-charcoal">
                                                {{ $payment->discount_type === 'percentage' ? number_format((float) $payment->discount_value, 0, ',', '.').'%' : 'Rp '.number_format((float) $payment->discount_value, 0, ',', '.') }}
                                                <span class="text-brand-secondary">(hemat Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }})</span>
                                            </dd>
                                        </div>
                                        @if ($payment->promo_code)
                                            <div><dt class="text-brand-secondary">Kode redeem</dt><dd class="mt-1 break-all font-mono font-semibold text-brand-charcoal">{{ $payment->promo_code }}</dd></div>
                                        @endif
                                    @endif
                                    @if ($registration->school_name)
                                        <div><dt class="text-brand-secondary">Sekolah</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $registration->school_name }}</dd></div>
                                        <div><dt class="text-brand-secondary">Penanggung jawab</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $registration->contact_person }}</dd></div>
                                    @endif
                                    <div><dt class="text-brand-secondary">Status pesanan</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ ucfirst(str_replace('_', ' ', $registration->status)) }}</dd></div>
                                    @if ($payment)
                                        <div><dt class="text-brand-secondary">Status pembayaran</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ ucfirst($payment->transaction_status) }}</dd></div>
                                    @endif
                                </dl>
                                @if ($registration->rejection_reason)
                                    <p class="mt-4 border-l-2 border-brand-forest pl-3 text-sm leading-6 text-brand-charcoal">Alasan penolakan: {{ $registration->rejection_reason }}</p>
                                @endif
                                @if ($registration->e_ticket_code)
                                    <div class="mt-5 border-t border-brand-border pt-5" role="status">
                                        <p class="text-sm font-semibold text-brand-forest">E-ticket terverifikasi</p>
                                        <p class="mt-1 break-all font-mono text-lg text-brand-forest">{{ $registration->e_ticket_code }}</p>
                                        <p class="mt-1 text-sm text-brand-secondary">Tunjukkan kode ini saat kunjungan.</p>
                                    </div>
                                @endif
                            </div>

                            @if ($registration->status === 'approved' && $registration->ticket?->type === 'school')
                                <form method="POST" action="{{ route('payments.checkout', $registration) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="redeem_code_{{ $registration->id }}" class="block text-sm font-semibold text-brand-charcoal">Kode redeem (opsional)</label>
                                        <input id="redeem_code_{{ $registration->id }}" name="redeem_code" type="text" value="{{ old('redeem_code') }}" maxlength="32" autocomplete="off" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface uppercase text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                                        <p class="mt-1 text-sm text-brand-secondary">Kode akan menggantikan promo produk dan hanya dapat dipakai satu kali total.</p>
                                    </div>
                                    <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 focus:outline-none focus:ring-2 focus:ring-brand-forest">Bayar melalui Midtrans</button>
                                </form>
                            @elseif ($registration->status === 'awaiting_payment' && $payment?->snap_url)
                                <div class="flex flex-col gap-3">
                                    <a href="{{ $payment->snap_url }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 focus:outline-none focus:ring-2 focus:ring-brand-forest">Lanjutkan pembayaran</a>
                                    <form method="POST" action="{{ route('payments.status', $registration) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Periksa status pembayaran</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="mt-6">{{ $registrations->links() }}</div>
        @endif
    </main>
</x-app-layout>
