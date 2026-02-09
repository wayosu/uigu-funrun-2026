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
        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->integer('quota');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();

            $table->enum('registration_type', ['individual', 'collective']);
            $table->integer('collective_size')->nullable();

            $table->string('prefix');

            $table->integer('bib_start')->nullable();
            $table->integer('bib_end')->nullable();
            $table->integer('bib_current')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_categories');
    }
};
