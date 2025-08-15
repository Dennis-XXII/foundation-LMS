<?php

namespace App\Policies;

use App\Models\{User, Announcement, Course};
use App\Policies\Concerns\AuthorizesLms;

class AnnouncementPolicy
{
    use AuthorizesLms;

    public function view(?User $user, Announcement $announcement): bool
    {
        if ($announcement->is_global) {
            return true; // everyone can view global announcements
        }

        // For course-specific announcements, students must be enrolled in any linked course
        $courses = $announcement->courses()->get();

        // Admin/lecturer can view
        if ($user && ($this->isAdmin($user) || $this->isLecturer($user))) {
            return true;
        }

        // Student must be enrolled in at least one course attached to the announcement
        if ($user && $this->isStudent($user) && $user->student) {
            foreach ($courses as $course) {
                if ($this->studentEnrolledInCourse($user, $course)) return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Only lecturer/admin can post announcements
        return $this->isAdmin($user) || $this->isLecturer($user);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($this->isAdmin($user)) return true;
        // Allow the original poster to update, or lecturers assigned to any attached course
        if ($announcement->user_id === $user->id) return true;

        foreach ($announcement->courses as $course) {
            if ($this->lecturerTeachesCourse($user, $course)) return true;
        }
        return false;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($this->isAdmin($user)) return true;
        return $announcement->user_id === $user->id;
    }
}
