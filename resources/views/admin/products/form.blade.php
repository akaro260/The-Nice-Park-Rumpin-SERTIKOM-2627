<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-3xl px-5 py-8 sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-secondary">Pengelolaan katalog</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-brand-forest sm:text-4xl">{{ $product->exists ? 'Ubah produk' : 'Tambah produk' }}</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-3xl px-5 py-8 sm:px-8 sm:py-10">
        @if ($errors->any())
            <div class="mb-6 border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">
                <p class="font-semibold">Periksa kembali data produk.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($product->type === null && $product->exists)
            <p class="mb-5 border-l-4 border-brand-forest bg-brand-sunshine px-5 py-4 text-sm leading-6 text-brand-charcoal" role="status">Produk lama belum memiliki jenis. Pilih jenisnya sebelum produk muncul di katalog publik.</p>
        @endif

        <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="space-y-5 border border-brand-border bg-brand-surface p-5 sm:p-8">
            @csrf
            @if ($product->exists)
                @method('PATCH')
            @endif

            <div>
                <label for="type" class="block text-sm font-semibold text-brand-charcoal">Jenis produk</label>
                <select id="type" name="type" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                    <option value="">Pilih jenis produk</option>
                    <option value="general" @selected(old('type', $product->type) === 'general')>Tiket umum</option>
                    <option value="school" @selected(old('type', $product->type) === 'school')>Paket sekolah</option>
                </select>
                @error('type') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-semibold text-brand-charcoal">Nama produk</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" maxlength="255" required class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                @error('name') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-brand-charcoal">Deskripsi</label>
                <textarea id="description" name="description" rows="5" maxlength="10000" class="mt-2 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="price" class="block text-sm font-semibold text-brand-charcoal">Harga dalam rupiah</label>
                    <input id="price" name="price" type="number" value="{{ old('price', $product->price) }}" min="1" max="9999999999" step="1" required inputmode="numeric" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                    <p class="mt-2 text-sm text-brand-secondary">Harga tiket umum berlaku per tiket. Harga paket sekolah berlaku per pengajuan.</p>
                    @error('price') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="quota" class="block text-sm font-semibold text-brand-charcoal">Kuota per tanggal</label>
                    <input id="quota" name="quota" type="number" value="{{ old('quota', $product->quota) }}" min="1" max="2147483647" step="1" required inputmode="numeric" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                    @error('quota') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <section class="space-y-5 border-t border-brand-border pt-5" aria-labelledby="discount-heading">
                <div>
                    <h2 id="discount-heading" class="text-xl font-semibold text-brand-forest">Diskon</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-secondary">Persentase berupa bilangan bulat 1 sampai 99. Nominal Rupiah berlaku per tiket atau sekali untuk satu pengajuan sekolah. Tanggal mulai dan akhir termasuk dalam periode promo.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="discount_type" class="block text-sm font-semibold text-brand-charcoal">Jenis diskon</label>
                        <select id="discount_type" name="discount_type" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                            <option value="">Tidak ada diskon</option>
                            <option value="percentage" @selected(old('discount_type', $product->discount_type) === 'percentage')>Persentase (%)</option>
                            <option value="fixed" @selected(old('discount_type', $product->discount_type) === 'fixed')>Nominal tetap (Rp)</option>
                        </select>
                        @error('discount_type') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="discount_value" class="block text-sm font-semibold text-brand-charcoal">Nilai diskon</label>
                        <input id="discount_value" name="discount_value" type="number" value="{{ old('discount_value', $product->discount_value) }}" min="1" max="9999999999" step="1" inputmode="numeric" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                        @error('discount_value') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="discount_starts_on" class="block text-sm font-semibold text-brand-charcoal">Tanggal mulai</label>
                        <input id="discount_starts_on" name="discount_starts_on" type="date" value="{{ old('discount_starts_on', $product->discount_starts_on?->format('Y-m-d')) }}" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                        @error('discount_starts_on') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="discount_ends_on" class="block text-sm font-semibold text-brand-charcoal">Tanggal berakhir</label>
                        <input id="discount_ends_on" name="discount_ends_on" type="date" value="{{ old('discount_ends_on', $product->discount_ends_on?->format('Y-m-d')) }}" class="mt-2 min-h-12 w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20">
                        @error('discount_ends_on') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-brand-border pt-4">
                    <input type="hidden" name="discount_is_active" value="0">
                    <label for="discount_is_active" class="flex min-h-11 items-center gap-3 text-sm font-semibold text-brand-charcoal">
                        <input id="discount_is_active" name="discount_is_active" type="checkbox" value="1" @checked(old('discount_is_active', $product->exists ? $product->discount_is_active : false)) class="size-5 rounded-sm border-brand-secondary text-brand-forest focus:ring-2 focus:ring-brand-forest">
                        Aktifkan diskon dalam periode yang dipilih
                    </label>
                    @error('discount_is_active') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="border-t border-brand-border pt-5">
                <input type="hidden" name="is_active" value="0">
                <label for="is_active" class="flex min-h-11 items-center gap-3 text-sm font-semibold text-brand-charcoal">
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true)) class="size-5 rounded-sm border-brand-secondary text-brand-forest focus:ring-2 focus:ring-brand-forest">
                    Tampilkan produk di katalog publik
                </label>
                @error('is_active') <p class="mt-2 text-sm text-brand-charcoal" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-3 border-t border-brand-border pt-5 sm:flex-row">
                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-5 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 active:bg-brand-sunshine/80 focus:outline-none focus:ring-2 focus:ring-brand-forest">{{ $product->exists ? 'Simpan perubahan' : 'Tambah produk' }}</button>
                <a href="{{ route('admin.products.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-sm border border-brand-forest px-5 text-sm font-semibold text-brand-forest hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Kembali ke katalog</a>
            </div>
        </form>
    </main>
</x-app-layout>
