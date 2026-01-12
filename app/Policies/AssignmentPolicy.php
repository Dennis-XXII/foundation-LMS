<?php

namespace App\Policies;

use App\Models\{User, Assignment};
use App\Policies\Concerns\AuthorizesLms;

class AssignmentPolicy
{
    use AuthorizesLms;

    public function view(?User $user, Assignment $assignment): bool
    {
        // Admin/lecturer: always
        if ($user && ($this->isAdmin($user) || $this->isLecturer($user))) {
            return true;
        }

        // Student/guest: published + level gate
        $course = $assignment->course;
        $level  = $this->studentLevelForCourse($user, $course);

        return $assignment->is_published
            && ($assignment->level === null || ($level !== null && $assignment->level <= $level));
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isLecturer($user);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $assignment->course);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $assignment->course);
    }

    public function restore(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function forceDelete(User $user, Assignment $assignment): bool
    {
        return $this->isAdmin($user);
    }
}
