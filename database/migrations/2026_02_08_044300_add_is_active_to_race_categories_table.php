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
        Schema::table('race_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('bib_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
