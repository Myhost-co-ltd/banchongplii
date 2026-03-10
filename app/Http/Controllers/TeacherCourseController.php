<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAttendanceHoliday;
use App\Models\CourseAttendanceRecord;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherCourseController extends Controller
{
    public function __construct()
    {
        Course::clearExpiredTemporaryAssignments();
    }

    public function index()
    {
        $teacherMajor = Auth::user()->major ?? null;

        $courses = Course::visibleToTeacher((int) Auth::id())
            ->latest()
            ->get();

        // รวมรายชื่อหลักสูตรทั้งหมด (ไม่จำกัดวิชาเอก) ให้เลือกหรือเป็นตัวอย่างค่าอัตโนมัติ
        $localCourseOptions = Course::query()
            ->select('id', 'name', 'grade', 'term', 'year')
            ->orderBy('name')
            ->get();

        $adminCourseOptions = $localCourseOptions;

        if (Schema::hasTable('tb_course') && Schema::hasColumn('tb_course', 'name_course')) {
            $legacyCourseOptions = DB::table('tb_course')
                ->selectRaw('MIN(id_course) AS id, TRIM(name_course) AS name')
                ->whereNotNull('name_course')
                ->whereRaw("TRIM(name_course) <> ''")
                ->groupByRaw('TRIM(name_course)')
                ->orderByRaw('TRIM(name_course)')
                ->get()
                ->map(function ($course) {
                    return (object) [
                        'id' => (int) ($course->id ?? 0),
                        'name' => trim((string) ($course->name ?? '')),
                        'grade' => null,
                        'term' => null,
                        'year' => null,
                    ];
                })
                ->filter(fn ($course) => $course->name !== '')
                ->values();

            if ($legacyCourseOptions->isNotEmpty()) {
                $normalize = function (string $name): string {
                    $value = trim($name);
                    return function_exists('mb_strtolower')
                        ? mb_strtolower($value, 'UTF-8')
                        : strtolower($value);
                };

                $adminCourseOptions = $legacyCourseOptions
                    ->concat($localCourseOptions)
                    ->unique(fn ($course) => $normalize((string) ($course->name ?? '')))
                    ->values();
            }
        }

        return view('teacher.course-create', [
            'courses' => $courses,
            'adminCourseOptions' => $adminCourseOptions,
            'teacherMajor' => $teacherMajor,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'grade'       => 'required|string|max:20',
            'rooms'       => 'required|array|min:1',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2,summer',
            'year'        => ['nullable', 'integer', 'min:1', $this->yearNotInPastRule()],
            'description' => 'nullable|string|max:5000',
            'assignment_cap' => 'nullable|integer|min:1|max:100',
        ]);

        $courseName = $validated['name'];
        $assignmentCap = $validated['assignment_cap'] ?? 70;

        Course::create([
            'user_id'     => Auth::id(),
            'name'        => $courseName,
            'grade'       => $validated['grade'],
            'rooms'       => $validated['rooms'],
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
            'assignment_cap' => $assignmentCap,
        ]);

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'สร้างหลักสูตรเรียบร้อยแล้ว');
    }

    public function claim(Request $request, Course $course)
    {
        if ($course->user_id) {
            abort(403, 'หลักสูตรนี้มีเจ้าของแล้ว');
        }

        $course->update(['user_id' => Auth::id()]);

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'เพิ่มหลักสูตรเข้าบัญชีของคุณแล้ว');
    }

    public function show(?int $courseId = null)
    {
        $teacherId = (int) Auth::id();
        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();

        $courses = Course::visibleToTeacher($teacherId)
            ->latest()
            ->get();

        if ($courseId !== null) {
            $course = $courses->firstWhere('id', $courseId);
            abort_if(! $course, 403);
        } else {
            $course = $courses->first();
        }

        if (! $course) {
            return redirect()
                ->route('teacher.course-create')
                ->with('status', 'ยังไม่มีหลักสูตร กรุณาสร้างหลักสูตรก่อนเข้าหน้ารายละเอียด');
        }

        $this->authorizeVisibleCourse($course);
        $canManageCourse = $course->isTeacherResponsible($teacherId, $todayDate);

        $selectedTerm = $this->resolveTerm($course, request('term'));
        $payload = $this->buildCoursePayload($course, $selectedTerm);

        return view('teacher.course-detail', [
            'course'        => $course,
            'courses'       => $courses,
            'selectedTerm'  => $selectedTerm,
            'hours'         => $payload['hours'],
            'lessons'       => $payload['lessons'],
            'assignments'   => $payload['assignments'],
            'lessonCapacity'=> $payload['lessonCapacity'],
            'assignmentTotal' => $payload['assignmentTotal'],
            'assignmentRemaining' => $payload['assignmentRemaining'],
            'lessonAllowedTotal' => $payload['lessonAllowedTotal'],
            'lessonUsedTotal' => $payload['lessonUsedTotal'],
            'lessonRemainingTotal' => $payload['lessonRemainingTotal'],
            'canManageCourse' => $canManageCourse,
        ]);
    }

    public function assignments(?int $courseId = null)
    {
        $courses = Course::accessibleByTeacher((int) Auth::id())
            ->latest()
            ->get();

        $course = $courses->firstWhere('id', $courseId) ?? $courses->first();

        if (! $course) {
            return redirect()
                ->route('teacher.course-create')
                ->with('status', 'ยังไม่มีหลักสูตร กรุณาสร้างหลักสูตรก่อนเข้าหน้าติดตามงาน');
        }

        $this->authorizeCourse($course);

        $selectedTerm = $this->resolveTerm($course, request('term'));
        $payload = $this->buildCoursePayload($course, $selectedTerm);

        $studentData = $this->buildCourseStudentData($course);
        $assignedRooms = $studentData['assignedRooms'];
        $studentsByRoom = $studentData['studentsByRoom'];

        return view('teacher.course-assignments', [
            'course'               => $course,
            'courses'              => $courses,
            'selectedTerm'         => $selectedTerm,
            'assignments'          => $payload['assignments'],
            'assignmentCap'        => $payload['assignmentCap'],
            'assignmentTotal'      => $payload['assignmentTotal'],
            'assignmentRemaining'  => $payload['assignmentRemaining'],
            'studentsByRoom'       => $studentsByRoom,
            'assignedRooms'        => $assignedRooms,
        ]);
    }

    public function attendance(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $filters = $request->validate([
            'term' => 'nullable|in:1,2,summer',
            'date' => 'nullable|date',
            'room' => 'nullable|string|max:50',
        ]);

        $courses = Course::accessibleByTeacher((int) Auth::id())
            ->latest()
            ->get();

        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $selectedTerm = $this->resolveTerm($course, $filters['term'] ?? null);
        $attendanceDate = $filters['date']
            ?? $todayDate;
        if ($attendanceDate !== $todayDate) {
            $attendanceDate = $todayDate;
        }
        $holidayRecord = $this->findAttendanceHolidayWithGlobal($course->id, $selectedTerm, $attendanceDate);
        $isHoliday = $holidayRecord !== null;

        $studentData = $this->buildCourseStudentData($course);
        $assignedRooms = $studentData['assignedRooms'];
        $studentsByRoom = $studentData['studentsByRoom'];
        $roomOptions = collect($studentsByRoom->keys())->values();
        $selectedRoom = trim((string) ($filters['room'] ?? ''));

        if ($selectedRoom !== '' && $roomOptions->contains($selectedRoom)) {
            $studentsByRoom = collect([
                $selectedRoom => collect($studentsByRoom->get($selectedRoom, collect())),
            ]);
        } else {
            $selectedRoom = '';
        }

        $visibleStudentIds = $studentsByRoom
            ->flatten(1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $records = collect();
        if (! $isHoliday) {
            $recordsQuery = CourseAttendanceRecord::query()
                ->where('course_id', $course->id)
                ->where('term', $selectedTerm)
                ->whereDate('attendance_date', $attendanceDate);

            if ($visibleStudentIds->isNotEmpty()) {
                $recordsQuery->whereIn('student_id', $visibleStudentIds->all());
            } else {
                $recordsQuery->whereRaw('1 = 0');
            }

            $records = $recordsQuery->get();
        }

        $attendanceByStudent = $records
            ->keyBy('student_id')
            ->map(fn (CourseAttendanceRecord $record) => [
                'status' => $record->status,
            ])
            ->all();

        $statusSummary = [
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'absent' => $records->where('status', 'absent')->count(),
        ];

        return view('teacher.course-attendance', [
            'course' => $course,
            'courses' => $courses,
            'selectedTerm' => $selectedTerm,
            'attendanceDate' => $attendanceDate,
            'studentsByRoom' => $studentsByRoom,
            'assignedRooms' => $assignedRooms,
            'roomOptions' => $roomOptions,
            'selectedRoom' => $selectedRoom,
            'attendanceByStudent' => $attendanceByStudent,
            'statusSummary' => $statusSummary,
            'recordedCount' => $records->count(),
            'holidayRecord' => $holidayRecord,
            'isHoliday' => $isHoliday,
            'minAttendanceDate' => $todayDate,
            'maxAttendanceDate' => $todayDate,
        ]);
    }

    public function attendanceReport(Request $request, Course $course)
    {
        $this->authorizeVisibleCourse($course);

        $filters = $request->validate([
            'term' => 'nullable|in:1,2,summer',
            'date' => 'nullable|date',
            'month' => 'nullable|date_format:Y-m',
            'room' => 'nullable|string|max:50',
        ]);

        $courses = Course::visibleToTeacher((int) Auth::id())
            ->latest()
            ->get();

        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $canManageCourse = $course->isTeacherResponsible((int) Auth::id(), $todayDate);

        $payload = $this->buildAttendanceReportPayload($course, $filters);

        return view('teacher.course-attendance-report', array_merge([
            'course' => $course,
            'courses' => $courses,
            'canManageCourse' => $canManageCourse,
        ], $payload));
    }

    public function exportAttendanceReport(Request $request, Course $course)
    {
        $this->authorizeVisibleCourse($course);

        $filters = $request->validate([
            'term' => 'nullable|in:1,2,summer',
            'date' => 'nullable|date',
            'month' => 'nullable|date_format:Y-m',
            'room' => 'nullable|string|max:50',
        ]);

        $payload = $this->buildAttendanceReportPayload($course, $filters);

        $fontPath = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');

        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $thSarabunRegularPath = storage_path('fonts/THSarabunNew-Regular.ttf');
        $thSarabunBoldPath = storage_path('fonts/THSarabunNew-Bold.ttf');
        $thSarabunItalicPath = storage_path('fonts/THSarabunNew-Italic.ttf');
        $thSarabunBoldItalicPath = storage_path('fonts/THSarabunNew-BoldItalic.ttf');

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
                'fontDir' => $fontPath,
                'fontCache' => $fontCache,
                'tempDir' => $fontCache,
                'defaultFont' => 'THSarabunNew',
                'enable_font_subsetting' => false,
            ])
            ->loadView('teacher.course-attendance-report-pdf', array_merge([
                'course' => $course,
                'teacher' => $request->user(),
                'generatedAt' => now(config('app.timezone', 'Asia/Bangkok')),
            ], $payload))
            ->setPaper('a3', 'landscape');

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

        $selectedRoom = trim((string) ($payload['selectedRoom'] ?? ''));
        $roomSuffix = $selectedRoom !== '' ? '-room-' . Str::slug($selectedRoom) : '';
        $fileName = sprintf(
            'attendance-report-course-%d-term-%s-%s%s.pdf',
            (int) $course->id,
            (string) ($payload['selectedTerm'] ?? '1'),
            (string) ($payload['reportMonth'] ?? now()->format('Y-m')),
            $roomSuffix
        );

        return $pdf->download($fileName);
    }

    private function buildAttendanceReportPayload(Course $course, array $filters): array
    {
        $tz = config('app.timezone', 'Asia/Bangkok');
        $today = now($tz);
        $todayDate = $today->toDateString();
        $todayMonth = $today->format('Y-m');
        $selectedTerm = $this->resolveTerm($course, $filters['term'] ?? null);

        $requestedMonth = trim((string) ($filters['month'] ?? ''));
        if ($requestedMonth === '' && ! empty($filters['date'])) {
            $requestedMonth = \Illuminate\Support\Carbon::parse($filters['date'], $tz)->format('Y-m');
        }
        if ($requestedMonth === '') {
            $requestedMonth = $todayMonth;
        }
        if ($requestedMonth > $todayMonth) {
            $requestedMonth = $todayMonth;
        }

        $monthStartAt = \Illuminate\Support\Carbon::createFromFormat('Y-m', $requestedMonth, $tz)->startOfMonth();
        $monthEndAt = $monthStartAt->copy()->endOfMonth();
        if ($monthEndAt->toDateString() > $todayDate) {
            $monthEndAt = \Illuminate\Support\Carbon::parse($todayDate, $tz)->endOfDay();
        }

        $monthStart = $monthStartAt->toDateString();
        $monthEnd = $monthEndAt->toDateString();
        $reportDate = ! empty($filters['date'])
            ? \Illuminate\Support\Carbon::parse($filters['date'], $tz)->toDateString()
            : $monthEnd;
        if ($reportDate < $monthStart || $reportDate > $monthEnd) {
            $reportDate = $monthEnd;
        }

        $studentData = $this->buildCourseStudentData($course);
        $assignedRooms = $studentData['assignedRooms'];
        $studentsByRoom = $studentData['studentsByRoom'];
        $roomOptions = collect($studentsByRoom->keys())->values();
        $selectedRoom = trim((string) ($filters['room'] ?? ''));

        if ($selectedRoom !== '' && $roomOptions->contains($selectedRoom)) {
            $studentsByRoom = collect([
                $selectedRoom => collect($studentsByRoom->get($selectedRoom, collect())),
            ]);
        } else {
            $selectedRoom = '';
        }

        $visibleStudentIds = $studentsByRoom
            ->flatten(1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $recordsQuery = CourseAttendanceRecord::query()
            ->where('course_id', $course->id)
            ->where('term', $selectedTerm)
            ->whereDate('attendance_date', '>=', $monthStart)
            ->whereDate('attendance_date', '<=', $monthEnd);

        if ($visibleStudentIds->isNotEmpty()) {
            $recordsQuery->whereIn('student_id', $visibleStudentIds->all());
        } else {
            $recordsQuery->whereRaw('1 = 0');
        }

        $records = $recordsQuery->get();

        $attendanceGrid = [];
        $studentStatusSummary = [];
        foreach ($records as $record) {
            $studentId = (int) $record->student_id;
            $dateKey = \Illuminate\Support\Carbon::parse($record->attendance_date, $tz)->toDateString();
            $status = (string) $record->status;

            $attendanceGrid[$studentId][$dateKey] = $status;
            if (! isset($studentStatusSummary[$studentId])) {
                $studentStatusSummary[$studentId] = [
                    'present' => 0,
                    'late' => 0,
                    'leave' => 0,
                    'absent' => 0,
                ];
            }
            if (array_key_exists($status, $studentStatusSummary[$studentId])) {
                $studentStatusSummary[$studentId][$status]++;
            }
        }

        $statusSummary = [
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'absent' => $records->where('status', 'absent')->count(),
        ];

        $monthDates = [];
        $cursor = $monthStartAt->copy()->startOfDay();
        $monthEndDay = $monthEndAt->copy()->startOfDay();
        while ($cursor->lte($monthEndDay)) {
            $monthDates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $reportMonthLabel = $monthStartAt->copy()->addYears(543)->locale('th')->isoFormat('MMMM YYYY');

        return [
            'selectedTerm' => $selectedTerm,
            'reportDate' => $reportDate,
            'reportMonth' => $requestedMonth,
            'reportMonthStart' => $monthStart,
            'reportMonthEnd' => $monthEnd,
            'reportMonthLabel' => $reportMonthLabel,
            'monthDates' => $monthDates,
            'maxReportDate' => $todayDate,
            'maxReportMonth' => $todayMonth,
            'studentsByRoom' => $studentsByRoom,
            'assignedRooms' => $assignedRooms,
            'roomOptions' => $roomOptions,
            'selectedRoom' => $selectedRoom,
            'attendanceGrid' => $attendanceGrid,
            'studentStatusSummary' => $studentStatusSummary,
            'statusSummary' => $statusSummary,
            'recordedCount' => $records->count(),
        ];
    }

    public function storeAttendance(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $data = $request->validate([
            'term' => 'required|in:1,2,summer',
            'attendance_date' => [
                'required',
                'date',
                'after_or_equal:' . $todayDate,
                'before_or_equal:' . $todayDate,
            ],
            'room' => 'nullable|string|max:50',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.status' => 'required|in:present,late,leave,absent',
        ]);
        $term = (string) $data['term'];
        $attendanceDate = $data['attendance_date'];
        $selectedRoom = trim((string) ($data['room'] ?? ''));

        if ($this->findAttendanceHolidayWithGlobal($course->id, $term, $attendanceDate)) {
            return redirect()
                ->route('teacher.courses.attendance', [
                    'course' => $course->id,
                    'term' => $data['term'],
                    'date' => $attendanceDate,
                    'room' => $selectedRoom !== '' ? $selectedRoom : null,
                ])
                ->withErrors([
                    'attendance_date' => 'วันที่นี้ถูกกำหนดเป็นวันหยุดแล้ว จึงไม่สามารถบันทึกการเช็กชื่อได้',
                ]);
        }

        if ($this->findAttendanceHoliday($course->id, $term, $attendanceDate)) {
            return redirect()
                ->route('teacher.courses.attendance', [
                    'course' => $course->id,
                    'term' => $data['term'],
                    'date' => $attendanceDate,
                    'room' => $selectedRoom !== '' ? $selectedRoom : null,
                ])
                ->withErrors([
                    'attendance_date' => 'วันนี้ถูกกำหนดเป็นวันหยุดโดยผู้บริหาร จึงไม่สามารถบันทึกเช็คชื่อได้',
                ]);
        }

        $students = collect($data['students'])->values();

        $studentData = $this->buildCourseStudentData($course);
        $studentsByRoom = $studentData['studentsByRoom'];
        $roomOptions = collect($studentsByRoom->keys())->values();

        if ($selectedRoom !== '' && $roomOptions->contains($selectedRoom)) {
            $studentsByRoom = collect([
                $selectedRoom => collect($studentsByRoom->get($selectedRoom, collect())),
            ]);
        } else {
            $selectedRoom = '';
        }

        $allowedStudentIds = $studentsByRoom
            ->flatten(1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $submittedStudentIds = $students
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($submittedStudentIds->diff($allowedStudentIds)->isNotEmpty()) {
            abort(403);
        }

        $now = now();
        $existingRecords = CourseAttendanceRecord::query()
            ->where('course_id', $course->id)
            ->where('term', $term)
            ->whereDate('attendance_date', $attendanceDate)
            ->when($allowedStudentIds->isNotEmpty(), function ($query) use ($allowedStudentIds) {
                $query->whereIn('student_id', $allowedStudentIds->all());
            })
            ->when($allowedStudentIds->isEmpty(), function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function (array $student) use ($course, $term, $attendanceDate, $now, $existingRecords) {
            $existing = $existingRecords->get((int) $student['student_id']);

            return [
                'course_id' => $course->id,
                'student_id' => (int) $student['student_id'],
                'term' => $term,
                'attendance_date' => $attendanceDate,
                'status' => $student['status'],
                'deduction_points' => $existing ? (float) $existing->deduction_points : 0,
                'note' => $existing ? $existing->note : null,
                'recorded_by' => Auth::id(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        $cleanupQuery = CourseAttendanceRecord::query()
            ->where('course_id', $course->id)
            ->where('term', $term)
            ->whereDate('attendance_date', $attendanceDate);

        if ($selectedRoom !== '') {
            if ($allowedStudentIds->isNotEmpty()) {
                $cleanupQuery->whereIn('student_id', $allowedStudentIds->all());
            } else {
                $cleanupQuery->whereRaw('1 = 0');
            }
        }

        $cleanupQuery
            ->whereNotIn('student_id', $submittedStudentIds->all())
            ->delete();

        CourseAttendanceRecord::query()->upsert(
            $rows->all(),
            ['course_id', 'student_id', 'term', 'attendance_date'],
            ['status', 'deduction_points', 'note', 'recorded_by', 'updated_at']
        );

        return redirect()
            ->route('teacher.courses.attendance', [
                'course' => $course->id,
                'term' => $data['term'],
                'date' => $attendanceDate,
                'room' => $selectedRoom !== '' ? $selectedRoom : null,
            ])
            ->with('status', 'บันทึกข้อมูลการเช็คชื่อเรียบร้อยแล้ว');
    }

    public function deductions(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $filters = $request->validate([
            'term' => 'nullable|in:1,2,summer',
            'date' => 'nullable|date',
            'room' => 'nullable|string|max:50',
        ]);

        $courses = Course::accessibleByTeacher((int) Auth::id())
            ->latest()
            ->get();

        $selectedTerm = $this->resolveTerm($course, $filters['term'] ?? null);
        $attendanceDate = $filters['date']
            ?? now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $gridSlotLimit = $this->resolveDeductionGridSlotLimit($course, $selectedTerm);
        $gridSlotMaxScores = $this->resolveDeductionGridMaxScores($course, $selectedTerm, $gridSlotLimit);

        $studentData = $this->buildCourseStudentData($course);
        $assignedRooms = $studentData['assignedRooms'];
        $studentsByRoom = $studentData['studentsByRoom'];
        $roomOptions = collect($studentsByRoom->keys())->values();
        $selectedRoom = trim((string) ($filters['room'] ?? ''));

        if ($selectedRoom !== '' && $roomOptions->contains($selectedRoom)) {
            $studentsByRoom = collect([
                $selectedRoom => collect($studentsByRoom->get($selectedRoom, collect())),
            ]);
        } else {
            $selectedRoom = '';
        }

        $visibleStudentIds = $studentsByRoom
            ->flatten(1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $recordsQuery = CourseAttendanceRecord::query()
            ->where('course_id', $course->id)
            ->where('term', $selectedTerm)
            ->whereDate('attendance_date', $attendanceDate);

        if ($visibleStudentIds->isNotEmpty()) {
            $recordsQuery->whereIn('student_id', $visibleStudentIds->all());
        } else {
            $recordsQuery->whereRaw('1 = 0');
        }

        $records = $recordsQuery->get();

        $deductionByStudent = $records
            ->keyBy('student_id')
            ->map(function (CourseAttendanceRecord $record) use ($gridSlotLimit, $gridSlotMaxScores) {
                $payload = $this->parseDeductionNotePayload($record->note, $gridSlotLimit, $gridSlotMaxScores);
                $gridPoints = $payload['grid_points'];
                $termScore = (float) ($payload['term_score'] ?? 0);
                $finalExamScore = (float) ($payload['final_exam_score'] ?? 0);
                $totalScore = (float) ($payload['total_score'] ?? 0);
                $attendanceStatus = $this->normalizeAttendanceStatus((string) $record->status);
                $attendanceScore = $this->resolveAttendanceAffectiveScore($attendanceStatus);

                // Backward-compatible fallback: old data had only total deduction.
                if (empty($gridPoints) && (float) $record->deduction_points > 0) {
                    $slotOneMax = max(0, (int) ($gridSlotMaxScores[1] ?? 0));
                    $legacyPoint = max(0, (int) floor((float) $record->deduction_points));
                    if ($slotOneMax > 0 && $legacyPoint > 0) {
                        $gridPoints = [1 => min($slotOneMax, $legacyPoint)];
                        $termScore = $this->normalizePercentScore((float) ($gridPoints[1] ?? 0));
                    }
                }

                if ($totalScore <= 0 && (float) $record->deduction_points > 0) {
                    $totalScore = $this->normalizePercentScore((float) $record->deduction_points);
                }

                $calculatedTotalScore = $this->normalizePercentScore($termScore + $finalExamScore);
                if ($calculatedTotalScore > 0) {
                    $totalScore = $calculatedTotalScore;
                }

                return [
                    'status' => $attendanceStatus,
                    'attendance_score' => $attendanceScore,
                    'deduction_points' => (float) $record->deduction_points,
                    'note' => $payload['note'],
                    'grid_points' => $gridPoints,
                    'term_score' => $termScore,
                    'final_exam_score' => $finalExamScore,
                    'total_score' => $totalScore,
                ];
            })
            ->all();

        $deductedCount = collect($deductionByStudent)
            ->filter(fn (array $item) => (float) ($item['total_score'] ?? 0) > 0)
            ->count();

        $attendanceAffectiveCount = collect($deductionByStudent)
            ->filter(fn (array $item) => (float) ($item['attendance_score'] ?? 0) > 0)
            ->count();

        $attendanceAffectiveTotal = (float) collect($deductionByStudent)
            ->sum(fn (array $item) => (float) ($item['attendance_score'] ?? 0));

        $totalDeduction = (float) collect($deductionByStudent)
            ->sum(fn (array $item) => (float) ($item['total_score'] ?? ($item['deduction_points'] ?? 0)));

        $recordedCount = $records->count();

        $attendanceAffectiveScoreMap = $this->attendanceAffectiveScoreMap();

        return view('teacher.course-deductions', [
            'course' => $course,
            'courses' => $courses,
            'selectedTerm' => $selectedTerm,
            'attendanceDate' => $attendanceDate,
            'studentsByRoom' => $studentsByRoom,
            'assignedRooms' => $assignedRooms,
            'roomOptions' => $roomOptions,
            'selectedRoom' => $selectedRoom,
            'deductionByStudent' => $deductionByStudent,
            'gridSlotLimit' => $gridSlotLimit,
            'gridSlotMaxScores' => $gridSlotMaxScores,
            'totalDeduction' => $totalDeduction,
            'deductedCount' => $deductedCount,
            'recordedCount' => $recordedCount,
            'attendanceAffectiveCount' => $attendanceAffectiveCount,
            'attendanceAffectiveTotal' => $attendanceAffectiveTotal,
            'attendanceAffectiveScoreMap' => $attendanceAffectiveScoreMap,
        ]);
    }

    public function storeDeductions(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term' => 'required|in:1,2,summer',
            'attendance_date' => 'required|date',
            'room' => 'nullable|string|max:50',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.grid_points' => 'nullable|array',
            'students.*.grid_points.*' => 'nullable|integer|min:0|max:100',
            'students.*.final_exam_score' => 'nullable|integer|min:0|max:100',
            'students.*.deduction_points' => 'nullable|integer|min:0|max:100',
            'students.*.note' => 'nullable|string|max:500',
        ]);

        $gridSlotLimit = $this->resolveDeductionGridSlotLimit($course, (string) $data['term']);
        $gridSlotMaxScores = $this->resolveDeductionGridMaxScores($course, (string) $data['term'], $gridSlotLimit);

        $studentData = $this->buildCourseStudentData($course);
        $allowedStudentIds = collect($studentData['students'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $submittedStudentIds = collect($data['students'])
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($submittedStudentIds->diff($allowedStudentIds)->isNotEmpty()) {
            abort(403);
        }

        $existingRecords = CourseAttendanceRecord::query()
            ->where('course_id', $course->id)
            ->where('term', (string) $data['term'])
            ->whereDate('attendance_date', $data['attendance_date'])
            ->get()
            ->keyBy('student_id');

        $now = now();
        $rows = collect($data['students'])
            ->map(function (array $student) use ($course, $data, $now, $existingRecords, $gridSlotLimit, $gridSlotMaxScores) {
                $studentId = (int) $student['student_id'];
                $existing = $existingRecords->get($studentId);
                $status = $this->normalizeAttendanceStatus((string) ($existing->status ?? 'present'));
                $attendanceScore = $this->resolveAttendanceAffectiveScore($status);

                $gridPoints = $this->normalizeDeductionGridPoints(
                    $student['grid_points'] ?? [],
                    $gridSlotLimit,
                    $gridSlotMaxScores
                );
                $termScore = $this->normalizePercentScore((float) collect($gridPoints)->sum());
                $finalExamScore = max(0, min(100, (int) ($student['final_exam_score'] ?? 0)));
                $maxFinalExamScore = $this->normalizePercentScore(max(0, 100 - $termScore));
                $finalExamScore = min($finalExamScore, $maxFinalExamScore);
                $overallScore = $this->normalizePercentScore($termScore + $finalExamScore);
                $deduction = $overallScore > 0
                    ? $overallScore
                    : (float) ($student['deduction_points'] ?? 0);
                $note = trim((string) ($student['note'] ?? ''));
                $storedNote = $this->composeDeductionNotePayload(
                    $note,
                    $gridPoints,
                    $termScore,
                    $attendanceScore,
                    $finalExamScore,
                    $overallScore,
                    $gridSlotLimit,
                    $gridSlotMaxScores
                );

                if (! $existing && $deduction <= 0 && $storedNote === null) {
                    return null;
                }

                return [
                    'course_id' => $course->id,
                    'student_id' => $studentId,
                    'term' => (string) $data['term'],
                    'attendance_date' => $data['attendance_date'],
                    'status' => $existing->status ?? 'present',
                    'deduction_points' => $deduction,
                    'note' => $storedNote,
                    'recorded_by' => Auth::id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values();

        if ($rows->isNotEmpty()) {
            CourseAttendanceRecord::query()->upsert(
                $rows->all(),
                ['course_id', 'student_id', 'term', 'attendance_date'],
                ['status', 'deduction_points', 'note', 'recorded_by', 'updated_at']
            );
        }

        return redirect()
            ->route('teacher.courses.deductions', [
                'course' => $course->id,
                'term' => $data['term'],
                'date' => $data['attendance_date'],
                'room' => $data['room'] ?? null,
            ])
            ->with('status', 'บันทึกการตัดคะแนนเรียบร้อยแล้ว');
<<<<<<< HEAD
=======
    }

    private function findAttendanceHolidayWithGlobal(int $courseId, string $term, string $attendanceDate): ?CourseAttendanceHoliday
    {
        if ($this->isWeekendDate($attendanceDate)) {
            return new CourseAttendanceHoliday([
                'holiday_date' => $attendanceDate,
                'holiday_name' => \Illuminate\Support\Carbon::parse($attendanceDate)->isSaturday() ? 'วันเสาร์' : 'วันอาทิตย์',
                'note' => 'เสาร์-อาทิตย์เป็นวันหยุดอัตโนมัติ',
            ]);
        }

        $courseHoliday = $this->findAttendanceHoliday($courseId, $term, $attendanceDate);

        if ($courseHoliday) {
            return $courseHoliday;
        }

        return CourseAttendanceHoliday::query()
            ->whereNull('course_id')
            ->whereNull('term')
            ->whereDate('holiday_date', $attendanceDate)
            ->first();
    }

    private function isWeekendDate(string $date): bool
    {
        return \Illuminate\Support\Carbon::parse($date)->isWeekend();
>>>>>>> 10fffd100439c08823ba3f31a75f40a6037a822a
    }

    private function findAttendanceHoliday(int $courseId, string $term, string $attendanceDate): ?CourseAttendanceHoliday
    {
        return CourseAttendanceHoliday::query()
            ->where('course_id', $courseId)
            ->where('term', $term)
            ->whereDate('holiday_date', $attendanceDate)
            ->first();
    }

    public function export(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $selectedTerm = $this->resolveTerm($course, $request->input('term'));
        $payload = $this->buildCoursePayload($course, $selectedTerm);

        $fontPath = strtr(storage_path('fonts'), '\\', '/');
        $fontCache = strtr(storage_path('fonts/cache'), '\\', '/');

        if (! is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }

        $thSarabunRegularPath = storage_path('fonts/THSarabunNew-Regular.ttf');
        $thSarabunBoldPath = storage_path('fonts/THSarabunNew-Bold.ttf');
        $thSarabunItalicPath = storage_path('fonts/THSarabunNew-Italic.ttf');
        $thSarabunBoldItalicPath = storage_path('fonts/THSarabunNew-BoldItalic.ttf');
        $leelaRegularPath = "{$fontPath}/LeelawUI.ttf";
        $leelaBoldPath = "{$fontPath}/LeelaUIb.ttf";

        $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => base_path(),
                'fontDir' => $fontPath,
                'fontCache' => $fontCache,
                'tempDir' => $fontCache,
                'defaultFont' => 'THSarabunNew',
                'enable_font_subsetting' => false,
            ])
            ->loadView('teacher.course-detail-pdf', array_merge($payload, [
                'course' => $course,
                'selectedTerm' => $selectedTerm,
                'teacher' => $request->user(),
            ]));

        // Register fonts explicitly so Dompdf embeds Thai glyphs correctly
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
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'normal',
        ], $leelaRegularPath);
        $metrics->registerFont([
            'family' => 'LeelawUI',
            'style' => 'normal',
            'weight' => 'bold',
        ], $leelaBoldPath);

        return $pdf->download('course-'.$course->id.'-term-'.$selectedTerm.'.pdf');
    }


    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        $rooms = $course->rooms ?? [];

        return view('teacher.course-edit', compact('course', 'rooms'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'grade'       => 'required|string|max:20',
            'rooms'       => 'required|array|min:1',
            'rooms.*'     => 'string|max:20',
            'term'        => 'nullable|in:1,2,summer',
            'year'        => ['nullable', 'integer', 'min:1', $this->yearNotInPastRule()],
            'description' => 'nullable|string|max:5000',
            'assignment_cap' => 'nullable|integer|min:1|max:100',
        ]);

        $course->update([
            'name'        => $validated['name'],
            'grade'       => $validated['grade'],
            'rooms'       => $validated['rooms'],
            'term'        => $validated['term'] ?? null,
            'year'        => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
            'assignment_cap' => $validated['assignment_cap'] ?? ($course->assignment_cap ?? 70),
        ]);

        return redirect()
            ->route('course.detail', $course)
            ->with('status', 'อัปเดตหลักสูตรเรียบร้อยแล้ว');
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        $course->delete();

        return redirect()
            ->route('teacher.course-create')
            ->with('status', 'ลบหลักสูตรเรียบร้อยแล้ว');
    }

    protected function authorizeCourse(Course $course): void
    {
        $todayDate = now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        abort_unless($course->isTeacherResponsible((int) Auth::id(), $todayDate), 403);
    }

    protected function authorizeVisibleCourse(Course $course): void
    {
        abort_unless(
            Course::query()
                ->whereKey($course->id)
                ->visibleToTeacher((int) Auth::id())
                ->exists(),
            403
        );
    }

    protected function resolveTerm(Course $course, $input): string
    {
        $selectedTerm = in_array((string) $input, ['1', '2', 'summer'], true)
            ? (string) $input
            : (string) ($course->term ?? '1');

        return $selectedTerm;
    }

    protected function buildCoursePayload(Course $course, string $selectedTerm): array
    {
        $hours = collect($course->teaching_hours ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $lessons = collect($course->lessons ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $assignments = collect($course->assignments ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $assignmentCap = $course->assignment_cap !== null
            ? (float) $course->assignment_cap
            : 70.0;
        $assignmentTotal = $assignments->sum(fn ($item) => $item['score'] ?? 0);
        $assignmentRemaining = max(0, $assignmentCap - $assignmentTotal);

        $lessonCapacity = [];
        $hourTargets = $hours->groupBy('category')->map(fn ($group) => (int) round($group->sum('hours')));
        $lessonUsed = $lessons->groupBy('category')->map(fn ($group) => (int) round($group->sum('hours')));

        foreach ($hourTargets as $category => $allowedHours) {
            $usedHours = $lessonUsed[$category] ?? 0;
            $remaining = max(0, $allowedHours - $usedHours);
            $lessonCapacity[$category] = [
                'allowed'   => $allowedHours,
                'used'      => $usedHours,
                'remaining' => $remaining,
            ];
        }

        $lessonAllowedTotal = (int) round($hourTargets->sum());
        $lessonUsedTotal = (int) round($lessonUsed->sum());
        $lessonRemainingTotal = max(0, $lessonAllowedTotal - $lessonUsedTotal);

        return [
            'hours' => $hours,
            'lessons' => $lessons,
            'assignments' => $assignments,
            'assignmentCap' => $assignmentCap,
            'assignmentTotal' => $assignmentTotal,
            'assignmentRemaining' => $assignmentRemaining,
            'lessonCapacity' => $lessonCapacity,
            'lessonAllowedTotal' => $lessonAllowedTotal,
            'lessonUsedTotal' => $lessonUsedTotal,
            'lessonRemainingTotal' => $lessonRemainingTotal,
        ];
    }

    private function redirectToCourseDetailSection(Course $course, ?string $term, string $section)
    {
        $params = ['course' => $course];
        $normalizedTerm = in_array((string) $term, ['1', '2', 'summer'], true)
            ? (string) $term
            : (string) ($course->term ?? '1');
        $params['term'] = $normalizedTerm;

        return redirect()->to(route('course.detail', $params) . '#' . ltrim($section, '#'));
    }

    private function buildCourseStudentData(Course $course): array
    {
        $assignedRooms = collect($course->rooms ?? [])
            ->map(fn ($room) => $this->normalizeRoomValue($room))
            ->filter()
            ->values();
        $assignedRoomLookup = $this->buildAssignedRoomLookup($assignedRooms);
        $assignedRoomFilters = $this->buildAssignedRoomFilterValues($assignedRooms);
        $hasClassroomCol = Schema::hasColumn('students', 'classroom');

        $studentsQuery = Student::query();
        if ($assignedRooms->isNotEmpty()) {
            $studentsQuery->where(function ($q) use ($assignedRoomFilters, $hasClassroomCol) {
                if ($hasClassroomCol) {
                    $q->whereIn('classroom', $assignedRoomFilters)
                        ->orWhereIn('room', $assignedRoomFilters);
                } else {
                    $q->whereIn('room', $assignedRoomFilters);
                }
            });
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }

        $students = $studentsQuery
            ->when($hasClassroomCol, fn ($q) => $q->orderBy('classroom'))
            ->orderBy('student_code')
            ->get()
            ->map(function ($student) use ($hasClassroomCol, $assignedRoomLookup) {
                $rawRoom = $this->normalizeRoomValue(
                    $hasClassroomCol ? ($student->classroom ?? $student->room ?? null) : ($student->room ?? null)
                );
                $student->room_normalized = $this->resolveAssignedRoomLabel($rawRoom, $assignedRoomLookup);
                return $student;
            });

        $studentsByRoom = $students
            ->groupBy(fn ($s) => $s->room_normalized ?? '-');

        $orderedStudentsByRoom = collect();
        foreach ($assignedRooms as $room) {
            $orderedStudentsByRoom->put($room, collect($studentsByRoom->get($room, collect())));
        }
        foreach ($studentsByRoom as $room => $group) {
            if (! $orderedStudentsByRoom->has($room)) {
                $orderedStudentsByRoom->put($room, $group);
            }
        }

        return [
            'assignedRooms' => $assignedRooms,
            'students' => $students,
            'studentsByRoom' => $orderedStudentsByRoom,
        ];
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
                $aliases->push("\u{0E1B}." . $grade . '/' . $roomNo);
                $aliases->push('P.' . $grade . '/' . $roomNo);
                $aliases->push('U.' . $grade . '/' . $roomNo);
                $aliases->push("\u{0E1B}\u{0E23}\u{0E30}\u{0E16}\u{0E21}\u{0E28}\u{0E36}\u{0E01}\u{0E29}\u{0E32} " . $grade . '/' . $roomNo);
                $aliases->push("\u{0E1B}\u{0E23}\u{0E30}\u{0E16}\u{0E21}\u{0E28}\u{0E36}\u{0E01}\u{0E29}\u{0E32}" . $grade . '/' . $roomNo);
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

        $normalizedText = strtr($text, [
            "\u{0E50}" => '0', "\u{0E51}" => '1', "\u{0E52}" => '2', "\u{0E53}" => '3', "\u{0E54}" => '4',
            "\u{0E55}" => '5', "\u{0E56}" => '6', "\u{0E57}" => '7', "\u{0E58}" => '8', "\u{0E59}" => '9',
        ]);
        $normalizedCompact = preg_replace('/\s+/u', '', $normalizedText) ?? $normalizedText;

        if (preg_match('/^(\d)(\d{2})$/', $normalizedCompact, $matches) === 1) {
            return [
                'grade' => (int) $matches[1],
                'room' => (int) $matches[2],
            ];
        }

        $normalizedPatterns = [
            '/^(?:u|p|\x{0E1B}|\x{0E21})\.?(\d{1,2})\/(\d{1,2})$/iu',
            '/^\x{0E1B}\x{0E23}\x{0E30}\x{0E16}\x{0E21}\x{0E28}\x{0E36}\x{0E01}\x{0E29}\x{0E32}(\d{1,2})\/(\d{1,2})$/u',
        ];

        foreach ($normalizedPatterns as $pattern) {
            if (preg_match($pattern, $normalizedCompact, $matches) === 1) {
                return [
                    'grade' => (int) $matches[1],
                    'room' => (int) $matches[2],
                ];
            }
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
            '/^(?:u|p)\.?(\d{1,2})\/(\d{1,2})$/iu',
            '/^(?:ป|ม)\.?(\d{1,2})\/(\d{1,2})$/u',
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
            "\u{0E50}" => '0', "\u{0E51}" => '1', "\u{0E52}" => '2', "\u{0E53}" => '3', "\u{0E54}" => '4',
            "\u{0E55}" => '5', "\u{0E56}" => '6', "\u{0E57}" => '7', "\u{0E58}" => '8', "\u{0E59}" => '9',
        ]);

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

    private function normalizeRoomValue($item): ?string
    {
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

    private function normalizeAttendanceStatus(?string $status): string
    {
        $normalized = strtolower(trim((string) ($status ?? '')));

        return in_array($normalized, ['present', 'late', 'leave', 'absent'], true)
            ? $normalized
            : 'present';
    }

    private function attendanceAffectiveScoreMap(): array
    {
        return [
            'present' => 0.0,
            'late' => 0.5,
            'leave' => 1.0,
            'absent' => 2.0,
        ];
    }

    private function resolveAttendanceAffectiveScore(?string $status): float
    {
        $normalized = $this->normalizeAttendanceStatus($status);
        $scoreMap = $this->attendanceAffectiveScoreMap();

        return round((float) ($scoreMap[$normalized] ?? 0), 2);
    }

    private function resolveDeductionGridSlotLimit(Course $course, string $selectedTerm): int
    {
        $assignmentCount = collect($course->assignments ?? [])
            ->filter(fn ($item) => (string) ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->count();

        return max(1, (int) $assignmentCount);
    }

    private function resolveDeductionGridMaxScores(Course $course, string $selectedTerm, int $slotLimit): array
    {
        $assignments = collect($course->assignments ?? [])
            ->filter(fn ($item) => (string) ($item['term'] ?? (string) ($course->term ?? '1')) === $selectedTerm)
            ->values();

        $resolvedLimit = max(1, $slotLimit);
        $maxScores = [];

        for ($slot = 1; $slot <= $resolvedLimit; $slot++) {
            $item = $assignments->get($slot - 1);
            $rawScore = is_array($item) ? ($item['score'] ?? null) : null;
            $numericScore = is_numeric($rawScore) ? (float) $rawScore : 0;
            $maxScores[$slot] = max(0, min(100, (int) floor($numericScore)));
        }

        return $maxScores;
    }

    private function normalizeDeductionGridPoints($rawGridPoints, int $maxSlots = 8, array $slotMaxScores = []): array
    {
        if (! is_array($rawGridPoints)) {
            return [];
        }

        $normalized = [];

        foreach ($rawGridPoints as $slot => $value) {
            $slotNo = (int) $slot;
            if ($slotNo < 1 || $slotNo > $maxSlots) {
                continue;
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $numeric = (int) floor((float) $value);
            if ($numeric <= 0) {
                continue;
            }

            $slotMax = array_key_exists($slotNo, $slotMaxScores)
                ? max(0, (int) $slotMaxScores[$slotNo])
                : 100;
            if ($slotMax <= 0) {
                continue;
            }

            $normalized[$slotNo] = min($slotMax, $numeric);
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizePercentScore($rawScore): float
    {
        if ($rawScore === '' || $rawScore === null) {
            return 0.0;
        }

        $score = (float) $rawScore;
        if ($score <= 0) {
            return 0.0;
        }

        return round(min(100, $score), 2);
    }

    private function parseDeductionNotePayload(?string $rawNote, int $maxSlots = 8, array $slotMaxScores = []): array
    {
        $raw = trim((string) ($rawNote ?? ''));

        if ($raw === '') {
            return [
                'note' => '',
                'grid_points' => [],
                'term_score' => 0.0,
                'attendance_score' => 0.0,
                'final_exam_score' => 0.0,
                'total_score' => 0.0,
            ];
        }

        $decoded = json_decode($raw, true);
        if (
            json_last_error() === JSON_ERROR_NONE
            && is_array($decoded)
            && (
                array_key_exists('grid_points', $decoded)
                || array_key_exists('term_score', $decoded)
                || array_key_exists('attendance_score', $decoded)
                || array_key_exists('final_exam_score', $decoded)
                || array_key_exists('total_score', $decoded)
                || array_key_exists('note', $decoded)
            )
        ) {
            $gridPoints = $this->normalizeDeductionGridPoints(
                $decoded['grid_points'] ?? [],
                $maxSlots,
                $slotMaxScores
            );
            $termScore = array_key_exists('term_score', $decoded)
                ? $this->normalizePercentScore($decoded['term_score'])
                : $this->normalizePercentScore((float) collect($gridPoints)->sum());
            $attendanceScore = array_key_exists('attendance_score', $decoded)
                ? $this->normalizePercentScore($decoded['attendance_score'])
                : 0.0;
            $finalExamScore = $this->normalizePercentScore($decoded['final_exam_score'] ?? 0);
            $maxFinalExamScore = $this->normalizePercentScore(max(0, 100 - $termScore));
            $finalExamScore = min($finalExamScore, $maxFinalExamScore);
            $totalScore = array_key_exists('total_score', $decoded)
                ? $this->normalizePercentScore($decoded['total_score'])
                : $this->normalizePercentScore($termScore + $finalExamScore);

            return [
                'note' => trim((string) ($decoded['note'] ?? '')),
                'grid_points' => $gridPoints,
                'term_score' => $termScore,
                'attendance_score' => $attendanceScore,
                'final_exam_score' => $finalExamScore,
                'total_score' => $totalScore,
            ];
        }

        return [
            'note' => $raw,
            'grid_points' => [],
            'term_score' => 0.0,
            'attendance_score' => 0.0,
            'final_exam_score' => 0.0,
            'total_score' => 0.0,
        ];
    }

    private function composeDeductionNotePayload(
        string $note,
        array $gridPoints,
        float $termScore = 0,
        float $attendanceScore = 0,
        float $finalExamScore = 0,
        float $totalScore = 0,
        int $maxSlots = 8,
        array $slotMaxScores = []
    ): ?string
    {
        $cleanNote = trim($note);
        $normalizedGrid = $this->normalizeDeductionGridPoints($gridPoints, $maxSlots, $slotMaxScores);
        $normalizedTerm = $termScore > 0
            ? $this->normalizePercentScore($termScore)
            : $this->normalizePercentScore((float) collect($normalizedGrid)->sum());
        $normalizedAttendance = $this->normalizePercentScore($attendanceScore);
        $normalizedFinalExam = $this->normalizePercentScore($finalExamScore);
        $maxFinalExamScore = $this->normalizePercentScore(max(0, 100 - $normalizedTerm));
        $normalizedFinalExam = min($normalizedFinalExam, $maxFinalExamScore);
        $normalizedTotal = $totalScore > 0
            ? $this->normalizePercentScore($totalScore)
            : $this->normalizePercentScore($normalizedTerm + $normalizedFinalExam);

        $hasStructuredScores = ! empty($normalizedGrid)
            || $normalizedTerm > 0
            || $normalizedAttendance > 0
            || $normalizedFinalExam > 0
            || $normalizedTotal > 0;

        if (! $hasStructuredScores) {
            return $cleanNote === '' ? null : $cleanNote;
        }

        $payload = [
            'note' => $cleanNote === '' ? null : $cleanNote,
            'grid_points' => $normalizedGrid,
            'term_score' => $normalizedTerm,
            'attendance_score' => $normalizedAttendance,
            'final_exam_score' => $normalizedFinalExam,
            'total_score' => $normalizedTotal,
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            return $cleanNote === '' ? null : $cleanNote;
        }

        return $encoded;
    }

    private function yearNotInPastRule(): \Closure
    {
        $currentYearAd = now(config('app.timezone', 'Asia/Bangkok'))->year;
        $currentYearBe = $currentYearAd + 543;

        return function ($attribute, $value, $fail) use ($currentYearBe) {
            if ($value === null || $value === '') {
                return;
            }

            $year = (int) $value;
            if ($year < 2400) {
                $fail('กรุณากรอกปีการศึกษาเป็น พ.ศ. (เช่น '.$currentYearBe.')');
                return;
            }

            if ($year !== $currentYearBe) {
                $fail("ปีการศึกษาต้องเป็นปีปัจจุบันเท่านั้น (พ.ศ. {$currentYearBe})");
            }
        };
    }

    public function storeTeachingHour(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2,summer',
            'category' => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'unit'     => 'required|string|max:50',
            'note'     => 'nullable|string|max:1000',
        ]);

        $hours = $course->teaching_hours ?? [];
        $hours[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'category' => $data['category'],
            'hours'    => $data['hours'],
            'unit'     => $data['unit'],
            'note'     => $data['note'] ?? null,
        ];

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'บันทึกชั่วโมงการสอนเรียบร้อยแล้ว');
    }

    public function updateTeachingHour(Request $request, Course $course, string $hour)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'term'     => 'required|in:1,2,summer',
            'category' => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'unit'     => 'required|string|max:50',
            'note'     => 'nullable|string|max:1000',
        ]);

        $hours = $course->teaching_hours ?? [];
        $updated = false;

        foreach ($hours as $index => $item) {
            if (($item['id'] ?? null) === $hour) {
                $hours[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'category' => $data['category'],
                    'hours'    => $data['hours'],
                    'unit'     => $data['unit'],
                    'note'     => $data['note'] ?? null,
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return back()->withErrors(['hour' => 'ไม่พบข้อมูลชั่วโมงที่ต้องการแก้ไข']);
        }

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'อัปเดตชั่วโมงสอนเรียบร้อยแล้ว');
    }

    public function destroyTeachingHour(Course $course, string $hour)
    {
        $this->authorizeCourse($course);

        $hours = collect($course->teaching_hours ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $hour)
            ->values()
            ->all();

        $course->update(['teaching_hours' => $hours]);

        return back()->with('status', 'ลบชั่วโมงการสอนแล้ว');
    }

    public function storeLesson(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validator = Validator::make($request->all(), [
            'term'     => 'required|in:1,2,summer',
            'category' => 'required|string|max:255',
            'title'    => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'period'   => 'nullable|string|max:100',
            'details'  => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return $this->redirectToCourseDetailSection($course, $request->input('term'), 'lessons')
                ->withErrors($validator)
                ->withInput()
                ->with('status_context', 'lessons');
        }
        $data = $validator->validated();

        try {
            $this->guardLessonHours($course, $data['term'], $data['category'], $data['hours']);
        } catch (ValidationException $exception) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'lessons')
                ->withErrors($exception->errors())
                ->withInput()
                ->with('status_context', 'lessons');
        }

        $lessons = $course->lessons ?? [];
        $lessons[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'category' => $data['category'],
            'title'    => $data['title'],
            'hours'    => $data['hours'],
            'period'   => $data['period'] ?? null,
            'details'  => $data['details'] ?? null,
            'created_at' => now(config('app.timezone'))->toDateTimeString(),
        ];

        $course->update(['lessons' => $lessons]);

        return $this->redirectToCourseDetailSection($course, $data['term'], 'lessons')
            ->with('status', 'บันทึกหัวข้อเนื้อหาเรียบร้อยแล้ว')
            ->with('status_context', 'lessons');
    }

    public function destroyLesson(Course $course, string $lesson)
    {
        $this->authorizeCourse($course);
        $term = request('term');

        $lessons = collect($course->lessons ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $lesson)
            ->values()
            ->all();

        $course->update(['lessons' => $lessons]);

        return $this->redirectToCourseDetailSection($course, $term, 'lessons')
            ->with('status', 'ลบหัวข้อเนื้อหาแล้ว')
            ->with('status_context', 'lessons');
    }

    public function updateLesson(Request $request, Course $course, string $lesson)
    {
        $this->authorizeCourse($course);

        $validator = Validator::make($request->all(), [
            'term'     => 'required|in:1,2,summer',
            'category' => 'required|string|max:255',
            'title'    => 'required|string|max:255',
            'hours'    => 'required|integer|min:1',
            'period'   => 'nullable|string|max:100',
            'details'  => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return $this->redirectToCourseDetailSection($course, $request->input('term'), 'lessons')
                ->withErrors($validator)
                ->withInput()
                ->with('status_context', 'lessons');
        }
        $data = $validator->validated();

        $lessons = $course->lessons ?? [];
        $updated = false;

        foreach ($lessons as $index => $item) {
            if (($item['id'] ?? null) === $lesson) {
                try {
                    $this->guardLessonHours(
                        $course,
                        $data['term'],
                        $data['category'],
                        $data['hours'],
                        (int) ($item['hours'] ?? 0),
                        (string) ($item['category'] ?? '')
                    );
                } catch (ValidationException $exception) {
                    return $this->redirectToCourseDetailSection($course, $data['term'], 'lessons')
                        ->withErrors($exception->errors())
                        ->withInput()
                        ->with('status_context', 'lessons');
                }

                $lessons[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'category' => $data['category'],
                    'title'    => $data['title'],
                    'hours'    => $data['hours'],
                    'period'   => $data['period'] ?? null,
                    'details'  => $data['details'] ?? null,
                    'updated_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'lessons')
                ->withErrors(['lesson' => 'ไม่พบหัวข้อบทเรียนที่ต้องการอัปเดต'])
                ->withInput()
                ->with('status_context', 'lessons');
        }

        $course->update(['lessons' => $lessons]);

        return $this->redirectToCourseDetailSection($course, $data['term'], 'lessons')
            ->with('status', 'อัปเดตหัวข้อเรียบร้อยแล้ว')
            ->with('status_context', 'lessons');
    }

    private function guardLessonHours(
        Course $course,
        string $term,
        string $category,
        int $incomingHours,
        int $existingHours = 0,
        string $existingCategory = ''
    ): void {
        $targetHours = (int) round(collect($course->teaching_hours ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $term)
            ->where('category', $category)
            ->sum('hours'));

        if ($targetHours <= 0) {
            throw ValidationException::withMessages([
                'lesson' => 'ชั่วโมงสอนหมวดนี้ยังไม่ถูกกำหนดในภาคเรียนที่เลือก',
            ]);
        }

        $usedHours = (int) round(collect($course->lessons ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $term)
            ->filter(fn ($item) => ($item['category'] ?? '') === $category)
            ->sum('hours'));

        // remove existing hours when editing same category
        if ($existingCategory === $category) {
            $usedHours -= $existingHours;
        }

        if ($usedHours + $incomingHours > $targetHours) {
            throw ValidationException::withMessages([
                'lesson' => 'ชั่วโมงรวมเกินกว่าที่กำหนดในหมวดนี้',
            ]);
        }
    }

    public function storeAssignment(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validator = Validator::make($request->all(), [
            'term'     => 'required|in:1,2,summer',
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date|after_or_equal:today',
            'score'    => 'nullable|integer|min:0',
            'notes'    => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return $this->redirectToCourseDetailSection($course, $request->input('term'), 'assignments')
                ->withErrors($validator)
                ->withInput()
                ->with('status_context', 'assignments');
        }
        $data = $validator->validated();

        $assignments = $course->assignments ?? [];

        $currentTotal = collect($assignments)
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $data['term'])
            ->sum(fn ($item) => $item['score'] ?? 0);

        $newScore = isset($data['score']) ? (int) $data['score'] : 0;
        $assignmentCap = (int) ($course->assignment_cap ?? 70);
        if ($currentTotal >= $assignmentCap) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors([
                    'score' => 'คะแนนเต็มรวมครบแล้ว (เพดาน ' . number_format($assignmentCap, 0) . ' | รวมปัจจุบัน ' . number_format($currentTotal, 0) . ')',
                ])
                ->withInput()
                ->with('status_context', 'assignments');
        }
        if (($currentTotal + $newScore) > $assignmentCap) {
            $remaining = max(0, $assignmentCap - $currentTotal);
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors([
                    'score' => 'คะแนนเกินกำหนด: เหลือได้อีก ' . number_format($remaining, 0) . ' จากเพดาน ' . number_format($assignmentCap, 0),
                ])
                ->withInput()
                ->with('status_context', 'assignments');
        }

        $assignments[] = [
            'id'       => (string) Str::uuid(),
            'term'     => $data['term'],
            'title'    => $data['title'],
            'due_date' => $data['due_date'] ?? null,
            'score'    => isset($data['score']) ? (int) $data['score'] : null,
            'notes'    => $data['notes'] ?? null,
            'created_at' => now(config('app.timezone'))->toDateTimeString(),
        ];

        $course->update(['assignments' => $assignments]);

        return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
            ->with('status', 'บันทึกการบ้านเรียบร้อยแล้ว')
            ->with('status_context', 'assignments');
    }

    public function updateAssignment(Request $request, Course $course, string $assignment)
    {
        $this->authorizeCourse($course);

        $validator = Validator::make($request->all(), [
            'term'     => 'required|in:1,2,summer',
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date|after_or_equal:today',
            'score'    => 'nullable|integer|min:0',
            'notes'    => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return $this->redirectToCourseDetailSection($course, $request->input('term'), 'assignments')
                ->withErrors($validator)
                ->withInput()
                ->with('status_context', 'assignments');
        }
        $data = $validator->validated();

        $assignments = $course->assignments ?? [];
        $updated = false;
        $targetItem = null;

        foreach ($assignments as $index => $item) {
            if (($item['id'] ?? null) === $assignment) {
                $targetItem = $item;
                break;
            }
        }

        if (! $targetItem) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors(['assignment' => 'ไม่พบการบ้านที่ต้องการแก้ไข'])
                ->withInput()
                ->with('status_context', 'assignments');
        }

        $currentTotal = collect($assignments)
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $data['term'])
            ->sum(fn ($item) => $item['score'] ?? 0);

        // ถ้าอยู่ภาคเรียนเดิม ให้หักคะแนนเดิมออกก่อนคำนวณ
        if (($targetItem['term'] ?? null) === $data['term']) {
            $currentTotal -= $targetItem['score'] ?? 0;
        }

        $newScore = isset($data['score']) ? (int) $data['score'] : 0;
        $assignmentCap = (int) ($course->assignment_cap ?? 70);
        if ($currentTotal >= $assignmentCap) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors([
                    'score' => 'คะแนนเต็มรวมครบแล้ว (เพดาน ' . number_format($assignmentCap, 0) . ' | รวมปัจจุบัน ' . number_format($currentTotal, 0) . ')',
                ])
                ->withInput()
                ->with('status_context', 'assignments');
        }
        if (($currentTotal + $newScore) > $assignmentCap) {
            $remaining = max(0, $assignmentCap - $currentTotal);
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors([
                    'score' => 'คะแนนเกินกำหนด: เหลือได้อีก ' . number_format($remaining, 0) . ' จากเพดาน ' . number_format($assignmentCap, 0),
                ])
                ->withInput()
                ->with('status_context', 'assignments');
        }

        foreach ($assignments as $index => $item) {
            if (($item['id'] ?? null) === $assignment) {
                $assignments[$index] = array_merge($item, [
                    'term'     => $data['term'],
                    'title'    => $data['title'],
                    'due_date' => $data['due_date'] ?? null,
                    'score'    => isset($data['score']) ? (int) $data['score'] : null,
                    'notes'    => $data['notes'] ?? null,
                    'updated_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
                ->withErrors(['assignment' => 'ไม่พบการบ้านที่ต้องการแก้ไข'])
                ->withInput()
                ->with('status_context', 'assignments');
        }

        $course->update(['assignments' => $assignments]);

        return $this->redirectToCourseDetailSection($course, $data['term'], 'assignments')
            ->with('status', 'อัปเดตการบ้านเรียบร้อยแล้ว')
            ->with('status_context', 'assignments');
    }

    public function destroyAssignment(Course $course, string $assignment)
    {
        $this->authorizeCourse($course);
        $term = request('term');

        $assignments = collect($course->assignments ?? [])
            ->reject(fn ($item) => ($item['id'] ?? null) === $assignment)
            ->values()
            ->all();

        $course->update(['assignments' => $assignments]);

        return $this->redirectToCourseDetailSection($course, $term, 'assignments')
            ->with('status', 'ลบการบ้านแล้ว')
            ->with('status_context', 'assignments');
    }

    public function updateAssignmentCap(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'assignment_cap' => 'required|integer|min:1|max:100',
        ]);

        $term = $this->resolveTerm($course, $request->input('term'));
        $currentTotal = collect($course->assignments ?? [])
            ->filter(fn ($item) => ($item['term'] ?? (string) ($course->term ?? '1')) === $term)
            ->sum(fn ($item) => $item['score'] ?? 0);

        if ($data['assignment_cap'] < $currentTotal) {
            return back()->withErrors([
                'assignment_cap' => 'คะแนนเก็บที่ตั้งไว้ต่ำกว่าคะแนนรวมปัจจุบัน (รวม ' . number_format($currentTotal, 0) . ')',
            ]);
        }

        $course->update(['assignment_cap' => $data['assignment_cap']]);

        return back()->with('status', 'อัปเดตเพดานคะแนนเก็บเรียบร้อยแล้ว');
    }
}
