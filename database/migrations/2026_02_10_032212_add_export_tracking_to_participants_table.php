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
        Schema::table('participants', function (Blueprint $table) {
            $table->timestamp('last_exported_at')->nullable()->after('updated_at');
            $table->foreignId('last_exported_by')->nullable()->after('last_exported_at')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('export_count')->default(0)->after('last_exported_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['last_exported_by']);
            $table->dropColumn(['last_exported_at', 'last_exported_by', 'export_count']);
        });
    }
};
