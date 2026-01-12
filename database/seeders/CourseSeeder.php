<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Lecturer;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::create([
            'code' => 'ENG101',
            'name' => 'Foundation Course',
            'level' => '1',
            'description' => 'The only course in the LMS',
        ]);

        // Attach first lecturer
        $lecturer = Lecturer::first();
        if ($lecturer) {
            $course->lecturers()->attach($lecturer->id);
        }
    }
}
