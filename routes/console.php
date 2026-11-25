<?php

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-admin {email}', function (string $email): int {
    $email = strtolower(trim($email));

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Masukkan alamat email yang valid.');

        return Command::FAILURE;
    }

    $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

    if (! $user) {
        $this->error('Akun tidak ditemukan. Daftarkan akun tersebut terlebih dahulu.');

        return Command::FAILURE;
    }

    $user->forceFill(['role' => 'admin'])->save();

    $this->info("Akses admin diberikan kepada {$user->email}.");

    return Command::SUCCESS;
})->purpose('Berikan akses admin ke akun yang sudah terdaftar');
