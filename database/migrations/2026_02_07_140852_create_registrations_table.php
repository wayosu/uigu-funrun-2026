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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->string('registration_number')->unique();
            $table->foreignId('race_category_id')->constrained()->cascadeOnDelete();

            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_phone');

            $table->enum('status', [
                'pending_payment',
                'waiting_verification',
                'paid',
                'cancelled',
                'expired',
            ])->default('pending_payment');

            $table->decimal('total_amount', 12, 2);
            $table->string('snap_token')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
