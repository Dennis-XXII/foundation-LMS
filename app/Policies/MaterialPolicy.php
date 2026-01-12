<?php

namespace App\Policies;

use App\Models\{User, Material};
use App\Policies\Concerns\AuthorizesLms;

class MaterialPolicy
{
    use AuthorizesLms;

    public function view(?User $user, Material $material): bool
    {
        // Admin/lecturer can view always (includes unpublished)
        if ($user && ($this->isAdmin($user) || $this->isLecturer($user))) {
            return true;
        }

        // Guests/students: only published and within level-gate
        $course = $material->course;
        $level  = $this->studentLevelForCourse($user, $course);

        return $material->is_published
            && ($material->level === null || ($level !== null && $material->level <= $level));
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isLecturer($user);
    }

    public function update(User $user, Material $material): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $material->course);
    }

    public function delete(User $user, Material $material): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->lecturerTeachesCourse($user, $material->course);
    }

    public function restore(User $user, Material $material): bool
    {
        return $this->update($user, $material);
    }

    public function forceDelete(User $user, Material $material): bool
    {
        return $this->isAdmin($user);
    }
}
