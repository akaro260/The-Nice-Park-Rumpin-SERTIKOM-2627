<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('payment_gateway', 50)->default('midtrans');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('order_id')->unique();
            $table->string('payment_type', 100)->nullable();

            $table->decimal('gross_amount', 12, 2);

            $table->string('transaction_status', 50)
                ->default('pending');

            $table->string('fraud_status', 50)->nullable();

            $table->string('snap_token')->nullable();
            $table->text('snap_url')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
