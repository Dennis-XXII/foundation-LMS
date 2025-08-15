<?php

namespace App\Policies;

use App\Models\{User, Submission};
use App\Policies\Concerns\AuthorizesLms;
use Illuminate\Support\Carbon;

class SubmissionPolicy
{
    use AuthorizesLms;

    public function view(User $user, Submission $submission): bool
    {
        if ($this->isAdmin($user)) return true;

        $assignmentCourse = $submission->assignment->course;

        if ($this->isLecturer($user)) {
            // Lecturers of the course can view all submissions
            return $this->lecturerTeachesCourse($user, $assignmentCourse);
        }

        if ($this->isStudent($user) && $user->student) {
            // Student can view only their own submission
            return $submission->student_id === $user->student->id;
        }

        return false;
    }

    /** Permission to create a submission (i.e., submit work). */
    public function create(User $user, \App\Models\Assignment $assignment): bool
    {
        if (! $this->isStudent($user) || ! $user->student) return false;

        $course = $assignment->course;

        // Must be enrolled
        $level = $this->studentLevelForCourse($user, $course);
        if ($level === null) return false;

        // Assignment must be published and within level gate
        if (! $assignment->is_published) return false;
        if (! is_null($assignment->level) && $assignment->level > $level) return false;

        // Optional: due date check (allow admin/lecturer to override via controllers if needed)
        if ($assignment->due_at && Carbon::now()->greaterThan($assignment->due_at)) {
            return false;
        }

        return true;
    }

    public function update(User $user, Submission $submission): bool
    {
        // Let students update their submission until due date; admin always allowed
        if ($this->isAdmin($user)) return true;

        if ($this->isStudent($user) && $user->student && $submission->student_id === $user->student->id) {
            $due = $submission->assignment->due_at;
            return is_null($due) || now()->lessThanOrEqualTo($due);
        }

        // Lecturers typically should not "update" student submissions (only assess)
        return false;
    }

    public function delete(User $user, Submission $submission): bool
    {
        if ($this->isAdmin($user)) return true;
        // Students can delete their submission before due date (optional rule)
        if ($this->isStudent($user) && $user->student && $submission->student_id === $user->student->id) {
            $due = $submission->assignment->due_at;
            return is_null($due) || now()->lessThanOrEqualTo($due);
        }
        return false;
    }
}
