<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Display the dashboard with the current list of students.
     */
    public function index()
    {
        $user = Auth::user();

        // เตรียม filter นักเรียนตามห้องของครู (ห้องประจำชั้นหรือห้องจากหลักสูตร)
        $homeroomRooms = collect();
        if ($user && $user->hasRole('teacher')) {
            $homeroomRooms = collect(preg_split('/[,;]/', (string) $user->homeroom))
                ->map(fn ($room) => trim($room))
                ->filter()
                ->values();
        }

        $courses = Course::where('user_id', Auth::id())
            ->latest()
            ->get();

        $courseRooms = $courses
            ->flatMap(fn ($course) => collect($course->rooms ?? []))
            ->filter()
            ->unique()
            ->values();

        $assignedRooms = $homeroomRooms->isNotEmpty() ? $homeroomRooms : $courseRooms;

        $studentQuery = Student::query();
        if ($assignedRooms->isNotEmpty()) {
            $studentQuery->whereIn('room', $assignedRooms);
        } elseif ($user && $user->hasRole('teacher')) {
            // ครูไม่มีการตั้งค่าห้อง → ไม่ต้องแสดงใคร
            $studentQuery->whereRaw('1 = 0');
        }

        $students = $studentQuery
            ->orderBy('room')
            ->orderBy('student_code')
            ->get();

        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $teacherCount = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->count()
            : 0;

        $attendanceToday = Student::whereDate('created_at', today())->count();

        return view('dashboard', [
            'students' => $students,
            'studentCount' => $students->count(),
            'courses' => $courses,
            'courseCount' => $courses->count(),
            'teacherCount' => $teacherCount,
            'attendanceToday' => $attendanceToday,
            'homeroomRooms' => $homeroomRooms,
            'assignedRooms' => $assignedRooms,
            // newToday kept for backward compatibility
            'newToday' => $attendanceToday,
        ]);
    }

    /**
     * Persist a new student into storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'required|string|max:20|unique:students,student_code',
            'title' => 'required|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);

        Student::create($validated);

        return redirect()
            ->route('dashboard')
            ->with('status', 'เพิ่มข้อมูลนักเรียนเรียบร้อยแล้ว');
    }
}
