<?php

use Illuminate\Support\Facades\DB;

it('shows homepage sections with working in-page destinations', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('href="#tentang"', false)
        ->assertSee('href="#tiket"', false)
        ->assertSee('href="#paket-sekolah"', false);
});

it('shows informative empty states without directing visitors to Instagram', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSeeText('Belum ada tiket umum aktif.')
        ->assertSeeText('Belum ada paket sekolah aktif.')
        ->assertDontSee('instagram.com', false);
});

it('uses Indonesian copy and branded titles on login and registration pages', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('<title>Masuk | The Nice Park Rumpin</title>', false)
        ->assertSeeText('Ingat saya')
        ->assertSeeText('Lupa kata sandi?')
        ->assertSeeText('Kata sandi')
        ->assertDontSeeText('Log in');

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('<title>Daftar | The Nice Park Rumpin</title>', false)
        ->assertSeeText('Nama')
        ->assertSeeText('Konfirmasi kata sandi')
        ->assertDontSeeText('Register');
});

it('shows active products from both catalogs and excludes inactive products', function () {
    DB::table('tickets')->insert([
        [
            'name' => 'Tiket Hari Biasa',
            'type' => 'general',
            'description' => 'Akses kunjungan umum.',
            'price' => 75000,
            'quota' => 100,
            'available_quota' => 80,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Tiket Tidak Aktif',
            'type' => 'general',
            'description' => 'Produk yang tidak tersedia.',
            'price' => 50000,
            'quota' => 50,
            'available_quota' => 0,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => '<img src=x onerror=alert(1)>',
            'type' => 'general',
            'description' => null,
            'price' => 25000,
            'quota' => 20,
            'available_quota' => 20,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Paket Sekolah Rombongan',
            'type' => 'school',
            'description' => 'Harga tetap per pengajuan.',
            'price' => 250000,
            'quota' => 60,
            'available_quota' => 60,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSeeText('Tiket Hari Biasa')
        ->assertSeeText('Rp 75.000')
        ->assertSeeText('Paket Sekolah Rombongan')
        ->assertSeeText('Rp 250.000')
        ->assertDontSeeText('Tiket Tidak Aktif')
        ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});
