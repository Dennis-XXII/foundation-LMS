<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /** Anyone who is enrolled (student) or assigned (lecturer) can view */
    public function view(User $user, Course $course): bool
    {
        if ($this->isAdmin($user)) return true;

        if ($user->lecturer) {
            return $course->lecturers()->whereKey($user->lecturer->id)->exists();
        }

        if ($user->student) {
            return $course->students()->whereKey($user->student->id)->exists();
        }

        return false;
    }

    /** Admins or lecturers assigned to the course can update/manage it */
    public function update(User $user, Course $course): bool
    {
        if ($this->isAdmin($user)) return true;

        return $user->lecturer
            && $course->lecturers()->whereKey($user->lecturer->id)->exists();
    }

    /** Optional helpers */
    private function isAdmin(User $user): bool
    {
        // adapt to your schema: e.g. role column = 'admin' or relation ->admin
        return method_exists($user, 'isAdmin')
            ? $user->isAdmin()
            : (bool) $user->admin; // if you have admin relation
    }
}



