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
        Schema::table('assignments', function (Blueprint $table) {
            // Adding 'day' and 'week' columns, placing them after 'level' for organization.
            $table->enum('day', [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'REVIEW' // Kept 'REVIEW' to match your materials table
            ])->nullable()->after('level');
            
            $table->unsignedTinyInteger('week')->nullable()->after('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // This will safely remove the columns if you need to roll back
            $table->dropColumn(['day', 'week']);
        });
    }
};
