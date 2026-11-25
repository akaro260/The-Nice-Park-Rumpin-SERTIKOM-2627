<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-secondary">Pengelolaan promo</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-brand-forest sm:text-4xl">Kode redeem</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl space-y-8 px-5 py-8 sm:px-8 sm:py-10 lg:px-12">
        @if (session('success'))
            <p class="border-l-4 border-brand-leaf bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">{{ session('error') }}</p>
        @endif
        @if ($errors->any())
            <div class="border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">
                <p class="font-semibold">Kode belum dapat disimpan.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-8 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-start">
            <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="space-y-5 border border-brand-border bg-brand-surface p-5 sm:p-8">
                @csrf
                <div>
                    <h2 class="text-2xl font-semibold text-brand-forest">Buat kode baru</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-secondary">Kode berlaku untuk tiket umum dan paket sekolah. Kode menggantikan diskon produk dan hanya dapat berhasil dipakai satu kali total.</p>
                </div>

                <div>
                    <label for="code" class="block text-sm font-semibold text-brand-charcoal">Kode redeem</label>
                    <input id="code" name="code" type="text" value="{{ old('code') }}" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9-]*" autocomplete="off" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface uppercase text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                    <p class="mt-2 text-sm text-brand-secondary">Gunakan huruf, angka, dan tanda hubung. Sistem menyimpan kode dalam huruf kapital.</p>
                    @error('code') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <div>
                        <label for="discount_type" class="block text-sm font-semibold text-brand-charcoal">Jenis potongan</label>
                        <select id="discount_type" name="discount_type" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                            <option value="percentage" @selected(old('discount_type') === 'percentage')>Persentase (%)</option>
                            <option value="fixed" @selected(old('discount_type') === 'fixed')>Nominal (Rp)</option>
                        </select>
                        @error('discount_type') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="discount_value" class="block text-sm font-semibold text-brand-charcoal">Nilai potongan</label>
                        <input id="discount_value" name="discount_value" type="number" value="{{ old('discount_value') }}" min="1" max="9999999999" step="1" inputmode="numeric" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                        <p class="mt-2 text-sm text-brand-secondary">Persentase harus 1 sampai 99. Potongan nominal harus lebih kecil dari harga produk saat ditukar.</p>
                        @error('discount_value') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-brand-border pt-4">
                    <input type="hidden" name="is_active" value="0">
                    <label for="is_active" class="flex min-h-11 items-center gap-3 text-sm font-semibold text-brand-charcoal">
                        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="size-5 rounded-sm border-brand-secondary text-brand-forest focus:ring-2 focus:ring-brand-forest">
                        Aktifkan kode setelah dibuat
                    </label>
                    @error('is_active') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest sm:w-auto">Simpan kode</button>
            </form>

            <section aria-labelledby="promo-codes-heading">
                <div class="mb-4">
                    <h2 id="promo-codes-heading" class="text-2xl font-semibold text-brand-forest">Kode yang sudah dibuat</h2>
                    <p class="mt-2 text-sm leading-6 text-brand-secondary">Pembayaran pending mengunci kode. Pembayaran gagal atau kedaluwarsa melepaskan kunci, sedangkan pembayaran berhasil menghabiskan kode.</p>
                </div>

                @if ($promoCodes->isEmpty())
                    <div class="border border-brand-border bg-brand-surface px-5 py-8" role="status">
                        <h3 class="font-semibold text-brand-forest">Belum ada kode redeem.</h3>
                        <p class="mt-2 text-sm leading-6 text-brand-secondary">Buat kode pada formulir ini untuk mulai memberi diskon kepada pelanggan.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($promoCodes as $promoCode)
                            @php
                                $status = $promoCode->redeemed_at
                                    ? 'Sudah digunakan'
                                    : ($promoCode->reserved_payment_id
                                        ? 'Menunggu pembayaran'
                                        : ($promoCode->is_active ? 'Tersedia' : 'Nonaktif'));
                            @endphp
                            <article class="border border-brand-border bg-brand-surface p-5 sm:p-6">
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="break-all font-mono text-lg font-semibold text-brand-forest">{{ $promoCode->code }}</h3>
                                        <p class="mt-2 font-semibold text-brand-charcoal">
                                            {{ $promoCode->discount_type === 'percentage'
                                                ? number_format((float) $promoCode->discount_value, 0, ',', '.').'%'
                                                : 'Rp '.number_format((float) $promoCode->discount_value, 0, ',', '.') }}
                                        </p>
                                        <p class="mt-2 text-sm text-brand-secondary">{{ $status }}</p>
                                        @if ($promoCode->redeemed_at)
                                            <p class="mt-1 text-sm text-brand-secondary">Dipakai {{ $promoCode->redeemed_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    </div>

                                    @if (! $promoCode->redeemed_at)
                                        <div class="flex flex-col gap-2 sm:w-40">
                                            <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $promoCode->is_active ? 0 : 1 }}">
                                                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-cream active:bg-brand-border focus:outline-none focus:ring-2 focus:ring-brand-forest">
                                                    {{ $promoCode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            @if (! $promoCode->reserved_payment_id)
                                                <form method="POST" action="{{ route('admin.promo-codes.destroy', $promoCode) }}" onsubmit="return confirm('Hapus kode redeem ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-brand-border px-4 text-sm font-semibold text-brand-charcoal hover:bg-brand-cream active:bg-brand-border focus:outline-none focus:ring-2 focus:ring-brand-forest">Hapus kode</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-6">{{ $promoCodes->links() }}</div>
                @endif
            </section>
        </section>
    </main>
</x-app-layout>
