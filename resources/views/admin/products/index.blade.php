<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-secondary">Pengelolaan katalog</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-brand-forest sm:text-4xl">Produk kunjungan</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10 lg:px-12">
        @if (session('success'))
            <p class="mb-5 border-l-4 border-brand-leaf bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-5 border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">{{ session('error') }}</p>
        @endif

        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a href="{{ route('admin.products.create', ['type' => 'general']) }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 focus:outline-none focus:ring-2 focus:ring-brand-forest">Tambah tiket umum</a>
            <a href="{{ route('admin.products.create', ['type' => 'school']) }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Tambah paket sekolah</a>
            <a href="{{ route('admin.applications.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Pengajuan sekolah</a>
        </div>

        @if ($products->isEmpty())
            <section class="border border-brand-border bg-brand-surface px-6 py-10 sm:px-8" aria-labelledby="empty-products">
                <h2 id="empty-products" class="text-xl font-semibold text-brand-forest">Katalog masih kosong.</h2>
                <p class="mt-3 max-w-xl leading-7 text-brand-secondary">Tambahkan tiket umum atau paket sekolah untuk mulai menampilkan pilihan kunjungan kepada pengunjung.</p>
            </section>
        @else
            <section class="space-y-4" aria-label="Daftar produk">
                @foreach ($products as $product)
                    <article class="border border-brand-border bg-brand-surface p-5 sm:p-6">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-secondary">
                                    {{ match ($product->type) {
                                        'general' => 'Tiket umum',
                                        'school' => 'Paket sekolah',
                                        default => 'Jenis belum ditetapkan',
                                    } }}
                                    <span class="mx-1" aria-hidden="true">·</span>
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </p>
                                <h2 class="mt-2 break-words text-xl font-semibold text-brand-forest">{{ $product->name }}</h2>
                                @if ($product->description)
                                    <p class="mt-2 max-w-2xl whitespace-pre-line leading-7 text-brand-secondary">{{ $product->description }}</p>
                                @endif
                                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <dt class="text-brand-secondary">Harga</dt>
                                        <dd class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format((float) $product->price, 0, ',', '.') }}{{ $product->type === 'school' ? ' / pengajuan' : ' / tiket' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-brand-secondary">Diskon</dt>
                                        <dd class="mt-1 font-semibold text-brand-forest">
                                            @if ($product->discount_type === 'percentage')
                                                {{ number_format((float) $product->discount_value, 0, ',', '.') }}%
                                            @elseif ($product->discount_type === 'fixed')
                                                Rp {{ number_format((float) $product->discount_value, 0, ',', '.') }}
                                            @else
                                                Tidak diatur
                                            @endif
                                        </dd>
                                        @if ($product->discount_type)
                                            <p class="mt-1 text-brand-secondary">{{ $product->discount_is_active ? 'Pengaturan aktif' : 'Pengaturan nonaktif' }} · {{ $product->discount_starts_on?->format('d/m/Y') }} - {{ $product->discount_ends_on?->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                    <div>
                                        <dt class="text-brand-secondary">Kuota per tanggal</dt>
                                        <dd class="mt-1 font-semibold text-brand-forest">{{ number_format($product->quota, 0, ',', '.') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-brand-secondary">Pengajuan/pesanan tercatat</dt>
                                        <dd class="mt-1 font-semibold text-brand-forest">{{ number_format($product->registrations_count, 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col">
                                <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex min-h-11 items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Ubah produk</a>
                                @if ($product->registrations_count === 0)
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-charcoal hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Hapus produk</button>
                                    </form>
                                @else
                                    <p class="max-w-48 text-sm leading-6 text-brand-secondary">Produk memiliki riwayat. Nonaktifkan untuk menyembunyikannya dari katalog.</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </main>
</x-app-layout>
