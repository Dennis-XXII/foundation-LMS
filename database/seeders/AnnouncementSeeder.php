<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::firstOrFail();
        $poster = User::whereIn('role', ['lecturer','admin'])->inRandomOrder()->firstOrFail();

        // Global announcement
        Announcement::create([
            'user_id'     => $poster->id,
            'title'       => 'Welcome to the LMS!',
            'description' => 'Check materials and announcements regularly.',
            'is_global'   => true,
            'posted_at'   => now(),
        ]);

        // Course-specific announcement
        $announcement = Announcement::create([
            'user_id'     => $poster->id,
            'title'       => 'Course Update',
            'description' => 'New materials have been added.',
            'is_global'   => false,
            'posted_at'   => now(),
        ]);
        $announcement->courses()->attach($course->id);
    }
}

