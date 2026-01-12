<?php

namespace App\Policies;

use App\Models\{User, Assessment};
use App\Policies\Concerns\AuthorizesLms;

class AssessmentPolicy
{
    use AuthorizesLms;

    public function view(User $user, Assessment $assessment): bool
    {
        if ($this->isAdmin($user)) return true;

        // Lecturer who graded OR any lecturer who teaches the course may view
        $assignmentCourse = $assessment->submission->assignment->course;

        if ($this->isLecturer($user)) {
            if ($assessment->lecturer && $assessment->lecturer->user_id === $user->id) {
                return true;
            }
            return $this->lecturerTeachesCourse($user, $assignmentCourse);
        }

        // Student: only if it’s their own assessment
        if ($this->isStudent($user) && $user->student) {
            return $assessment->submission->student_id === $user->student->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isLecturer($user);
    }

    public function update(User $user, Assessment $assessment): bool
    {
        if ($this->isAdmin($user)) return true;

        // Only the grading lecturer (or lecturer of the course) can update
        $assignmentCourse = $assessment->submission->assignment->course;
        if ($assessment->lecturer && $assessment->lecturer->user_id === $user->id) {
            return true;
        }
        return $this->lecturerTeachesCourse($user, $assignmentCourse);
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        if ($this->isAdmin($user)) return true;
        // Usually not allowed to delete grades; restrict to grading lecturer if you must
        return $assessment->lecturer && $assessment->lecturer->user_id === $user->id;
    }
}
