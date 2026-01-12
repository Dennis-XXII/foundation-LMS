<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Lecturer;
use App\Models\User;

class LecturerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $lecturers = Lecturer::with('user')->latest()->paginate(20);
        return view('admin.lecturers.index', compact('lecturers'));
    }

    public function create()
    {
        return view('admin.lecturers.create');
    }

    public function store(Request $request)
    {
        // Create a User with role=lecturer, then attach a Lecturer profile
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'nickname' => ['nullable','string','max:255'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => 'lecturer',
            'nickname' => $data['nickname'] ?? $data['name'],
        ]);

        Lecturer::create(['user_id' => $user->id]);

        return to_route('admin.lecturers.index')->with('success', 'Lecturer created by admin: '.Auth::id());
    }

    public function edit(Lecturer $lecturer)
    {
        $lecturer->load('user');
        return view('admin.lecturers.edit', compact('lecturer'));
    }

    public function update(Request $request, Lecturer $lecturer)
    {
        $lecturer->load('user');

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($lecturer->user_id)],
            'nickname' => ['nullable','string','max:255'],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        $payload = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'nickname' => $data['nickname'] ?? $lecturer->user->nickname,
        ];
        if (!empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        $lecturer->user->update($payload);

        return back()->with('success', 'Lecturer updated by admin: '.Auth::id());
    }

    public function destroy(Lecturer $lecturer)
    {
        // Deleting Lecturer will cascade if FK is set; otherwise detach manually as needed
        $lecturer->delete();
        return to_route('admin.lecturers.index')->with('success', 'Lecturer deleted.');
    }
}
