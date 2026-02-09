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
        Schema::create('jersey_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name: "Extra Small", "Small", etc
            $table->string('code')->unique(); // Unique code: "xs", "s", "m", etc
            $table->foreignId('race_category_id')->nullable()->constrained()->cascadeOnDelete(); // Optional: specific to race category
            $table->integer('sort_order')->default(0); // For ordering
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['race_category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jersey_sizes');
    }
};
