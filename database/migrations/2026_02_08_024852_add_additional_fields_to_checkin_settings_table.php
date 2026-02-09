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
        Schema::table('checkin_settings', function (Blueprint $table) {
            $table->dateTime('checkin_start_time')->nullable()->after('is_active');
            $table->dateTime('checkin_end_time')->nullable()->after('checkin_start_time');
            $table->string('check_in_location')->nullable()->after('checkin_end_time');
            $table->text('instructions')->nullable()->after('check_in_location');
            $table->boolean('allow_duplicate_scan')->default(false)->after('instructions');
            $table->boolean('require_photo_verification')->default(false)->after('allow_duplicate_scan');
            $table->boolean('auto_print_bib')->default(false)->after('require_photo_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkin_settings', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_start_time',
                'checkin_end_time',
                'check_in_location',
                'instructions',
                'allow_duplicate_scan',
                'require_photo_verification',
                'auto_print_bib',
            ]);
        });
    }
};
