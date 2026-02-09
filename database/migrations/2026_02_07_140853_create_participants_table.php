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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');

            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');

            $table->string('jersey_size');
            $table->string('identity_number')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('emergency_name');
            $table->string('emergency_phone');
            $table->string('emergency_relation');

            $table->boolean('is_pic')->default(false);

            $table->string('bib_number')->nullable()->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
