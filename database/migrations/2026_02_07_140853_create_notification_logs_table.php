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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();

            $table->string('channel'); // whatsapp, email
            $table->string('type'); // registration_success, payment_verified, etc
            $table->string('recipient');
            $table->text('message_body');
            $table->enum('status', ['pending', 'sent', 'failed']);
            $table->json('response_data')->nullable(); // response from fonnte / mailgun

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
