<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->restrictOnDelete();

            $table->string('registration_code', 50)->unique();
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->string('status', 50)->default('pending');
            $table->timestamp('registered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
