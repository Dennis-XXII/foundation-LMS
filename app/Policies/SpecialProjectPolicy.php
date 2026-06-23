<?php

namespace App\Policies;

use App\Models\{User, SpecialProject};
use App\Policies\Concerns\AuthorizesLms;

class SpecialProjectPolicy
{
    use AuthorizesLms;

    public function view(?User $user, SpecialProject $specialProject): bool
    {
        // Admin/lecturer: always
        if ($user && ($this->isAdmin($user) || $this->isLecturer($user))) {
            return true;
        }

        // Student/guest: published + level gate
        $course = $specialProject->course;
        $level  = $this->studentLevelForCourse($user, $course);

        return $specialProject->is_published
            && ($specialProject->level === null || ($level !== null && $specialProject->level <= $level));
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isLecturer($user);
    }

    public function update(User $user, SpecialProject $specialProject): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $specialProject->course);
    }

    public function delete(User $user, SpecialProject $specialProject): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $specialProject->course);
    }

    public function restore(User $user, SpecialProject $specialProject): bool
    {
        return $this->update($user, $specialProject);
    }

    public function forceDelete(User $user, SpecialProject $specialProject): bool
    {
        return $this->isAdmin($user);
    }
}
