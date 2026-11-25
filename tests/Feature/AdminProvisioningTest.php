<?php

use App\Models\User;

it('promotes an existing account to admin through the console', function () {
    $user = User::factory()->create();

    $this->artisan('app:make-admin', ['email' => strtoupper($user->email)])
        ->expectsOutputToContain("Akses admin diberikan kepada {$user->email}.")
        ->assertSuccessful();

    expect($user->fresh()->role)->toBe('admin');
});

it('does not promote a non-existent account', function () {
    $this->artisan('app:make-admin', ['email' => 'missing@example.test'])
        ->expectsOutputToContain('Akun tidak ditemukan.')
        ->assertFailed();

    $this->assertDatabaseCount('users', 0);
});
