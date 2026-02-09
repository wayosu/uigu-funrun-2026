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
        // Change enum values to match PaymentStatus enum
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('pending_payment', 'payment_uploaded', 'payment_verified', 'expired', 'cancelled') DEFAULT 'pending_payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('pending_payment', 'waiting_verification', 'paid', 'cancelled', 'expired') DEFAULT 'pending_payment'");
    }
};
