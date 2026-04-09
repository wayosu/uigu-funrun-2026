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
        if (! Schema::hasColumn('registrations', 'payment_verified_at')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->timestamp('payment_verified_at')->nullable()->after('expired_at');
            });
        }

        if (! Schema::hasColumn('registrations', 'payment_verified_by')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->foreignId('payment_verified_by')
                    ->nullable()
                    ->after('payment_verified_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('registrations', 'payment_verified_by')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('payment_verified_by');
            });
        }

        if (Schema::hasColumn('registrations', 'payment_verified_at')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->dropColumn('payment_verified_at');
            });
        }
    }
};
