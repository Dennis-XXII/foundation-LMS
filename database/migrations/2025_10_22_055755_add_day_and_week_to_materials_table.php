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
        Schema::table('materials', function (Blueprint $table) {
            // Using ENUM as it's efficient and enforces the specific day values.
            $table->enum('day', [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Review'
            ])->nullable()->after('level'); // Placing it after 'level' for logical grouping

            // unsignedTinyInteger is perfect for a small range like 1-8.
            $table->unsignedTinyInteger('week')->nullable()->after('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['day', 'week']);
        });
    }
};
