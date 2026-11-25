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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('id');
            $table->index(['type', 'is_active']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('ticket_id');
            $table->string('school_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('e_ticket_code', 100)->nullable()->unique();
            $table->timestamp('issued_at')->nullable();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('ticket_date_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $table->date('visit_date');
            $table->unsignedInteger('quota');
            $table->unsignedInteger('available_quota');
            $table->timestamps();
            $table->unique(['ticket_id', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_date_quotas');

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropUnique(['e_ticket_code']);
            $table->dropColumn([
                'visit_date',
                'school_name',
                'contact_person',
                'rejection_reason',
                'e_ticket_code',
                'issued_at',
            ]);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['type', 'is_active']);
            $table->dropColumn('type');
        });
    }
};
