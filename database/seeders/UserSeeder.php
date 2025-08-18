<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Lecturer;
use App\Models\Student;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (fixed credentials for login)
        $admin = User::create([
            'name' => 'Admin User',
            'nickname' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'line_id' => 'admin_line_id',
        ]);
        Admin::create(['user_id' => $admin->id]);

        // Lecturer (fixed credentials)
        $lecturerUser = User::create([
            'name' => 'Lecturer User',
            'nickname' => 'lect1',
            'email' => 'lecturer@test.com',
            'password' => bcrypt('password'),
            'role' => 'lecturer',
            'line_id' => 'lecturer_line_id',
        ]);
        Lecturer::create(['user_id' => $lecturerUser->id]);

        // Students (faker)
        User::factory()
            ->count(10)
            ->create(['role' => 'student'])
            ->each(function (User $user, $i) {
                Student::create([
                    'user_id'    => $user->id,
                    'student_id' => '8' . str_pad($i + 1, 6, '0', STR_PAD_LEFT)
                ]);
            });
    
    }
}

