<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Student,Admin,Lecturer,Course,Enrollment,Material,Assignment,Announcement};

class LmsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);
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
}
