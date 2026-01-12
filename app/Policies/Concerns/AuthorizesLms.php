<?php

namespace App\Policies\Concerns;

use App\Models\{User, Course, Enrollment, Lecturer};

trait AuthorizesLms
{
    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    protected function isLecturer(User $user): bool
    {
        return $user->role === 'lecturer';
    }

    protected function isStudent(User $user): bool
    {
        return $user->role === 'student';
    }

    /** Is this lecturer assigned to teach the given course? */
    protected function lecturerTeachesCourse(User $user, Course $course): bool
    {
        if (! $this->isLecturer($user) || ! $user->lecturer) return false;
        return $course->lecturers()->whereKey($user->lecturer->id)->exists();
    }

    /** Student’s numeric level for this course (null if not enrolled). */
    protected function studentLevelForCourse(?User $user, Course $course): ?int
    {
        if (! $user || ! $this->isStudent($user) || ! $user->student) return null;

        return Enrollment::query()
            ->where('student_id', $user->student->id)
            ->where('course_id', $course->id)
            ->value('level'); // null if not enrolled
    }

    /** Is a student enrolled in this course? */
    protected function studentEnrolledInCourse(?User $user, Course $course): bool
    {
        return $this->studentLevelForCourse($user, $course) !== null;
    }
}
