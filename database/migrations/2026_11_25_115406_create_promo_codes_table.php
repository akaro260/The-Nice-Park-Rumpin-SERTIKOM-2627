<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('reserved_payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();
            $table->foreignId('redeemed_payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();
            $table->foreignId('redeemed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->nullOnDelete();
            $table->string('promo_code', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn('promo_code');
        });

        Schema::dropIfExists('promo_codes');
    }
};
