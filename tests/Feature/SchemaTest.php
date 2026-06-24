<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Student,Admin,Lecturer,Course,Enrollment,Material,SpecialProject,Announcement};

class LmsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);

        // Seed students specifically for tests since they were removed from the general UserSeeder
        User::factory()
            ->count(10)
            ->create(['role' => 'student'])
            ->each(function (User $user, $i) {
                Student::create([
                    'user_id'    => $user->id,
                    'student_id' => '8' . str_pad($i + 1, 6, '0', STR_PAD_LEFT)
                ]);
            });

        // Enroll students since EnrollmentSeeder ran when there were no students
        $course = Course::firstOrFail();
        Student::all()->each(function ($student) use ($course) {
            Enrollment::updateOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                [
                    'level'  => 1,
                    'status' => 'active',
                ]
            );
        });
    }

    public function test_core_tables_seeded()
    {
        $this->assertTrue(User::count() >= 12);
        $this->assertEquals(1, Course::count());
        $this->assertEquals(10, Student::count());
        $this->assertEquals(10, Enrollment::count());
    }

    public function test_level_gating_and_publish()
    {
        $enrollment = Enrollment::first();
        $level = (int) $enrollment->level;

        $materials = Material::where('course_id', $enrollment->course_id)
            ->where('is_published', true)
            ->where(fn($w) => $w->whereNull('level')->orWhere('level','<=',$level))
            ->get();

        $this->assertNotNull($materials);
        foreach ($materials as $m) {
            $this->assertTrue($m->is_published);
            $this->assertTrue(is_null($m->level) || $m->level <= $level);
        }
    }

    public function test_announcements_pivot_links()
    {
        $a = Announcement::where('is_global', false)->first();
        if ($a) {
            $this->assertTrue($a->courses()->exists());
        } else {
            $this->assertTrue(true); // no course-specific ann, skip
        }
    }

    public function test_lecturer_can_remove_student()
    {
        $lecturerUser = User::where('role', 'lecturer')->firstOrFail();
        $course = Course::firstOrFail();
        $student = Student::firstOrFail();
        $studentUser = $student->user;

        $this->assertTrue(Enrollment::where('course_id', $course->id)->where('student_id', $student->id)->exists());

        $response = $this->actingAs($lecturerUser)
            ->delete(route('lecturer.courses.students.destroy', [$course, $studentUser->id]));

        $response->assertStatus(302); // Redirect back
        $this->assertFalse(Enrollment::where('course_id', $course->id)->where('student_id', $student->id)->exists());
    }

    public function test_admin_registration_requires_correct_security_key()
    {
        // 1. Try registering an admin with incorrect security key
        $response = $this->post(route('register.admin'), [
            'security_password' => 'wrong_key',
            'name' => 'New Admin',
            'nickname' => 'newadmin',
            'email' => 'newadmin@rsu.ac.th',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('security_password');
        $this->assertFalse(User::where('email', 'newadmin@rsu.ac.th')->exists());

        // 2. Try registering with correct security key
        $response = $this->post(route('register.admin'), [
            'security_password' => 'X98Mbldqo9]F', // Seeded admin password
            'name' => 'New Admin',
            'nickname' => 'newadmin',
            'email' => 'newadmin@rsu.ac.th',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(User::where('email', 'newadmin@rsu.ac.th')->exists());
    }

    public function test_lecturer_registration_requires_correct_security_key()
    {
        // 1. Try registering a lecturer with incorrect security key
        $response = $this->post(route('register.lecturer'), [
            'security_password' => 'wrong_key',
            'name' => 'New Lecturer',
            'nickname' => 'newlect',
            'email' => 'newlect@rsu.ac.th',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('security_password');
        $this->assertFalse(User::where('email', 'newlect@rsu.ac.th')->exists());

        // 2. Try registering with correct security key
        $response = $this->post(route('register.lecturer'), [
            'security_password' => ';9zZjEI&1Gn3', // Seeded lecturer password
            'name' => 'New Lecturer',
            'nickname' => 'newlect',
            'email' => 'newlect@rsu.ac.th',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(User::where('email', 'newlect@rsu.ac.th')->exists());
    }
}
