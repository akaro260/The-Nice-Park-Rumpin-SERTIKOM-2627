<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-secondary">Pengelolaan kunjungan</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-brand-forest sm:text-4xl">Pengajuan paket sekolah</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10 lg:px-12">
        @if (session('success'))
            <p class="mb-5 border-l-4 border-brand-leaf bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <div class="mb-5 border-l-4 border-brand-forest bg-brand-surface px-5 py-4 text-sm text-brand-charcoal" role="alert">
                <p class="font-semibold">Pengajuan belum dapat diproses.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <a href="{{ route('admin.products.index') }}" class="inline-flex min-h-11 items-center rounded-sm text-sm font-semibold text-brand-forest underline decoration-brand-sunshine decoration-2 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-brand-forest">Kembali ke produk</a>
        </div>

        @if ($applications->isEmpty())
            <section class="border border-brand-border bg-brand-surface px-6 py-10 sm:px-8" aria-labelledby="empty-applications">
                <h2 id="empty-applications" class="text-xl font-semibold text-brand-forest">Belum ada pengajuan sekolah.</h2>
                <p class="mt-3 max-w-xl leading-7 text-brand-secondary">Pengajuan yang masuk akan ditampilkan di sini untuk ditinjau.</p>
            </section>
        @else
            <section class="space-y-4" aria-label="Daftar pengajuan sekolah">
                @foreach ($applications as $application)
                    <article class="border border-brand-border bg-brand-surface p-5 sm:p-6">
                        <div class="grid gap-6 lg:grid-cols-[1fr_auto]">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-secondary">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</p>
                                <h2 class="mt-2 text-xl font-semibold text-brand-forest">{{ $application->school_name }}</h2>
                                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                    <div><dt class="text-brand-secondary">Paket</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $application->ticket->name }}</dd></div>
                                    <div><dt class="text-brand-secondary">Penanggung jawab</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $application->contact_person }}</dd></div>
                                    <div><dt class="text-brand-secondary">Tanggal kunjungan</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ $application->visit_date->format('d/m/Y') }}</dd></div>
                                    <div><dt class="text-brand-secondary">Jumlah peserta</dt><dd class="mt-1 font-semibold text-brand-charcoal">{{ number_format($application->quantity, 0, ',', '.') }}</dd></div>
                                    <div><dt class="text-brand-secondary">Pemohon</dt><dd class="mt-1 break-all font-semibold text-brand-charcoal">{{ $application->user->name }} · {{ $application->user->email }}</dd></div>
                                    <div><dt class="text-brand-secondary">Harga saat pengajuan</dt><dd class="mt-1 inline-block bg-brand-sunshine px-2 py-1 font-semibold text-brand-forest">Rp {{ number_format((float) $application->total_price, 0, ',', '.') }}</dd></div>
                                </dl>
                                @if ($application->rejection_reason)
                                    <p class="mt-4 border-l-2 border-brand-forest pl-3 text-sm leading-6 text-brand-charcoal">Alasan penolakan: {{ $application->rejection_reason }}</p>
                                @endif
                            </div>

                            @if ($application->status === 'pending_approval')
                                <div class="flex flex-col gap-3 lg:w-64">
                                    <form method="POST" action="{{ route('admin.applications.approve', $application) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-sm border border-brand-forest bg-brand-sunshine px-4 text-sm font-semibold text-brand-forest hover:bg-brand-sunshine/90 focus:outline-none focus:ring-2 focus:ring-brand-forest">Setujui pengajuan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.applications.reject', $application) }}" class="space-y-2 border-t border-brand-border pt-3">
                                        @csrf
                                        @method('PATCH')
                                        <label for="rejection_reason_{{ $application->id }}" class="block text-sm font-semibold text-brand-charcoal">Alasan jika menolak</label>
                                        <textarea id="rejection_reason_{{ $application->id }}" name="rejection_reason" rows="3" maxlength="2000" class="w-full rounded-sm border-brand-secondary bg-brand-surface text-brand-charcoal focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20" required></textarea>
                                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-brand-forest px-4 text-sm font-semibold text-brand-charcoal hover:bg-brand-cream focus:outline-none focus:ring-2 focus:ring-brand-forest">Tolak pengajuan</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="mt-6">{{ $applications->links() }}</div>
        @endif
    </main>
</x-app-layout>
