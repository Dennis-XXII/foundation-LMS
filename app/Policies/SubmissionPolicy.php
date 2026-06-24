<?php

namespace App\Policies;

use App\Models\{User, Submission, SpecialProject};
use App\Policies\Concerns\AuthorizesLms;
use Illuminate\Support\Carbon;

class SubmissionPolicy
{
    use AuthorizesLms;

    public function view(User $user, Submission $submission): bool
    {
        if ($this->isAdmin($user)) return true;

        $specialProjectCourse = $submission->specialProject->course;

        if ($this->isLecturer($user)) {
            // Lecturers of the course can view all submissions
            return $this->lecturerTeachesCourse($user, $specialProjectCourse);
        }

        if ($this->isStudent($user) && $user->student) {
            // Student can view only their own submission
            return $submission->student_id === $user->student->id;
        }

        return false;
    }

    /** Permission to create a submission (i.e., submit work). */
    public function create(User $user, SpecialProject $specialProject): bool
    {
        if (! $this->isStudent($user) || ! $user->student) return false;

        $course = $specialProject->course;

        // Must be enrolled
        $level = $this->studentLevelForCourse($user, $course);
        if ($level === null) return false;

        // Special project must be published and within level gate
        if (! $specialProject->is_published) return false;
        if (! is_null($specialProject->level) && $specialProject->level > $level) return false;

        // Optional: due date check
        if ($specialProject->due_at && Carbon::now()->greaterThan($specialProject->due_at)) {
            return false;
        }

        return true;
    }

    public function update(User $user, Submission $submission): bool
    {
        // Admin can always update
        if ($this->isAdmin($user)) return true;

        // Student can update their own submission before the due date
        if ($this->isStudent($user) && $user->student && $submission->student_id === $user->student->id) {
            $due = $submission->specialProject->due_at;
            return is_null($due) || now()->lessThanOrEqualTo($due);
        }
        // Lecturer can "update" (in the context of grading) if they teach the course
        if ($this->isLecturer($user)) {
            $submission->loadMissing('specialProject.course'); // Make sure relationship is loaded
            if ($submission->specialProject?->course) {
                return $this->lecturerTeachesCourse($user, $submission->specialProject->course);
            }
        }
        return false;
    }

    public function delete(User $user, Submission $submission): bool
    {
        if ($this->isAdmin($user)) return true;
        // Students can delete their submission before due date
        if ($this->isStudent($user) && $user->student && $submission->student_id === $user->student->id) {
            $due = $submission->specialProject->due_at;
            return is_null($due) || now()->lessThanOrEqualTo($due);
        }
        return false;
    }
}
