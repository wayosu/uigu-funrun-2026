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
            // Add new columns
            $table->string('slug')->after('name')->unique();
            $table->string('distance', 50)->nullable()->after('slug');

            // Add registration period columns
            $table->dateTime('registration_open_at')->nullable();
            $table->dateTime('registration_close_at')->nullable();
        });

        // Rename columns in separate statement
        Schema::table('race_categories', function (Blueprint $table) {
            $table->renameColumn('price', 'price_individual');
            $table->renameColumn('prefix', 'registration_prefix');
            $table->renameColumn('bib_start', 'bib_start_number');
            $table->renameColumn('bib_end', 'bib_end_number');
            $table->renameColumn('bib_current', 'bib_current_number');
        });

        // Add new pricing columns after rename
        Schema::table('race_categories', function (Blueprint $table) {
            $table->decimal('price_collective_5', 12, 2)->nullable()->after('price_individual');
            $table->decimal('price_collective_10', 12, 2)->nullable()->after('price_collective_5');
        });

        // Drop old columns
        Schema::table('race_categories', function (Blueprint $table) {
            $table->dropColumn(['registration_type', 'collective_size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_categories', function (Blueprint $table) {
            // Reverse changes
            $table->dropColumn([
                'slug',
                'distance',
                'price_collective_5',
                'price_collective_10',
                'registration_open_at',
                'registration_close_at',
            ]);

            // Rename back
            $table->renameColumn('price_individual', 'price');
            $table->renameColumn('registration_prefix', 'prefix');
            $table->renameColumn('bib_start_number', 'bib_start');
            $table->renameColumn('bib_end_number', 'bib_end');
            $table->renameColumn('bib_current_number', 'bib_current');

            // Add back old columns
            $table->enum('registration_type', ['individual', 'collective']);
            $table->integer('collective_size')->nullable();
        });
    }
};
