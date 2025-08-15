<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Material::class     => \App\Policies\MaterialPolicy::class,
        \App\Models\Assignment::class   => \App\Policies\AssignmentPolicy::class,
        \App\Models\Announcement::class => \App\Policies\AnnouncementPolicy::class,
        \App\Models\Assessment::class   => \App\Policies\AssessmentPolicy::class,
        \App\Models\Submission::class   => \App\Policies\SubmissionPolicy::class,
        \App\Models\Course::class       => \App\Policies\CoursePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // You can define Gates here if needed
    }
}
