<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Course;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::firstOrFail();

        // Open to all (null level) — published
        Assignment::create([
            'course_id'    => $course->id,
            'is_published' => true,
            'level'        => null,
            'title'        => 'Intro Essay',
            'instruction'  => 'Write a 500-word introduction.',
            'due_at'       => now()->addDays(7),
        ]);

        // Level 2 — hidden (draft)
        Assignment::create([
            'course_id'    => $course->id,
            'is_published' => false,
            'level'        => 2,
            'title'        => 'Mid-level Quiz',
            'instruction'  => 'Short quiz for level 2.',
        ]);

        // Level 3 — published
        Assignment::create([
            'course_id'    => $course->id,
            'is_published' => true,
            'level'        => 3,
            'title'        => 'Advanced Project',
            'instruction'  => 'Research and present on an advanced topic.',
            'due_at'       => now()->addDays(14),
        ]);
    }
}

