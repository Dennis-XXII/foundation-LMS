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
        Schema::table('assessments', function (Blueprint $table) {
            // --- Add the CORRECT columns ---

            // Foreign key linking to the submission being assessed
            $table->foreignId('submission_id')
                  ->after('id') // Place it after the ID for clarity
                  ->constrained('submissions') // Assumes your submissions table is named 'submissions'
                  ->cascadeOnDelete();

            // Foreign key linking to the lecturer who assessed it
            $table->foreignId('lecturer_id')
                  ->after('submission_id')
                  ->nullable() // Or remove nullable if a lecturer is always required
                  ->constrained('lecturers') // Assumes your lecturers table is named 'lecturers'
                  ->nullOnDelete(); // Or cascadeOnDelete if preferred

            // Assessment details
            $table->unsignedTinyInteger('score')->nullable(); // Score (e.g., 0-10 or 0-100)
            $table->text('comment')->nullable(); // Lecturer's comment
            $table->string('feedback_file_path')->nullable(); // Path to optional feedback file
            $table->timestamp('assessed_at')->nullable(); // When it was assessed
            $table->foreignId('assessed_by') // Link to the user who assessed (could be lecturer's user_id)
                  ->nullable()
                  ->constrained('users') // Link to the main users table
                  ->nullOnDelete();

            // --- Drop the INCORRECT columns (if they exist from the wrong migration) ---
            // Add more columns here if your incorrect migration had others
            $columnsToDrop = ['user_id', 'title', 'description', 'file_path', 'is_global', 'posted_at'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('assessments', $column)) {
                    // Need to drop foreign key first if it exists (like for user_id)
                    if ($column === 'user_id') {
                         try {
                            // The foreign key constraint name might vary, check your DB schema
                            // Common pattern: {table}_{column}_foreign
                            $table->dropForeign(['user_id']);
                         } catch (\Exception $e) {
                            // Ignore if dropping fails (might not exist or different name)
                         }
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
             // Drop the correct columns added in up()
            $table->dropForeign(['submission_id']);
            $table->dropForeign(['lecturer_id']);
            $table->dropForeign(['assessed_by']);
            $table->dropColumn(['submission_id', 'lecturer_id', 'score', 'comment', 'feedback_file_path', 'assessed_at', 'assessed_by']);

            // Optionally, re-add the incorrect columns if you need a perfect rollback
            // Be cautious with this part if data integrity is crucial during rollback
            // $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // $table->string('title');
            // ... etc ...
        });
    }
};