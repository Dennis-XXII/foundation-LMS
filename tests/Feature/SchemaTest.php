<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Student,Admin,Lecturer,Course,Enrollment,Material,SpecialProject,Announcement,UsefulLink};

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

    public function test_student_login_page_renders()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Student Login');
    }

    public function test_lecturer_login_page_renders()
    {
        $response = $this->get(route('login.lecturer'));
        $response->assertStatus(200);
        $response->assertSee('Lecturer & Admin Login', false);
    }

    public function test_login_student_only_allows_student()
    {
        $studentUser = User::where('role', 'student')->firstOrFail();
        $studentProfile = Student::where('user_id', $studentUser->id)->firstOrFail();

        // 1. Try student ID
        $response = $this->post(route('login.student.post'), [
            'login_identifier' => $studentProfile->student_id,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($studentUser);

        \Illuminate\Support\Facades\Auth::logout();

        // 2. Try student email
        $response = $this->post(route('login.student.post'), [
            'login_identifier' => $studentUser->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($studentUser);

        \Illuminate\Support\Facades\Auth::logout();

        // 3. Try lecturer credentials on student route
        $lecturerUser = User::where('role', 'lecturer')->firstOrFail();
        $response = $this->post(route('login.student.post'), [
            'login_identifier' => $lecturerUser->email,
            'password' => ';9zZjEI&1Gn3',
        ]);
        $response->assertSessionHasErrors('login_identifier');
        $this->assertGuest();
    }

    public function test_login_lecturer_only_allows_staff()
    {
        $lecturerUser = User::where('role', 'lecturer')->firstOrFail();

        // 1. Try lecturer credentials
        $response = $this->post(route('login.lecturer.post'), [
            'login_identifier' => $lecturerUser->email,
            'password' => ';9zZjEI&1Gn3',
        ]);
        $response->assertRedirect(route('lecturer.dashboard'));
        $this->assertAuthenticatedAs($lecturerUser);

        \Illuminate\Support\Facades\Auth::logout();

        // 2. Try student credentials on lecturer route
        $studentUser = User::where('role', 'student')->firstOrFail();
        $response = $this->post(route('login.lecturer.post'), [
            'login_identifier' => $studentUser->email,
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('login_identifier');
        $this->assertGuest();
    }

    public function test_lecturer_can_manage_useful_links()
    {
        $lecturerUser = User::where('role', 'lecturer')->firstOrFail();
        $course = Course::firstOrFail();

        // 1. Visit index page
        $response = $this->actingAs($lecturerUser)
            ->get(route('lecturer.courses.useful_links.index', $course));
        $response->assertStatus(200);

        // 2. Create link
        $response = $this->actingAs($lecturerUser)
            ->post(route('lecturer.courses.useful_links.store', $course), [
                'title' => 'Laravel Docs',
                'description' => 'Documentation link',
                'link' => 'https://laravel.com/docs',
            ]);
        $response->assertRedirect(route('lecturer.courses.useful_links.index', $course));
        $this->assertTrue(UsefulLink::where('title', 'Laravel Docs')->exists());

        $link = UsefulLink::where('title', 'Laravel Docs')->firstOrFail();

        // 3. Edit link
        $response = $this->actingAs($lecturerUser)
            ->get(route('lecturer.useful_links.edit', $link));
        $response->assertStatus(200);

        // 4. Update link
        $response = $this->actingAs($lecturerUser)
            ->put(route('lecturer.useful_links.update', $link), [
                'title' => 'Updated Docs',
                'description' => 'Updated description',
                'link' => 'https://laravel.com/docs/11.x',
            ]);
        $response->assertRedirect(route('lecturer.courses.useful_links.index', $course));
        $this->assertTrue(UsefulLink::where('title', 'Updated Docs')->exists());

        // 5. Delete link
        $response = $this->actingAs($lecturerUser)
            ->delete(route('lecturer.useful_links.destroy', $link));
        $response->assertRedirect(route('lecturer.courses.useful_links.index', $course));
        $this->assertSoftDeleted('useful_links', ['id' => $link->id]);
    }

    public function test_student_can_view_useful_links()
    {
        $studentUser = User::where('role', 'student')->firstOrFail();
        $student = Student::where('user_id', $studentUser->id)->firstOrFail();
        $course = Course::firstOrFail();

        // Ensure student is enrolled
        Enrollment::updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            ['level' => 1, 'status' => 'active']
        );

        // Create a test link
        $link = UsefulLink::create([
            'course_id' => $course->id,
            'title' => 'Google Search',
            'description' => 'Search engine',
            'link' => 'https://google.com',
        ]);

        // 1. Visit index page as Student
        $response = $this->actingAs($studentUser)
            ->get(route('student.courses.useful_links.index', $course));
        $response->assertStatus(200);
        $response->assertSee('Google Search');

        // 2. Student cannot manage useful links (should redirect or be unauthorized)
        $response = $this->actingAs($studentUser)
            ->post(route('lecturer.courses.useful_links.store', $course), [
                'title' => 'Student Link',
                'link' => 'https://student.com',
            ]);
        $response->assertStatus(403); // Forbidden
    }
}
