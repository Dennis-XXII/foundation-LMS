<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;

class UsefulLinkController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $usefulLinks = $course->usefulLinks()->latest()->get();

        return view('student.useful_links.index', compact('course', 'usefulLinks'));
    }
}
