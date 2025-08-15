<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use App\Models\Course;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::firstOrFail();

        // Open to all levels (NULL) — published
        Material::create([
            'course_id'    => $course->id,
            'is_published' => true,
            'type'         => 'lesson',
            'level'        => null, // visible to everyone
            'title'        => 'Welcome Pack',
            'descriptions' => 'Overview, syllabus, and orientation.',
            'uploaded_at'  => now(),
        ]);

        // Level 1
        Material::create([
            'course_id'    => $course->id,
            'is_published' => true,
            'type'         => 'lesson',
            'level'        => 1,
            'title'        => 'Basics 1',
            'descriptions' => 'Foundational concepts for beginners.',
            'uploaded_at'  => now()->subDay(),
        ]);

        // Level 2 (draft)
        Material::create([
            'course_id'    => $course->id,
            'is_published' => false,
            'type'         => 'worksheet',
            'level'        => 2,
            'title'        => 'Practice Set 2',
            'descriptions' => 'Exercises for intermediate learners.',
        ]);

        // Level 3 (published)
        Material::create([
            'course_id'    => $course->id,
            'is_published' => true,
            'type'         => 'self_study',
            'level'        => 3,
            'title'        => 'Advanced Reading',
            'descriptions' => 'Deep dive for advanced students.',
            'uploaded_at'  => now(),
        ]);
    }
}

