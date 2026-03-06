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
        $schoolName = $this->resolveSchoolName();

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
                'schoolName' => $schoolName,
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
        $assignedRoomLookup = $this->buildAssignedRoomLookup($assignedRooms);
        $assignedRoomFilters = $this->buildAssignedRoomFilterValues($assignedRooms);

        $studentQuery = Student::query();
        if ($assignedRooms->isNotEmpty()) {
            $studentQuery->where(function ($q) use ($assignedRoomFilters, $hasClassroomCol) {
                if ($hasClassroomCol) {
                    $q->whereIn('classroom', $assignedRoomFilters)
                      ->orWhereIn('room', $assignedRoomFilters);
                } else {
                    $q->whereIn('room', $assignedRoomFilters);
                }
            });
        } elseif ($user && $user->hasRole('teacher')) {
            $studentQuery->whereRaw('1 = 0');
        }

        $students = $studentQuery
            ->when($hasClassroomCol, fn ($q) => $q->orderBy('classroom'))
            ->orderBy('student_code')
            ->get();
        $students = $students->map(function ($student) use ($normalizeRoom, $assignedRoomLookup) {
            $rawRoom = $normalizeRoom($student->classroom ?? $student->room ?? null);
            $student->room_normalized = $this->resolveAssignedRoomLabel($rawRoom, $assignedRoomLookup);
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
        $schoolName = $this->resolveSchoolName();

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
        $assignedRoomLookup = $this->buildAssignedRoomLookup($assignedRooms);
        $assignedRoomFilters = $this->buildAssignedRoomFilterValues($assignedRooms);

        $studentsByRoom = Student::query()
            ->when($assignedRooms->isNotEmpty(), fn ($q) => $q->where(function ($qq) use ($assignedRoomFilters, $hasClassroomCol) {
                if ($hasClassroomCol) {
                    $qq->whereIn('classroom', $assignedRoomFilters)
                       ->orWhereIn('room', $assignedRoomFilters);
                } else {
                    $qq->whereIn('room', $assignedRoomFilters);
                }
            }))
            ->when($hasClassroomCol, fn ($q) => $q->orderBy('classroom'))
            ->orderBy('student_code')
            ->get()
            ->map(function ($student) use ($normalizeRoom, $assignedRoomLookup) {
                $rawRoom = $normalizeRoom($student->classroom ?? $student->room ?? null);
                $student->room_normalized = $this->resolveAssignedRoomLabel($rawRoom, $assignedRoomLookup);
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
                'schoolName' => $schoolName,
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

    private function resolveSchoolName(): string
    {
        $default = 'โรงเรียนบ้านช่องพลี';
        $path = storage_path('app/login-settings.json');

        if (! file_exists($path)) {
            return $default;
        }

        $settings = json_decode((string) file_get_contents($path), true);
        if (! is_array($settings)) {
            return $default;
        }

        $name = trim((string) ($settings['login_title'] ?? ''));
        return $name !== '' ? $name : $default;
    }

    private function buildAssignedRoomLookup($assignedRooms): array
    {
        $lookup = [];

        foreach (collect($assignedRooms)->filter() as $room) {
            $canonical = $this->normalizeRoomValue($room);
            if ($canonical === null) {
                continue;
            }

            foreach ($this->buildRoomAliases($canonical) as $alias) {
                $key = $this->roomAliasKey($alias);
                if ($key !== null && ! isset($lookup[$key])) {
                    $lookup[$key] = $canonical;
                }
            }
        }

        return $lookup;
    }

    private function buildAssignedRoomFilterValues($assignedRooms)
    {
        return collect($assignedRooms)
            ->filter()
            ->flatMap(fn ($room) => $this->buildRoomAliases((string) $room))
            ->map(fn ($room) => trim((string) $room))
            ->filter(fn ($room) => $room !== '')
            ->unique(fn ($room) => $this->roomAliasKey($room) ?? $room)
            ->values();
    }

    private function resolveAssignedRoomLabel(?string $room, array $assignedRoomLookup): ?string
    {
        if ($room === null) {
            return null;
        }

        foreach ($this->buildRoomAliases($room) as $alias) {
            $key = $this->roomAliasKey($alias);
            if ($key !== null && isset($assignedRoomLookup[$key])) {
                return $assignedRoomLookup[$key];
            }
        }

        return $room;
    }

    private function buildRoomAliases(string $room): array
    {
        $base = trim($room);
        if ($base === '') {
            return [];
        }

        $aliases = collect([$base]);
        $compact = preg_replace('/\s+/u', '', $base) ?? $base;
        $aliases->push($compact);

        $parsed = $this->extractGradeRoomFromText($base);
        if ($parsed !== null) {
            $grade = (int) ($parsed['grade'] ?? 0);
            $roomNo = (int) ($parsed['room'] ?? 0);

            if ($grade > 0 && $roomNo > 0) {
                $aliases->push(sprintf('%d%02d', $grade, $roomNo));
                $aliases->push($grade . '/' . $roomNo);
                $aliases->push('ป.' . $grade . '/' . $roomNo);
                $aliases->push('U.' . $grade . '/' . $roomNo);
                $aliases->push('P.' . $grade . '/' . $roomNo);
                $aliases->push('ประถมศึกษา ' . $grade . '/' . $roomNo);
                $aliases->push('ประถมศึกษา' . $grade . '/' . $roomNo);
            }
        }

        return $aliases
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique(fn ($value) => $this->roomAliasKey($value) ?? $value)
            ->values()
            ->all();
    }

    private function extractGradeRoomFromText(string $value): ?array
    {
        $text = trim($value);
        if ($text === '') {
            return null;
        }

        $text = strtr($text, [
            '๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
            '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9',
        ]);
        $compact = preg_replace('/\s+/u', '', $text) ?? $text;

        if (preg_match('/^(\d)(\d{2})$/', $compact, $matches) === 1) {
            return [
                'grade' => (int) $matches[1],
                'room' => (int) $matches[2],
            ];
        }

        $patterns = [
            '/^(?:u|p|ป|ม)\.?(\d{1,2})\/(\d{1,2})$/iu',
            '/^ประถมศึกษา(\d{1,2})\/(\d{1,2})$/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $compact, $matches) === 1) {
                return [
                    'grade' => (int) $matches[1],
                    'room' => (int) $matches[2],
                ];
            }
        }

        return null;
    }

    private function roomAliasKey(?string $room): ?string
    {
        $value = trim((string) ($room ?? ''));
        if ($value === '') {
            return null;
        }

        $value = strtr($value, [
            '๐' => '0', '๑' => '1', '๒' => '2', '๓' => '3', '๔' => '4',
            '๕' => '5', '๖' => '6', '๗' => '7', '๘' => '8', '๙' => '9',
        ]);
        $value = preg_replace('/\s+/u', '', $value) ?? $value;
        $value = str_replace('.', '', $value);

        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
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
