<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display the dashboard with the current list of students.
     */
    public function index()
    {
        $data = $this->buildHomeroomData();

        return view('dashboard', $data);
    }

    /**
     * Export homeroom students to PDF.
     */
    public function exportHomeroom()
    {
        $data = $this->buildHomeroomData();

        $rooms = $data['assignedRooms']->isNotEmpty()
            ? $data['assignedRooms']
            : $data['students']->pluck('classroom')->filter()->unique();

        $studentsByRoom = collect($data['students'])->groupBy(fn ($s) => $s->classroom ?? '-');

        $fontPath = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');

        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $leelaRegularPath = "{$fontPath}/LeelawUI.ttf";
        $leelaBoldPath = "{$fontPath}/LeelaUIb.ttf";

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
                'tempDir' => $fontCache,
                'defaultFont' => 'LeelawUI',
                'fontDir' => $fontPath,
                'fontCache' => $fontCache,
                'enable_font_subsetting' => true,
            ])
            ->loadView('teacher.homeroom-pdf', [
                'teacher' => Auth::user(),
                'courses' => $data['courses'],
                'rooms' => $rooms,
                'studentsByRoom' => $studentsByRoom,
                'generatedAt' => now(),
            ]);

        // Explicitly register LeelawUI font so Dompdf embeds it
        $metrics = $pdf->getDomPDF()->getFontMetrics();
        $metrics->registerFont([
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'normal',
        ], $leelaRegularPath);
        $metrics->registerFont([
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'bold',
        ], $leelaBoldPath);

        return $pdf->download('homeroom-students.pdf');
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

    /**
     * Shared homeroom data builder for dashboard and export.
     */
    private function buildHomeroomData(): array
    {
        $user = Auth::user();
        $hasClassroomCol = Schema::hasColumn('students', 'classroom');

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
            $studentQuery->where(function ($q) use ($assignedRooms, $hasClassroomCol) {
                if ($hasClassroomCol) {
                    $q->whereIn('classroom', $assignedRooms)
                      ->orWhereIn('room', $assignedRooms);
                } else {
                    $q->whereIn('room', $assignedRooms);
                }
            });
        } elseif ($user && $user->hasRole('teacher')) {
            $studentQuery->whereRaw('1 = 0');
        }

        $students = $studentQuery
            ->when($hasClassroomCol, fn ($q) => $q->orderBy('classroom'))
            ->orderBy('student_code')
            ->get();

        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $teacherCount = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->count()
            : 0;

        $attendanceToday = Student::whereDate('created_at', today())->count();

        return [
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
        ];
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $hasClassroomCol = Schema::hasColumn('students', 'classroom');
        $courseName = null;

        $homeroomRooms = collect();
        if ($user && $user->hasRole('teacher')) {
            $homeroomRooms = collect(preg_split('/[,;]/', (string) $user->homeroom))
                ->map(fn ($room) => trim($room))
                ->filter()
                ->values();
        }

        $courses = Course::where('user_id', Auth::id())->latest()->get();
        $courseRooms = collect($courses)
            ->flatMap(fn ($course) => collect($course->rooms ?? []))
            ->filter()
            ->unique()
            ->values();

        // If a specific course is requested, use its rooms and name
        if ($courseId = $request->query('course_id')) {
            $course = Course::where('id', $courseId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $courseRooms = collect($course->rooms ?? [])->filter()->values();
            $courseName = $course->name;
        }

        // Force to support collection of plain strings to avoid Eloquent contains() calling getKey()
        $assignedRooms = $homeroomRooms->isNotEmpty() ? $homeroomRooms->values() : $courseRooms;
        $assignedRooms = collect($assignedRooms->all());

        $studentsByRoom = Student::query()
            ->when($assignedRooms->isNotEmpty(), fn ($q) => $q->where(function ($qq) use ($assignedRooms, $hasClassroomCol) {
                if ($hasClassroomCol) {
                    $qq->whereIn('classroom', $assignedRooms)
                       ->orWhereIn('room', $assignedRooms);
                } else {
                    $qq->whereIn('room', $assignedRooms);
                }
            }))
            ->when($hasClassroomCol, fn ($q) => $q->orderBy('classroom'))
            ->orderBy('student_code')
            ->get()
            ->groupBy(fn ($s) => $hasClassroomCol ? ($s->classroom ?? $s->room ?? '-') : ($s->room ?? '-'));

        $filterRoom = trim((string) $request->query('room', ''));
        if ($filterRoom !== '' && $assignedRooms->isNotEmpty() && ! $assignedRooms->contains($filterRoom)) {
            abort(403, 'ห้องนี้ไม่ได้อยู่ในความรับผิดชอบของคุณ');
        }

        if ($filterRoom !== '') {
            $filterKey = $studentsByRoom->keys()->first(fn ($k) => (string) $k === $filterRoom) ?? $filterRoom;
            $studentsByRoom = collect([$filterKey => $studentsByRoom->get($filterKey, collect())]);
            $assignedRooms = collect([$filterRoom]);
        }

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'fontDir'   => storage_path('fonts'),
                'fontCache' => storage_path('fonts'),
                'defaultFont' => 'leelawadee',
            ])
            ->loadView('teacher.students-export', [
                'teacher' => $user,
                'studentsByRoom' => $studentsByRoom,
                'assignedRooms' => $assignedRooms,
                'courseName' => $courseName,
            ]);

        $baseName = 'students';
        if ($courseName) {
            $baseName = 'students-course-' . Str::slug($courseName);
        }
        $fileName = $filterRoom !== ''
            ? $baseName . '-room-' . str_replace(['/', ' '], '_', $filterRoom) . '.pdf'
            : $baseName . '.pdf';

        return $pdf->download($fileName);
    }
}
