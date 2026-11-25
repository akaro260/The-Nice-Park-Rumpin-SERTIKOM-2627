<nav x-data="{ open: false }" class="border-b border-brand-border/30 bg-brand-forest text-brand-cream" aria-label="Navigasi akun">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-12">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-sm focus:outline-none focus:ring-2 focus:ring-brand-sunshine">
            <span class="grid size-9 place-items-center border border-brand-sunshine/70 text-brand-sunshine" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="1.8">
                    <path d="M12 21V9" stroke-linecap="round" />
                    <path d="M12 13c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Z" stroke-linejoin="round" />
                    <path d="M12 17c4.5 0 7-2.5 7-7-4.5 0-7 2.5-7 7Z" stroke-linejoin="round" />
                </svg>
            </span>
            <span class="hidden text-sm font-semibold tracking-[0.16em] sm:block">THE NICE PARK RUMPIN</span>
        </a>

        <div class="hidden items-center gap-6 sm:flex">
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('admin.products.index') }}" class="rounded-sm text-sm font-semibold underline decoration-brand-sunshine decoration-2 underline-offset-8">Produk</a>
                <a href="{{ route('admin.promo-codes.index') }}" class="rounded-sm text-sm text-brand-cream/80 hover:text-brand-sunshine">Kode redeem</a>
                <a href="{{ route('admin.applications.index') }}" class="rounded-sm text-sm text-brand-cream/80 hover:text-brand-sunshine">Pengajuan sekolah</a>
            @else
                <a href="{{ route('dashboard') }}" class="rounded-sm text-sm font-semibold underline decoration-brand-sunshine decoration-2 underline-offset-8">Pesanan saya</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="rounded-sm text-sm text-brand-cream/80 hover:text-brand-sunshine">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="min-h-11 rounded-sm border border-brand-cream/40 px-4 text-sm font-semibold hover:border-brand-sunshine hover:text-brand-sunshine">Keluar</button>
            </form>
        </div>

        <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="account-mobile-menu" class="grid size-11 place-items-center rounded-sm border border-brand-cream/40 sm:hidden" aria-label="Buka menu akun">
            <svg x-show="! open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
            </svg>
            <svg x-show="open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
            </svg>
        </button>
    </div>

    <div id="account-mobile-menu" x-show="open" x-cloak class="border-t border-brand-cream/20 px-5 py-4 sm:hidden">
        <div class="flex flex-col gap-1">
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('admin.products.index') }}" class="flex min-h-11 items-center rounded-sm px-2 font-semibold">Produk</a>
                <a href="{{ route('admin.promo-codes.index') }}" class="flex min-h-11 items-center rounded-sm px-2">Kode redeem</a>
                <a href="{{ route('admin.applications.index') }}" class="flex min-h-11 items-center rounded-sm px-2">Pengajuan sekolah</a>
            @else
                <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center rounded-sm px-2 font-semibold">Pesanan saya</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center rounded-sm px-2">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex min-h-11 w-full items-center rounded-sm px-2 text-left">Keluar</button>
            </form>
        </div>
    </div>
</nav>
