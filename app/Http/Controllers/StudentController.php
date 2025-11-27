<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            : $data['students']->pluck('room')->filter()->unique();

        $studentsByRoom = collect($data['students'])->groupBy(fn ($s) => $s->room ?? '-');

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
            ->with('status', '??????????????????????');
    }

    /**
     * Shared homeroom data builder for dashboard and export.
     */
    private function buildHomeroomData(): array
    {
        $user = Auth::user();

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
<<<<<<< HEAD
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $homeroomRooms = collect();
        if ($user && $user->hasRole('teacher')) {
            $homeroomRooms = collect(preg_split('/[,;]/', (string) $user->homeroom))
                ->map(fn ($room) => trim($room))
                ->filter()
                ->values();
        }

        $courses = Course::where('user_id', Auth::id())->latest()->get();
        $courseRooms = $courses
            ->flatMap(fn ($course) => collect($course->rooms ?? []))
            ->filter()
            ->unique()
            ->values();

        $assignedRooms = $homeroomRooms->isNotEmpty() ? $homeroomRooms : $courseRooms;

        $studentsByRoom = Student::query()
            ->when($assignedRooms->isNotEmpty(), fn ($q) => $q->whereIn('room', $assignedRooms))
            ->orderBy('room')
            ->orderBy('student_code')
            ->get()
            ->groupBy(fn ($s) => $s->room ?? '-');

        $filterRoom = trim((string) $request->query('room', ''));
        if ($filterRoom !== '' && $assignedRooms->isNotEmpty() && ! $assignedRooms->contains($filterRoom)) {
            abort(403, 'ห้องนี้ไม่ได้อยู่ในความรับผิดชอบของคุณ');
        }

        if ($filterRoom !== '') {
            $studentsByRoom = $studentsByRoom->only([$filterRoom]);
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
            ]);

        $fileName = $filterRoom !== ''
            ? 'students-room-' . str_replace(['/', ' '], '_', $filterRoom) . '.pdf'
            : 'students.pdf';

        return $pdf->download($fileName);
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
=======
        ];
>>>>>>> b4a503de30d69066aa64bfb4eab3682c67130fd5
    }
}
