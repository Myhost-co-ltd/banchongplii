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
            : $data['students']->pluck('room_normalized')->filter()->unique();

        $studentsByRoom = collect($data['students'])->groupBy(fn ($s) => $s->room_normalized ?? '-');

        $fontPath = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');
        $thSarabunRegularPath = storage_path('fonts/THSarabunNew-Regular.ttf');
        $thSarabunBoldPath = storage_path('fonts/THSarabunNew-Bold.ttf');
        $thSarabunItalicPath = storage_path('fonts/THSarabunNew-Italic.ttf');
        $thSarabunBoldItalicPath = storage_path('fonts/THSarabunNew-BoldItalic.ttf');

        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
                'tempDir' => $fontCache,
                'defaultFont' => 'THSarabunNew',
                'fontDir' => $fontPath,
                'fontCache' => $fontCache,
                // Embed full fonts so Thai vowel/tone marks stack correctly
                'enable_font_subsetting' => false,
            ])
            ->loadView('teacher.homeroom-pdf', [
                'teacher' => Auth::user(),
                'courses' => $data['courses'],
                'rooms' => $rooms,
                'studentsByRoom' => $studentsByRoom,
                'generatedAt' => now(),
            ]);

        // Register Thai fonts so Dompdf embeds them
        $metrics = $pdf->getDomPDF()->getFontMetrics();
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'normal',
            'weight' => 'normal',
        ], $thSarabunRegularPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'normal',
            'weight' => 'bold',
        ], $thSarabunBoldPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'italic',
            'weight' => 'normal',
        ], $thSarabunItalicPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'italic',
            'weight' => 'bold',
        ], $thSarabunBoldItalicPath);
        $metrics->registerFont([
            'family' => 'Sarabun',
            'style' => 'normal',
            'weight' => 'normal',
        ], storage_path('fonts/Sarabun-Regular.ttf'));
        $metrics->registerFont([
            'family' => 'Sarabun',
            'style' => 'normal',
            'weight' => 'bold',
        ], storage_path('fonts/Sarabun-Bold.ttf'));
        $metrics->registerFont([
            'family' => 'Noto Sans Thai',
            'style' => 'normal',
            'weight' => 'normal',
        ], storage_path('fonts/NotoSansThai-Regular.ttf'));
        $metrics->registerFont([
            'family' => 'Noto Sans Thai',
            'style' => 'normal',
            'weight' => 'bold',
        ], storage_path('fonts/NotoSansThai-Bold.ttf'));

        return $pdf->download('homeroom-students.pdf');
    }

    /**
     * Persist a new student into storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'student_code' => 'required|string|max:20|unique:students,student_code',
                'title' => 'required|string|max:20',
                'first_name' => 'required|string|max:100|regex:/^(?!.*\\d)[\\p{L}\\p{M}\\s]+$/u',
                'last_name' => 'required|string|max:100|regex:/^(?!.*\\d)[\\p{L}\\p{M}\\s]+$/u',
            ],
            [
                'first_name.regex' => 'ชื่อต้องเป็นตัวอักษรและไม่มีตัวเลข',
                'last_name.regex'  => 'นามสกุลต้องเป็นตัวอักษรและไม่มีตัวเลข',
            ]
        );

        Student::create($validated);

        return redirect()
            ->route('dashboard')
            ->with('status', 'บันทึกข้อมูลนักเรียนเรียบร้อยแล้ว');
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

        $normalizeRoom = fn ($item) => $this->normalizeRoomValue($item);

        $courseRooms = $courses
            ->flatMap(fn ($course) => collect($course->rooms ?? [])->map($normalizeRoom))
            ->filter()
            ->unique()
            ->values();

        $assignedRooms = $homeroomRooms->isNotEmpty() ? $homeroomRooms : $courseRooms;
        $assignedRooms = $assignedRooms->map($normalizeRoom)->filter()->values();

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
        $students = $students->map(function ($student) use ($normalizeRoom) {
            $student->room_normalized = $normalizeRoom($student->classroom ?? $student->room ?? null);
            return $student;
        });

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
        $normalizeRoom = fn ($item) => $this->normalizeRoomValue($item);
        $courseRooms = collect($courses)
            ->flatMap(fn ($course) => collect($course->rooms ?? [])->map($normalizeRoom))
            ->filter()
            ->unique()
            ->values();

        // If a specific course is requested, use its rooms and name
        if ($courseId = $request->query('course_id')) {
            $course = Course::where('id', $courseId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $courseRooms = collect($course->rooms ?? [])->map($normalizeRoom)->filter()->values();
            $courseName = $course->name;
        }

        // Force to support collection of plain strings to avoid Eloquent contains() calling getKey()
        $assignedRooms = $homeroomRooms->isNotEmpty() ? $homeroomRooms->values() : $courseRooms;
        $assignedRooms = collect($assignedRooms->all())->map($normalizeRoom)->filter()->values();

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
            ->map(function ($student) use ($normalizeRoom) {
                $student->room_normalized = $normalizeRoom($student->classroom ?? $student->room ?? null);
                return $student;
            })
            ->groupBy(fn ($s) => $s->room_normalized ?? '-');

        $filterRoom = trim((string) $request->query('room', ''));
        if ($filterRoom !== '' && $assignedRooms->isNotEmpty() && ! $assignedRooms->contains($filterRoom)) {
            abort(403, 'ไม่สามารถดูห้องที่คุณไม่ได้รับอนุญาต');
        }

        if ($filterRoom !== '') {
            $filterKey = $studentsByRoom->keys()->first(fn ($k) => (string) $k === $filterRoom) ?? $filterRoom;
            $studentsByRoom = collect([$filterKey => $studentsByRoom->get($filterKey, collect())]);
            $assignedRooms = collect([$filterRoom]);
        }

        $fontPath  = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');
        $thSarabunRegularPath = storage_path('fonts/THSarabunNew-Regular.ttf');
        $thSarabunBoldPath = storage_path('fonts/THSarabunNew-Bold.ttf');
        $thSarabunItalicPath = storage_path('fonts/THSarabunNew-Italic.ttf');
        $thSarabunBoldItalicPath = storage_path('fonts/THSarabunNew-BoldItalic.ttf');
        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $pdf = Pdf::setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'chroot'               => base_path(),
                'tempDir'              => $fontCache,
                'defaultFont'          => 'THSarabunNew',
                'fontDir'              => $fontPath,
                'fontCache'            => $fontCache,
                // Embed full fonts so Thai vowel/tone marks stack correctly
                'enable_font_subsetting' => false,
            ])
            ->loadView('teacher.students-export', [
                'teacher' => $user,
                'studentsByRoom' => $studentsByRoom,
                'assignedRooms' => $assignedRooms,
                'courseName' => $courseName,
            ]);

        // Register Thai fonts so Dompdf embeds them
        $metrics = $pdf->getDomPDF()->getFontMetrics();
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'normal',
            'weight' => 'normal',
        ], $thSarabunRegularPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'normal',
            'weight' => 'bold',
        ], $thSarabunBoldPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'italic',
            'weight' => 'normal',
        ], $thSarabunItalicPath);
        $metrics->registerFont([
            'family' => 'THSarabunNew',
            'style' => 'italic',
            'weight' => 'bold',
        ], $thSarabunBoldItalicPath);
        $metrics->registerFont([
            'family' => 'Noto Sans Thai',
            'style' => 'normal',
            'weight' => 'normal',
        ], storage_path('fonts/NotoSansThai-Regular.ttf'));
        $metrics->registerFont([
            'family' => 'Noto Sans Thai',
            'style' => 'normal',
            'weight' => 'bold',
        ], storage_path('fonts/NotoSansThai-Bold.ttf'));

        $baseName = 'students';
        if ($courseName) {
            $baseName = 'students-course-' . Str::slug($courseName);
        }
        $fileName = $filterRoom !== ''
            ? $baseName . '-room-' . str_replace(['/', ' '], '_', $filterRoom) . '.pdf'
            : $baseName . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Normalize room values that may arrive as strings, objects, or arrays.
     * Supports array shapes like ['room' => 'X'], ['name' => 'X'], or [0, 1, 2] (uses index 2 first).
     */
    private function normalizeRoomValue($item): ?string
    {
        // Decode JSON strings like "[\"a\",\"b\",\"c\"]" or objects
        if (is_string($item) && str_starts_with(trim($item), '[')) {
            $decoded = json_decode($item, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $item = $decoded;
            }
        } elseif (is_object($item)) {
            $item = (array) $item;
        }

        if (is_array($item)) {
            if (array_key_exists('room', $item)) {
                $item = $item['room'];
            } elseif (array_key_exists('name', $item)) {
                $item = $item['name'];
            } else {
                $item = $item[2] ?? $item[1] ?? $item[0] ?? null;
            }
        }

        if (is_null($item)) {
            return null;
        }

        if (is_string($item) || is_numeric($item)) {
            $trimmed = trim((string) $item);

            return $trimmed === '' ? null : $trimmed;
        }

        return null;
    }
}

