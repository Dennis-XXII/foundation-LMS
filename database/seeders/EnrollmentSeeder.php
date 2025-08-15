<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::firstOrFail();
        $maxLevel = 3; // adjust if you support more levels later

        Student::all()->each(function ($student) use ($course, $maxLevel) {
            Enrollment::updateOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                [
                    'level'  => random_int(1, $maxLevel), // random level 1..3
                    'status' => 'active',
                ]
            );
        });
    }
}

