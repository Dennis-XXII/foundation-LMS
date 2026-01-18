<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Lecturer;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (fixed credentials for login)
        $admin = User::create([
            'name' => 'Admin User',
            'nickname' => 'admin',
            'email' => 'fpadmin@2026',
            'password' => bcrypt('X98Mbldqo9]F'),
            'role' => 'admin',
            'line_id' => 'admin_line_id',
        ]);
        Admin::create(['user_id' => $admin->id]);

        // Lecturer (fixed credentials)
        $lecturerUser = User::create([
            'name' => 'Lecturer User',
            'nickname' => 'lect1',
            'email' => 'fplecturer@2026',
            'password' => bcrypt(';9zZjEI&1Gn3'),
            'role' => 'lecturer',
            'line_id' => 'lecturer_line_id',
        ]);
        Lecturer::create(['user_id' => $lecturerUser->id]);
    
    }
}

