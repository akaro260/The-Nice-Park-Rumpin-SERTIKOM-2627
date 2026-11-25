<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->date('discount_starts_on')->nullable();
            $table->date('discount_ends_on')->nullable();
            $table->boolean('discount_is_active')->default(false);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('subtotal_amount', 12, 2)->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
        });

        DB::table('payments')->update([
            'subtotal_amount' => DB::raw('gross_amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn([
                'subtotal_amount',
                'discount_type',
                'discount_value',
                'discount_amount',
            ]);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_starts_on',
                'discount_ends_on',
                'discount_is_active',
            ]);
        });
    }
};
