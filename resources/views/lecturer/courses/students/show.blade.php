<x-layout>
    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('lecturer.courses.index') }}" class="hover:text-blue-600">Courses</a>
                <span>/</span>
                <a href="{{ route('lecturer.courses.show', $course) }}" class="hover:text-blue-600">{{ $course->code }}</a>
                <span>/</span>
                <a href="{{ route('lecturer.courses.students.index', $course) }}" class="hover:text-blue-600">Students</a>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Student Profile</h1>
        </div>
        
        <a href="{{ route('lecturer.courses.students.index', $course) }}" 
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
            &larr; Back to Student List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Left Column: Student Identity Card --}}
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-600 h-24"></div> {{-- Blue Banner --}}
                <div class="px-6 pb-6 relative">
                    {{-- Avatar / Initials --}}
                    <div class="-mt-12 mb-4">
                        <div class="h-24 w-24 rounded-full border-4 border-white bg-gray-100 flex items-center justify-center text-3xl font-bold text-gray-400 shadow-md">
                            {{-- Extracts initials from name (e.g. "John Doe" -> "JD") --}}
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900">{{ $student->name }}</h2>
                    <p class="text-gray-500 text-sm mb-4">{{ $student->email }}</p>

                    <div class="border-t pt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Student ID</span>
                            <span class="font-medium text-gray-900">{{ $enrollment->student->student_id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Enrolled On</span>
                            <span class="font-medium text-gray-900">
                                {{-- We access the pivot table 'created_at' if available, or fallback --}}
                                {{ $enrollment->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danger Zone: Remove Student --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</h3>
                <form action="{{ route('lecturer.courses.students.destroy', ['course' => $course, 'student' => $student]) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to remove {{ $student->name }} from this course? All their grades and submissions will be detached.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded border border-transparent hover:border-red-100 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Remove from Course
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Progress & History --}}
        <div class="md:col-span-2 space-y-6">
            
            {{-- 1. Progress Stats Row --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                {{-- Completion Card --}}
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <div class="text-xs font-medium text-gray-500 uppercase mb-1">Assignments Done</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-gray-800">{{ $submittedCount }}</span>
                        <span class="text-gray-400 text-sm">/ {{ $totalAssignments }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                        <div class="bg-blue-600 h-1.5 rounded-full" 
                             style="width: {{ $totalAssignments > 0 ? ($submittedCount / $totalAssignments) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                
            </div>

            {{-- 2. Detailed Assignment Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-semibold text-gray-800">Assignment History</h3>
                </div>
                
                @if($assignments->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <p>No assignments have been created for this course yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Assignment</th>
                                    <th class="px-6 py-3 font-medium">Due Date</th>
                                    <th class="px-6 py-3 font-medium">Status</th>
                                    <th class="px-6 py-3 font-medium">Grade</th>
                                    <th class="px-6 py-3 font-medium text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($assignments as $assignment)
                                    @php
                                            $submission = $submissions->get($assignment->id);
                                            
                                            // FIX: Check if due_at exists first. 
                                            // If due_at is null, it cannot be overdue.
                                            $isOverdue = $assignment->due_at 
                                                        && \Carbon\Carbon::now()->gt($assignment->due_at) 
                                                        && !$submission;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $assignment->title }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ \Carbon\Carbon::parse($assignment->due_at)->format('M d, H:i') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($submission)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700">
                                                    Submitted
                                                </span>
                                            @elseif($isOverdue)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-50 text-red-700">
                                                    Missing
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($submission && $submission->grade !== null)
                                                <span class="font-bold text-gray-900">{{ $submission->grade }}</span>/100
                                            @elseif($submission)
                                                <span class="text-gray-400 italic">--</span>
                                            @else
                                                <span class="text-gray-200">--</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($submission)
                                                {{-- Link to Grade/Assess the submission --}}
                                                <a href="{{ route('lecturer.submissions.assessments.create', $submission) }}" 
                                                   class="text-blue-600 font-medium hover:underline hover:text-blue-800">
                                                    @if($submission->grade) Review @else Grade @endif
                                                </a>
                                            @else
                                                <span class="text-gray-300 cursor-default">No Work</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layout>