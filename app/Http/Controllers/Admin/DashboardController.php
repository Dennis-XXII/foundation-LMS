<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Announcement;
use App\Models\Student;
use App\Models\Lecturer;

class DashboardController extends Controller
{
    public function __construct()
    {
        // You already protect the whole /admin group with ['auth','admin']
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {

        
        // Example high‑level metrics for your UI
        return view('admin.dashboard', [
            'adminUser'     => Auth::user(),                
            'coursesCount'  => Course::count(),
            'studentsCount' => Student::count(),
            'lecturersCount'=> Lecturer::count(),
            'announcements' => Announcement::latest()->take(6)->get(),
            'courses' => Course::all(),
        ]);
    }
}
