<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display the dashboard with the current list of students.
     */
    public function index()
    {
        $students = Student::orderByDesc('created_at')->get();

        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $teacherCount = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->count()
            : 0;

        return view('dashboard', [
            'students' => $students,
            'studentCount' => $students->count(),
            'teacherCount' => $teacherCount,
            'newToday' => Student::whereDate('created_at', today())->count(),
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
