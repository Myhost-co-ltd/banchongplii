<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseAttendanceHoliday;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\TeacherDashboardSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectorController extends Controller
{
    public function dashboard(TeacherDashboardSummaryService $teacherDashboardSummary)
    {
        $studentCount = Student::count();
        $teacherSummary = $teacherDashboardSummary->build();
        $teacherDirectoryAll = $teacherSummary['teacherDirectoryAll'];
        $teacherCount = $teacherSummary['teacherCount'];
        $homeroomTeachers = $teacherSummary['teacherUsers'];

        $normalizeRoom = function ($room): ?string {
            if (is_array($room)) {
                if (array_key_exists('room', $room)) {
                    $room = $room['room'];
                } elseif (array_key_exists('name', $room)) {
                    $room = $room['name'];
                } elseif (array_key_exists(2, $room)) {
                    $room = $room[2];
                } else {
                    $room = reset($room);
                }
            }

            if (is_object($room)) {
                if (isset($room->room)) {
                    $room = $room->room;
                } elseif (isset($room->name)) {
                    $room = $room->name;
                } else {
                    $room = method_exists($room, '__toString') ? (string) $room : null;
                }
            }

            if ($room === null) {
                return null;
            }

            $normalized = trim((string) $room);
            return $normalized !== '' ? $normalized : null;
        };

        $hasClassroomCol = Schema::hasColumn('students', 'classroom');
        $studentColumns = $hasClassroomCol
            ? ['id', 'student_code', 'title', 'first_name', 'last_name', 'room', 'classroom']
            : ['id', 'student_code', 'title', 'first_name', 'last_name', 'room'];

        $studentsByRoom = Student::query()
            ->select($studentColumns)
            ->orderBy('student_code')
            ->get()
            ->map(function ($student) use ($normalizeRoom) {
                $student->room_normalized = $normalizeRoom($student->classroom ?? $student->room ?? null);
                return $student;
            })
            ->groupBy(fn ($student) => $student->room_normalized ?? 'à¹„à¸¡à¹ˆà¸£à¸°à¸šà¸¸à¸«à¹‰à¸­à¸‡')
            ->sortKeys();

        $roomOptions = $studentsByRoom
            ->reject(fn ($_students, $room) => $room === 'à¹„à¸¡à¹ˆà¸£à¸°à¸šà¸¸à¸«à¹‰à¸­à¸‡')
            ->keys()
            ->values();
        $classCount = $roomOptions->count();

        $studentsByRoomPayload = $studentsByRoom
            ->map(function ($students) {
                return collect($students)
                    ->map(function ($student) {
                        $fullName = trim(($student->title ? $student->title . ' ' : '') . $student->first_name . ' ' . $student->last_name);

                        return [
                            'student_code' => $student->student_code,
                            'name' => $fullName !== '' ? $fullName : '-',
                        ];
                    })
                    ->values()
                    ->all();
            })
            ->all();

        $normalizeMajor = function (?string $value): ?string {
            $decoded = $this->decodeLegacyThai((string) ($value ?? ''));
            $normalized = trim(preg_replace('/\s+/u', ' ', $decoded) ?? '');
            return $normalized !== '' ? $normalized : null;
        };

        $majorPresets = [
            'à¸„à¸“à¸´à¸•à¸¨à¸²à¸ªà¸•à¸£à¹Œ',
            'à¸§à¸´à¸—à¸¢à¸²à¸¨à¸²à¸ªà¸•à¸£à¹Œ',
            'à¸ à¸²à¸©à¸²à¹„à¸—à¸¢',
            'à¸ à¸²à¸©à¸²à¸­à¸±à¸‡à¸à¸¤à¸©',
            'à¸ªà¸±à¸‡à¸„à¸¡à¸¨à¸¶à¸à¸©à¸²',
            'à¸ªà¸¸à¸‚à¸¨à¸¶à¸à¸©à¸²/à¸žà¸¥à¸¨à¸¶à¸à¸©à¸²',
            'à¸¨à¸´à¸¥à¸›à¸°',
            'à¸”à¸™à¸•à¸£à¸µ',
            'à¸à¸²à¸£à¸‡à¸²à¸™à¸­à¸²à¸Šà¸µà¸ž',
            'à¸„à¸­à¸¡à¸žà¸´à¸§à¹€à¸•à¸­à¸£à¹Œ',
        ];

        $majorSeed = $this->fetchLegacyCourseNamesFromTable();
        if ($majorSeed->isEmpty()) {
            $majorSeed = collect($majorPresets);
        }

        $allMajors = $majorSeed
            ->map(fn ($major) => $normalizeMajor((string) $major))
            ->filter()
            ->merge(
                $homeroomTeachers
                    ->pluck('major')
                    ->map(fn ($major) => $normalizeMajor((string) $major))
                    ->filter()
            )
            ->unique()
            ->values();

        $teachersByMajor = $homeroomTeachers
            ->filter(fn ($teacher) => $normalizeMajor($teacher->major) !== null)
            ->groupBy(fn ($teacher) => $normalizeMajor($teacher->major));

        $courses = Course::with('teacher:id,name,email')
            ->latest()
            ->get();
        // Merge course names into the major map.
        foreach ($courses as $course) {
            $majorKey = $normalizeMajor((string) $course->name);
            if ($majorKey === null) {
                continue;
            }

            $current = collect($teachersByMajor->get($majorKey, collect()));

            if ($course->teacher) {
                $current = $current
                    ->push($course->teacher)
                    ->unique('id')
                    ->values();
            }
            // Keep majors visible even when no teacher is assigned yet.
            $teachersByMajor->put($majorKey, $current);
        }
        // Build the final major list including course names.
        $allMajors = $allMajors
            ->merge(
                $courses
                    ->pluck('name')
                    ->map(fn ($name) => $normalizeMajor((string) $name))
                    ->filter()
            )
            ->unique()
            ->values();

        $teacherWithMajorCount = $teachersByMajor
            ->flatten(1)
            ->unique('id')
            ->count();
        $completeTeachers = $teacherSummary['completeTeacherStatuses'];
        $incompleteTeachers = $teacherSummary['incompleteTeacherStatuses'];
        $completeTeacherCount = $teacherSummary['completeTeacherCount'];
        $incompleteTeacherCount = $teacherSummary['incompleteTeacherCount'];

        return view('dashboards.director', [
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'classCount'   => $classCount,
            'studentsByRoom' => $studentsByRoom,
            'roomOptions' => $roomOptions,
            'studentsByRoomPayload' => $studentsByRoomPayload,
            'homeroomTeachers' => $homeroomTeachers,
            'allMajors' => $allMajors,
            'teachersByMajor' => $teachersByMajor,
            'teacherWithMajorCount' => $teacherWithMajorCount,
            'courses' => $courses,
            'completeTeacherCount' => $completeTeacherCount,
            'incompleteTeacherCount' => $incompleteTeacherCount,
            'completeTeachers' => $completeTeachers,
            'incompleteTeachers' => $incompleteTeachers,
            'teacherDirectoryAll' => $teacherDirectoryAll,
        ]);
    }

    public function attendanceHolidays(Request $request)
    {
        if (($request->user()->role_name ?? null) !== 'director') {
            abort(403);
        }

        $filters = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
            'term' => 'nullable|in:1,2,summer',
            'date' => 'nullable|date',
        ]);

        $courses = Course::query()
            ->with('teacher:id,name')
            ->orderBy('name')
            ->orderBy('grade')
            ->get(['id', 'name', 'grade', 'term', 'user_id']);

        $selectedCourseId = (int) ($filters['course_id'] ?? ($courses->first()->id ?? 0));
        $selectedCourse = $courses->firstWhere('id', $selectedCourseId);
        $selectedTerm = in_array((string) ($filters['term'] ?? ''), ['1', '2', 'summer'], true)
            ? (string) $filters['term']
            : (string) ($selectedCourse->term ?? '1');
        $attendanceDate = $filters['date']
            ?? now(config('app.timezone', 'Asia/Bangkok'))->toDateString();

        $holidayRecord = null;
        $recentHolidays = collect();

        if ($selectedCourse) {
            $holidayRecord = CourseAttendanceHoliday::query()
                ->where('course_id', $selectedCourse->id)
                ->where('term', $selectedTerm)
                ->whereDate('holiday_date', $attendanceDate)
                ->first();

            $recentHolidays = CourseAttendanceHoliday::query()
                ->where('course_id', $selectedCourse->id)
                ->where('term', $selectedTerm)
                ->orderByDesc('holiday_date')
                ->limit(15)
                ->get();
        }

        return view('director.attendance-holidays', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'selectedCourseId' => $selectedCourse ? (int) $selectedCourse->id : 0,
            'selectedTerm' => $selectedTerm,
            'attendanceDate' => $attendanceDate,
            'holidayRecord' => $holidayRecord,
            'recentHolidays' => $recentHolidays,
        ]);
    }

    public function storeAttendanceHoliday(Request $request)
    {
        if (($request->user()->role_name ?? null) !== 'director') {
            abort(403);
        }

        $data = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'term' => 'required|in:1,2,summer',
            'attendance_date' => 'required|date',
            'action' => 'required|in:holiday,clear_holiday',
            'holiday_name' => 'nullable|string|max:255',
            'holiday_note' => 'nullable|string|max:1000',
        ]);

        $courseId = (int) $data['course_id'];
        $term = (string) $data['term'];
        $attendanceDate = $data['attendance_date'];

        if ($data['action'] === 'holiday') {
            $holidayName = trim((string) ($data['holiday_name'] ?? ''));
            $holidayNote = trim((string) ($data['holiday_note'] ?? ''));

            CourseAttendanceHoliday::query()->updateOrCreate(
                [
                    'course_id' => $courseId,
                    'term' => $term,
                    'holiday_date' => $attendanceDate,
                ],
                [
                    'holiday_name' => $holidayName !== '' ? $holidayName : null,
                    'note' => $holidayNote !== '' ? $holidayNote : null,
                    'recorded_by' => (int) ($request->user()->id ?? 0),
                ]
            );

            $status = 'à¸šà¸±à¸™à¸—à¸¶à¸à¸§à¸±à¸™à¸«à¸¢à¸¸à¸”à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§';
        } else {
            CourseAttendanceHoliday::query()
                ->where('course_id', $courseId)
                ->where('term', $term)
                ->whereDate('holiday_date', $attendanceDate)
                ->delete();

            $status = 'à¸¢à¸à¹€à¸¥à¸´à¸à¸§à¸±à¸™à¸«à¸¢à¸¸à¸”à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§';
        }

        return redirect()
            ->route('director.attendance-holidays', [
                'course_id' => $courseId,
                'term' => $term,
                'date' => $attendanceDate,
            ])
            ->with('status', $status);
    }

    public function attendanceHolidaysGlobal(Request $request)
    {
        if (($request->user()->role_name ?? null) !== 'director') {
            abort(403);
        }

        $today = now(config('app.timezone', 'Asia/Bangkok'));
        $filters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'month' => 'nullable|date_format:Y-m',
        ], [
            'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
            'month.date_format' => 'เดือนที่เลือกไม่ถูกต้อง',
        ]);

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? ($startDate ?: null);
        $selectedMonth = $filters['month'] ?? $today->format('Y-m');
        $monthCursor = Carbon::createFromFormat('Y-m', $selectedMonth, config('app.timezone', 'Asia/Bangkok'));
        $previewStartDate = $monthCursor->copy()->startOfMonth()->toDateString();
        $previewEndDate = $monthCursor->copy()->endOfMonth()->toDateString();
        $holidayPreview = $this->buildHolidayPreview($previewStartDate, $previewEndDate);

        $recentHolidays = CourseAttendanceHoliday::query()
            ->whereNull('course_id')
            ->whereNull('term')
            ->orderByDesc('holiday_date')
            ->limit(30)
            ->get();

        return view('director.attendance-holidays', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedMonth' => $selectedMonth,
            'previewStartDate' => $previewStartDate,
            'previewEndDate' => $previewEndDate,
            'holidayPreview' => $holidayPreview,
            'recentHolidays' => $recentHolidays,
        ]);
    }

    public function storeAttendanceHolidayGlobal(Request $request)
    {
        if (($request->user()->role_name ?? null) !== 'director') {
            abort(403);
        }

        $data = $request->validate([
            'action' => 'required|in:holiday,clear_holiday',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'holiday_date' => 'nullable|date',
            'holiday_name' => 'nullable|string|max:255',
            'holiday_note' => 'nullable|string|max:1000',
        ], [
            'start_date.date' => 'กรุณาเลือกวันที่เริ่มต้นให้ถูกต้อง',
            'end_date.date' => 'กรุณาเลือกวันที่สิ้นสุดให้ถูกต้อง',
            'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
            'holiday_date.date' => 'วันที่วันหยุดไม่ถูกต้อง',
        ]);

        if ($data['action'] === 'holiday') {
            $startDate = $data['start_date']
                ?? now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
            $endDate = $data['end_date'] ?? $startDate;
            $holidayName = trim((string) ($data['holiday_name'] ?? ''));
            $holidayNote = trim((string) ($data['holiday_note'] ?? ''));
            $dates = $this->buildGlobalHolidayDateRange($startDate, $endDate);

            if ($dates === []) {
                return redirect()
                    ->route('director.attendance-holidays', [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'month' => Carbon::parse($startDate)->format('Y-m'),
                    ])
                    ->with('status', 'วันเสาร์และวันอาทิตย์เป็นวันหยุดอัตโนมัติอยู่แล้ว จึงไม่ต้องบันทึกเพิ่ม');
            }

            foreach ($dates as $holidayDate) {
                CourseAttendanceHoliday::query()->updateOrCreate(
                    [
                        'course_id' => null,
                        'term' => null,
                        'holiday_date' => $holidayDate,
                    ],
                    [
                        'holiday_name' => $holidayName !== '' ? $holidayName : null,
                        'note' => $holidayNote !== '' ? $holidayNote : null,
                        'recorded_by' => (int) ($request->user()->id ?? 0),
                    ]
                );
            }

            $status = count($dates) === 1
                ? 'บันทึกวันหยุดเรียบร้อยแล้ว'
                : 'บันทึกวันหยุดเรียบร้อยแล้ว จำนวน ' . count($dates) . ' วัน';
        } else {
            $holidayDate = $data['holiday_date']
                ?? $data['start_date']
                ?? now(config('app.timezone', 'Asia/Bangkok'))->toDateString();

            CourseAttendanceHoliday::query()
                ->whereNull('course_id')
                ->whereNull('term')
                ->whereDate('holiday_date', $holidayDate)
                ->delete();

            $startDate = $holidayDate;
            $endDate = $holidayDate;
            $status = 'ยกเลิกวันหยุดเรียบร้อยแล้ว';
        }

        return redirect()
            ->route('director.attendance-holidays', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'month' => Carbon::parse($startDate)->format('Y-m'),
            ])
            ->with('status', $status);
    }

    private function buildGlobalHolidayDateRange(string $startDate, string $endDate): array
    {
        $dates = [];
        $cursor = Carbon::parse($startDate)->startOfDay();
        $lastDate = Carbon::parse($endDate)->startOfDay();

        while ($cursor->lte($lastDate)) {
            if (! $this->isWeekendDate($cursor->toDateString())) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    private function buildHolidayPreview(string $startDate, string $endDate)
    {
        $storedHolidays = CourseAttendanceHoliday::query()
            ->whereNull('course_id')
            ->whereNull('term')
            ->whereBetween('holiday_date', [$startDate, $endDate])
            ->orderBy('holiday_date')
            ->get()
            ->keyBy(fn (CourseAttendanceHoliday $holiday) => $holiday->holiday_date->toDateString());

        $preview = collect();
        $cursor = Carbon::parse($startDate)->startOfDay();
        $lastDate = Carbon::parse($endDate)->startOfDay();

        while ($cursor->lte($lastDate)) {
            $date = $cursor->toDateString();

            if ($storedHolidays->has($date)) {
                $preview->push($storedHolidays->get($date));
            } elseif ($this->isWeekendDate($date)) {
                $preview->push(new CourseAttendanceHoliday([
                    'holiday_date' => $date,
                    'holiday_name' => Carbon::parse($date)->isSaturday() ? 'วันเสาร์' : 'วันอาทิตย์',
                    'note' => 'เสาร์-อาทิตย์เป็นวันหยุดอัตโนมัติ',
                ]));
            }

            $cursor->addDay();
        }

        return $preview;
    }

    private function isWeekendDate(string $date): bool
    {
        return Carbon::parse($date)->isWeekend();
    }

    public function students(Request $request)
    {
        $normalizeRoom = function ($room): ?string {
            if (is_array($room)) {
                if (array_key_exists('room', $room)) {
                    $room = $room['room'];
                } elseif (array_key_exists('name', $room)) {
                    $room = $room['name'];
                } elseif (array_key_exists(2, $room)) {
                    $room = $room[2];
                } else {
                    $room = reset($room);
                }
            }

            if (is_object($room)) {
                if (isset($room->room)) {
                    $room = $room->room;
                } elseif (isset($room->name)) {
                    $room = $room->name;
                } else {
                    $room = method_exists($room, '__toString') ? (string) $room : null;
                }
            }

            if ($room === null) {
                return null;
            }

            $normalized = trim((string) $room);
            return $normalized !== '' ? $normalized : null;
        };

        $unknownRoomLabel = 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Â¸Ã Â¸Â«Ã Â¹â€°Ã Â¸Â­Ã Â¸â€¡';
        $unknownGradeLabel = 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Â¸Ã Â¸Å Ã Â¸Â±Ã Â¹â€°Ã Â¸â„¢';
        $hasClassroomCol = Schema::hasColumn('students', 'classroom');
        $studentColumns = $hasClassroomCol
            ? ['id', 'student_code', 'title', 'first_name', 'last_name', 'room', 'classroom']
            : ['id', 'student_code', 'title', 'first_name', 'last_name', 'room'];

        $studentsByRoom = Student::query()
            ->select($studentColumns)
            ->orderBy('student_code')
            ->get()
            ->map(function ($student) use ($normalizeRoom) {
                $student->room_normalized = $normalizeRoom($student->classroom ?? $student->room ?? null);
                return $student;
            })
            ->groupBy(fn ($student) => $student->room_normalized ?? $unknownRoomLabel)
            ->sortKeys();

        $roomMetaPayload = $studentsByRoom->mapWithKeys(function ($students, $room) use ($unknownRoomLabel, $unknownGradeLabel) {
            $roomText = trim((string) $room);
            $grade = $unknownGradeLabel;
            $roomLabel = $roomText;

            if ($roomText !== '' && $roomText !== $unknownRoomLabel) {
                $parts = explode('/', $roomText, 2);
                $gradePart = trim((string) ($parts[0] ?? ''));
                $roomPart = trim((string) ($parts[1] ?? ''));

                $grade = $gradePart !== '' ? $gradePart : $unknownGradeLabel;
                $roomLabel = $roomPart !== '' ? $roomPart : $roomText;
            } else {
                $roomLabel = $unknownRoomLabel;
            }

            return [
                $room => [
                    'grade' => $grade,
                    'room_label' => $roomLabel,
                    'full_label' => $roomText !== '' ? $roomText : $unknownRoomLabel,
                    'count' => collect($students)->count(),
                ],
            ];
        })->all();

        $roomsByGradePayload = collect($roomMetaPayload)
            ->groupBy('grade', true)
            ->map(function ($rooms) {
                return collect($rooms)->map(function ($meta, $roomValue) {
                    return [
                        'value' => $roomValue,
                        'room_label' => $meta['room_label'],
                        'full_label' => $meta['full_label'],
                        'count' => $meta['count'],
                    ];
                })->values()->all();
            })
            ->all();

        $gradeOptions = collect($roomsByGradePayload)->keys()->values();

        $selectedGrade = trim((string) $request->query('grade', ''));
        if ($selectedGrade === '' || ! array_key_exists($selectedGrade, $roomsByGradePayload)) {
            $selectedGrade = $gradeOptions->first();
        }

        $roomOptions = collect($roomsByGradePayload[$selectedGrade] ?? []);
        $selectedRoom = trim((string) $request->query('room', ''));
        $validRoomValues = $roomOptions->pluck('value')->all();
        if ($selectedRoom === '' || ! in_array($selectedRoom, $validRoomValues, true)) {
            $selectedRoom = $roomOptions->first()['value'] ?? null;
        }

        $studentsByRoomPayload = $studentsByRoom
            ->map(function ($students) {
                return collect($students)
                    ->map(function ($student) {
                        $fullName = trim(($student->title ? $student->title . ' ' : '') . $student->first_name . ' ' . $student->last_name);
                        return [
                            'student_code' => $student->student_code,
                            'name' => $fullName !== '' ? $fullName : '-',
                        ];
                    })
                    ->values()
                    ->all();
            })
            ->all();

        $studentCount = $studentsByRoom->sum(fn ($students) => collect($students)->count());

        return view('director.students', [
            'gradeOptions' => $gradeOptions,
            'roomsByGradePayload' => $roomsByGradePayload,
            'roomMetaPayload' => $roomMetaPayload,
            'roomOptions' => $roomOptions,
            'selectedGrade' => $selectedGrade,
            'selectedRoom' => $selectedRoom,
            'studentsByRoomPayload' => $studentsByRoomPayload,
            'studentCount' => $studentCount,
        ]);
    }

    public function teacherPlans(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $selectedCourse = (string) $request->input('course', '');
        $selectedGrade = (string) $request->input('grade', '');

        $useLegacyCourseNames = Schema::hasTable('tb_course') && Schema::hasColumn('tb_course', 'name_course');

        if ($useLegacyCourseNames) {
            $legacyRows = DB::table('tb_course as co')
                ->leftJoin('tb_class_schedule as sch', 'sch.id_course', '=', 'co.id_course')
                ->leftJoin('tb_teacher as t', 't.id_teacher', '=', 'sch.id_teacher')
                ->selectRaw("
                    co.id_course AS course_id,
                    co.name_course AS course_name,
                    sch.term AS term,
                    sch.year AS year,
                    sch.id_teacher AS teacher_id,
                    t.id_title_name AS teacher_title,
                    t.name AS teacher_first_name,
                    t.surname AS teacher_last_name
                ")
                ->whereNotNull('co.name_course')
                ->orderBy('co.name_course')
                ->get();

            $courses = collect($legacyRows)
                ->groupBy(fn ($row) => (int) ($row->course_id ?? 0))
                ->map(function ($rows) {
                    $rows = collect($rows);
                    $first = $rows->first();

                    $courseName = $this->decodeLegacyThai((string) ($first->course_name ?? ''));
                    $teacherRow = $rows->first(fn ($row) => is_numeric($row->teacher_id ?? null));

                    $teacher = null;
                    $teacherId = is_numeric($teacherRow->teacher_id ?? null) ? (int) $teacherRow->teacher_id : null;
                    if ($teacherId !== null) {
                        $teacherName = $this->formatLegacyFullName(
                            $teacherRow->teacher_title ?? null,
                            $teacherRow->teacher_first_name ?? null,
                            $teacherRow->teacher_last_name ?? null
                        );
                        $teacher = (object) [
                            'id' => $teacherId,
                            'name' => $teacherName !== '' ? $teacherName : '-',
                            'email' => '',
                        ];
                    }

                    $term = $rows->pluck('term')->filter()->first();
                    $year = $rows->pluck('year')->filter()->first();

                    return (object) [
                        'id' => (int) ($first->course_id ?? 0),
                        'user_id' => null,
                        'name' => $courseName !== '' ? $courseName : '-',
                        'grade' => null,
                        'term' => $term !== null ? (string) $term : null,
                        'year' => $year !== null ? (string) $year : null,
                        'rooms' => [],
                        'description' => null,
                        // Legacy courses from tb_course only provide names;
                        // treat completion as incomplete until real planning data exists.
                        'teaching_hours' => [],
                        'assignments' => [],
                        'teacher' => $teacher,
                        'course_rooms' => [],
                        'is_legacy_group_course' => true,
                        'is_complete' => false,
                    ];
                })
                ->filter(fn ($course) => trim((string) ($course->name ?? '')) !== '' && ($course->name ?? '') !== '-')
                ->sortBy(function ($course) {
                    $name = trim((string) ($course->name ?? ''));
                    return function_exists('mb_strtolower')
                        ? mb_strtolower($name, 'UTF-8')
                        : strtolower($name);
                }, SORT_NATURAL)
                ->values();

            $courseOptions = $courses
                ->pluck('name')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($selectedCourse && ! in_array($selectedCourse, $courseOptions, true)) {
                $selectedCourse = null;
            }

            $gradeOptions = [];
            $selectedGrade = null;

            $normalizedSearch = function_exists('mb_strtolower')
                ? mb_strtolower($search, 'UTF-8')
                : strtolower($search);

            $courses = $courses
                ->when($selectedCourse, fn ($items) => $items->filter(fn ($course) => (string) ($course->name ?? '') === (string) $selectedCourse))
                ->when($normalizedSearch !== '', function ($items) use ($normalizedSearch) {
                    return $items->filter(function ($course) use ($normalizedSearch) {
                        $courseName = trim((string) ($course->name ?? ''));
                        $teacherName = trim((string) ($course->teacher->name ?? ''));

                        $courseName = function_exists('mb_strtolower')
                            ? mb_strtolower($courseName, 'UTF-8')
                            : strtolower($courseName);
                        $teacherName = function_exists('mb_strtolower')
                            ? mb_strtolower($teacherName, 'UTF-8')
                            : strtolower($teacherName);

                        return str_contains($courseName, $normalizedSearch)
                            || str_contains($teacherName, $normalizedSearch);
                    });
                })
                ->values();
        } else {
            $teacherRoleId = Role::where('name', 'teacher')->value('id');

            $baseCourseQuery = Course::query()
                ->when($teacherRoleId, fn ($query) => $query->whereIn('user_id', function ($sub) use ($teacherRoleId) {
                    $sub->select('id')->from('users')->where('role_id', $teacherRoleId);
                }));

            $courseOptions = (clone $baseCourseQuery)
                ->select('name')
                ->whereNotNull('name')
                ->distinct()
                ->orderBy('name')
                ->pluck('name')
                ->all();

            if ($selectedCourse && ! in_array($selectedCourse, $courseOptions, true)) {
                $selectedCourse = null;
            }

            $gradeOptions = $selectedCourse
                ? (clone $baseCourseQuery)
                    ->where('name', $selectedCourse)
                    ->select('grade')
                    ->whereNotNull('grade')
                    ->distinct()
                    ->orderBy('grade')
                    ->pluck('grade')
                    ->all()
                : [];

            if ($selectedGrade && ! in_array($selectedGrade, $gradeOptions, true)) {
                $selectedGrade = null;
            }

            $courses = (clone $baseCourseQuery)
                ->with(['teacher:id,name,email'])
                ->when($selectedCourse, fn ($query) => $query->where('name', $selectedCourse))
                ->when($selectedGrade, fn ($query) => $query->where('grade', $selectedGrade))
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('grade', 'like', '%' . $search . '%')
                            ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', '%' . $search . '%'));
                    });
                })
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'grade',
                    'term',
                    'year',
                    'rooms',
                    'description',
                    'teaching_hours',
                    'assignments'
                )
                ->orderBy('grade')
                ->orderBy('name')
                ->get();
        }

        $teacherCount = $courses->pluck('teacher')->filter()->unique('id')->count();
        $studentCount = Student::count();
        $roomsCount = $courses->flatMap(fn ($course) => $course->rooms ?? [])->unique()->count();
        $teacherStatus = $courses
            ->filter(fn ($course) => $course->teacher)
            ->groupBy(fn ($course) => $course->teacher->id)
            ->map(function ($teacherCourses) {
                $hasIncompleteCourse = $teacherCourses->contains(function ($course) {
                    if (isset($course->is_complete)) {
                        return ! (bool) $course->is_complete;
                    }

                    $hasHours = ! empty($course->teaching_hours);
                    $hasAssignments = ! empty($course->assignments);
                    return ! ($hasHours && $hasAssignments);
                });

                return [
                    'complete' => ! $hasIncompleteCourse,
                ];
            })
            ->toBase();

        $completeTeacherCount = $teacherStatus->filter(fn ($status) => $status['complete'])->count();
        $incompleteTeacherCount = $teacherStatus->filter(fn ($status) => ! $status['complete'])->count();

        $activityData = $this->buildTeacherActivityData(
            $request,
            'activity_teacher_id',
            'activity_start_date',
            'activity_end_date',
            'activity_room_id',
            $courses
        );

        if ($request->ajax()) {
            $html = view('director.partials.teacher-plan-results', [
                'courses'       => $courses,
                'teacherCount'  => $teacherCount,
                'studentCount'  => $studentCount,
                'roomsCount'    => $roomsCount,
                'completeTeacherCount' => $completeTeacherCount,
                'incompleteTeacherCount' => $incompleteTeacherCount,
            ])->render();

            return response()->json([
                'html'           => $html,
                'gradeOptions'   => $gradeOptions,
                'selectedGrade'  => $selectedGrade,
                'selectedCourse' => $selectedCourse,
            ]);
        }

        return view('director.teacher-plans', [
            'courseOptions' => $courseOptions,
            'selectedCourse' => $selectedCourse,
            'gradeOptions'  => $gradeOptions,
            'selectedGrade' => $selectedGrade,
            'courses'       => $courses,
            'teacherCount'  => $teacherCount,
            'search'        => $search,
            'studentCount'  => $studentCount,
            'roomsCount'    => $roomsCount,
            'completeTeacherCount' => $completeTeacherCount,
            'incompleteTeacherCount' => $incompleteTeacherCount,
            'activityTeacherOptions' => $activityData['teacherOptions'],
            'activityRoomOptions' => $activityData['roomOptions'],
            'activitySelectedTeacherId' => $activityData['selectedTeacherId'],
            'activitySelectedRoomId' => $activityData['selectedRoomId'],
            'activityStartDate' => $activityData['startDate'],
            'activityEndDate' => $activityData['endDate'],
            'activityAttendanceLogs' => $activityData['attendanceLogs'],
            'activityBehaviorLogs' => $activityData['behaviorLogs'],
            'activityTimeline' => $activityData['timeline'],
            'activityAttendanceCount' => $activityData['attendanceCount'],
            'activityBehaviorCount' => $activityData['behaviorCount'],
            'activityActiveTeacherCount' => $activityData['activeTeacherCount'],
            'activityAttendanceMatrixDates' => $activityData['attendanceMatrixDates'],
            'activityAttendanceMatrixMonthSpans' => $activityData['attendanceMatrixMonthSpans'],
            'activityAttendanceMatrixRows' => $activityData['attendanceMatrixRows'],
        ]);
    }

    private function buildTeacherActivityData(
        Request $request,
        string $teacherIdKey = 'teacher_id',
        string $startDateKey = 'start_date',
        string $endDateKey = 'end_date',
        string $roomIdKey = 'room_id',
        $courses = null
    ): array {
        $teacherOptions = $this->fetchTeacherDirectoryFromLegacyTable();
        $roomOptions = collect();
        if (Schema::hasTable('tb_class')) {
            $roomOptions = DB::table('tb_class')
                ->select('id_class', 'class')
                ->whereNotNull('id_class')
                ->orderBy('class')
                ->get()
                ->map(function ($row) {
                    $roomId = is_numeric($row->id_class ?? null) ? (int) $row->id_class : null;
                    $roomName = $this->decodeLegacyThai((string) ($row->class ?? ''));

                    return [
                        'id' => $roomId,
                        'name' => $roomName !== '' ? $roomName : '-',
                    ];
                })
                ->filter(fn ($room) => $room['id'] !== null && ($room['name'] ?? '') !== '')
                ->unique('id')
                ->sortBy('name', SORT_NATURAL)
                ->values();
        }

        $selectedTeacherId = $request->query($teacherIdKey);
        $selectedTeacherId = is_numeric($selectedTeacherId) ? (int) $selectedTeacherId : null;

        $selectedRoomId = $request->query($roomIdKey);
        $selectedRoomId = is_numeric($selectedRoomId) ? (int) $selectedRoomId : null;
        if ($selectedRoomId !== null && $roomOptions->isNotEmpty()) {
            $roomExists = $roomOptions
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->contains($selectedRoomId);

            if (! $roomExists) {
                $selectedRoomId = null;
            }
        }

        $startDate = $this->normalizeIsoDate($request->query($startDateKey));
        $endDate = $this->normalizeIsoDate($request->query($endDateKey));

        $attendanceDateExpr = "COALESCE(STR_TO_DATE(cs.date, '%d/%m/%Y'), STR_TO_DATE(cs.date, '%Y-%m-%d'))";
        $attendanceDateTimeExpr = "COALESCE(
            STR_TO_DATE(CONCAT(cs.date, ' ', IFNULL(NULLIF(cs.Current_time, ''), '00:00')), '%d/%m/%Y %H:%i'),
            STR_TO_DATE(CONCAT(cs.date, ' ', IFNULL(NULLIF(cs.Current_time, ''), '00:00')), '%Y-%m-%d %H:%i'),
            {$attendanceDateExpr}
        )";

        $attendanceLogs = collect();
        if (Schema::hasTable('tb_checkname_student')) {
            $attendanceQuery = DB::table('tb_checkname_student as cs')
                ->leftJoin('tb_ex_class as ex', 'ex.id_ex_class', '=', 'cs.id_ex_class')
                ->leftJoin('tb_class_schedule as sch', 'sch.id_class_schedule', '=', 'ex.id_class_schedule')
                ->leftJoin('tb_teacher as t', function ($join) {
                    $join->whereRaw('t.id_teacher = COALESCE(cs.id_teacher_rong, sch.id_teacher)');
                })
                ->leftJoin('tb_student as st', 'st.id_student', '=', 'cs.id_student')
                ->leftJoin('tb_course as co', 'co.id_course', '=', 'ex.id_course')
                ->leftJoin('tb_class as cl', 'cl.id_class', '=', 'ex.id_class')
                ->selectRaw("
                    cs.id_checkname_student AS id,
                    COALESCE(cs.id_teacher_rong, sch.id_teacher) AS teacher_id,
                    t.id_title_name AS teacher_title,
                    t.name AS teacher_first_name,
                    t.surname AS teacher_last_name,
                    st.title_name AS student_title,
                    st.name AS student_first_name,
                    st.surname AS student_last_name,
                    cs.status AS check_status,
                    cs.Current_time AS check_time,
                    cs.date AS check_date,
                    ex.id_class AS class_id,
                    cl.class AS class_name,
                    co.name_course AS course_name,
                    {$attendanceDateTimeExpr} AS activity_at_raw
                ");

            if ($selectedTeacherId) {
                $attendanceQuery->whereRaw('COALESCE(cs.id_teacher_rong, sch.id_teacher) = ?', [$selectedTeacherId]);
            }
            if ($selectedRoomId !== null) {
                $attendanceQuery->where('ex.id_class', $selectedRoomId);
            }
            if ($startDate) {
                $attendanceQuery->whereRaw("{$attendanceDateExpr} >= ?", [$startDate]);
            }
            if ($endDate) {
                $attendanceQuery->whereRaw("{$attendanceDateExpr} <= ?", [$endDate]);
            }

            $attendanceLogs = $attendanceQuery
                ->orderByRaw("{$attendanceDateTimeExpr} DESC")
                ->limit(1000)
                ->get()
                ->map(function ($row) {
                    $teacherName = $this->formatLegacyFullName(
                        $row->teacher_title ?? null,
                        $row->teacher_first_name ?? null,
                        $row->teacher_last_name ?? null
                    );

                    $studentName = $this->formatLegacyFullName(
                        $row->student_title ?? null,
                        $row->student_first_name ?? null,
                        $row->student_last_name ?? null
                    );

                    $activityAt = $this->formatDateTimeForDisplay($row->activity_at_raw);
                    $status = $this->decodeLegacyThai((string) ($row->check_status ?? ''));
                    $className = $this->decodeLegacyThai((string) ($row->class_name ?? ''));
                    $courseName = $this->decodeLegacyThai((string) ($row->course_name ?? ''));

                    return [
                        'id' => (int) ($row->id ?? 0),
                        'activity_at' => $activityAt,
                        'activity_at_raw' => (string) ($row->activity_at_raw ?? ''),
                        'activity_date' => $this->toIsoDate($row->activity_at_raw ?? null, $row->check_date ?? null),
                        'teacher_id' => is_numeric($row->teacher_id) ? (int) $row->teacher_id : null,
                        'class_id' => is_numeric($row->class_id ?? null) ? (int) $row->class_id : null,
                        'teacher_name' => $teacherName !== '' ? $teacherName : '-',
                        'student_name' => $studentName !== '' ? $studentName : '-',
                        'status' => $status !== '' ? $status : '-',
                        'class_name' => $className !== '' ? $className : '-',
                        'course_name' => $courseName !== '' ? $courseName : '-',
                        'source_table' => 'tb_checkname_student',
                    ];
                })
                ->values();
        }

        $behaviorLogs = collect();
        if (Schema::hasTable('tbc_history_behavior_score')) {
            $behaviorQuery = DB::table('tbc_history_behavior_score as h')
                ->leftJoin('tb_teacher as t', 't.id_teacher', '=', 'h.id_teacher')
                ->leftJoin('tb_student as st', 'st.id_student', '=', 'h.id_student')
                ->selectRaw("
                    h.hbs_id AS id,
                    h.id_teacher AS teacher_id,
                    h.before_score,
                    h.score_add,
                    h.score_cut,
                    h.after_score,
                    h.hbs_note,
                    h.hbs_datetime AS activity_at_raw,
                    t.id_title_name AS teacher_title,
                    t.name AS teacher_first_name,
                    t.surname AS teacher_last_name,
                    st.title_name AS student_title,
                    st.name AS student_first_name,
                    st.surname AS student_last_name
                ");

            if ($selectedTeacherId) {
                $behaviorQuery->where('h.id_teacher', $selectedTeacherId);
            }
            if ($startDate) {
                $behaviorQuery->whereDate('h.hbs_datetime', '>=', $startDate);
            }
            if ($endDate) {
                $behaviorQuery->whereDate('h.hbs_datetime', '<=', $endDate);
            }

            $behaviorLogs = $behaviorQuery
                ->orderByDesc('h.hbs_datetime')
                ->limit(1000)
                ->get()
                ->map(function ($row) {
                    $teacherName = $this->formatLegacyFullName(
                        $row->teacher_title ?? null,
                        $row->teacher_first_name ?? null,
                        $row->teacher_last_name ?? null
                    );

                    $studentName = $this->formatLegacyFullName(
                        $row->student_title ?? null,
                        $row->student_first_name ?? null,
                        $row->student_last_name ?? null
                    );

                    return [
                        'id' => (int) ($row->id ?? 0),
                        'activity_at' => $this->formatDateTimeForDisplay($row->activity_at_raw),
                        'activity_at_raw' => (string) ($row->activity_at_raw ?? ''),
                        'teacher_id' => is_numeric($row->teacher_id) ? (int) $row->teacher_id : null,
                        'teacher_name' => $teacherName !== '' ? $teacherName : '-',
                        'student_name' => $studentName !== '' ? $studentName : '-',
                        'before_score' => (float) ($row->before_score ?? 0),
                        'score_add' => (float) ($row->score_add ?? 0),
                        'score_cut' => (float) ($row->score_cut ?? 0),
                        'after_score' => (float) ($row->after_score ?? 0),
                        'note' => $this->decodeLegacyThai((string) ($row->hbs_note ?? '')),
                        'source_table' => 'tbc_history_behavior_score',
                    ];
                })
                ->values();
        }

        if ($courses !== null) {
            $courseLookupByTeacherName = $this->buildCourseLookupByTeacherName($courses);

            $eligibleTeacherLookupKeys = $courseLookupByTeacherName
                ->map(function ($courseDetails) {
                    return collect($courseDetails)
                        ->contains(fn ($course) => (bool) ($course['complete'] ?? false));
                })
                ->filter(fn ($isEligible) => $isEligible === true)
                ->keys()
                ->values();

            $isEligibleTeacher = function ($teacherName) use ($eligibleTeacherLookupKeys): bool {
                $lookupKey = $this->normalizeTeacherName((string) $teacherName);
                if ($lookupKey === '') {
                    return false;
                }

                return $eligibleTeacherLookupKeys->contains($lookupKey);
            };

            $attendanceLogs = $attendanceLogs
                ->filter(fn ($item) => $isEligibleTeacher($item['teacher_name'] ?? ''))
                ->values();

            $behaviorLogs = $behaviorLogs
                ->filter(fn ($item) => $isEligibleTeacher($item['teacher_name'] ?? ''))
                ->values();
        }

        [
            $attendanceMatrixDates,
            $attendanceMatrixMonthSpans,
            $attendanceMatrixRows,
        ] = $this->buildAttendanceMatrixData($attendanceLogs);

        $attendanceDetailFormat = "\u{0E40}\u{0E0A}\u{0E47}\u{0E01}\u{0E0A}\u{0E37}\u{0E48}\u{0E2D} %s | \u{0E2B}\u{0E49}\u{0E2D}\u{0E07} %s | \u{0E23}\u{0E32}\u{0E22}\u{0E27}\u{0E34}\u{0E0A}\u{0E32} %s | \u{0E2A}\u{0E16}\u{0E32}\u{0E19}\u{0E30} %s";
        $behaviorDetailFormat = "\u{0E1B}\u{0E23}\u{0E31}\u{0E1A}\u{0E04}\u{0E30}\u{0E41}\u{0E19}\u{0E19} %s | \u{0E04}\u{0E30}\u{0E41}\u{0E19}\u{0E19} %s%s";
        $notePrefix = " | \u{0E40}\u{0E2B}\u{0E15}\u{0E38}\u{0E1C}\u{0E25}: ";

        $timeline = $attendanceLogs
            ->map(function ($item) use ($attendanceDetailFormat) {
                return [
                    'activity_at' => $item['activity_at'],
                    'activity_at_raw' => $item['activity_at_raw'],
                    'teacher_name' => $item['teacher_name'],
                    'activity_type' => 'attendance',
                    'detail' => sprintf(
                        $attendanceDetailFormat,
                        $item['student_name'],
                        $item['class_name'],
                        $item['course_name'],
                        $item['status']
                    ),
                ];
            })
            ->merge(
                $behaviorLogs->map(function ($item) use ($behaviorDetailFormat, $notePrefix) {
                    $scoreParts = [];
                    if (($item['score_add'] ?? 0) > 0) {
                        $scoreParts[] = '+' . number_format((float) $item['score_add'], 2);
                    }
                    if (($item['score_cut'] ?? 0) > 0) {
                        $scoreParts[] = '-' . number_format((float) $item['score_cut'], 2);
                    }

                    $scoreText = empty($scoreParts) ? '-' : implode(' / ', $scoreParts);
                    $note = trim((string) ($item['note'] ?? ''));
                    $noteText = $note !== '' ? $notePrefix . $note : '';

                    return [
                        'activity_at' => $item['activity_at'],
                        'activity_at_raw' => $item['activity_at_raw'],
                        'teacher_name' => $item['teacher_name'],
                        'activity_type' => 'behavior',
                        'detail' => sprintf(
                            $behaviorDetailFormat,
                            $item['student_name'],
                            $scoreText,
                            $noteText
                        ),
                    ];
                })
            )
            ->sortByDesc(fn ($item) => $item['activity_at_raw'] ?: '0000-00-00 00:00:00')
            ->values();

        $activeTeacherCount = $attendanceLogs
            ->pluck('teacher_id')
            ->merge($behaviorLogs->pluck('teacher_id'))
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->count();

        if ($roomOptions->isEmpty()) {
            $roomOptions = $attendanceLogs
                ->map(function ($item) {
                    return [
                        'id' => is_numeric($item['class_id'] ?? null) ? (int) $item['class_id'] : null,
                        'name' => trim((string) ($item['class_name'] ?? '')),
                    ];
                })
                ->filter(fn ($room) => $room['id'] !== null && ($room['name'] ?? '') !== '' && ($room['name'] ?? '') !== '-')
                ->unique('id')
                ->sortBy('name', SORT_NATURAL)
                ->values();
        }

        return [
            'teacherOptions' => $teacherOptions,
            'roomOptions' => $roomOptions,
            'selectedTeacherId' => $selectedTeacherId,
            'selectedRoomId' => $selectedRoomId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'attendanceLogs' => $attendanceLogs,
            'behaviorLogs' => $behaviorLogs,
            'timeline' => $timeline,
            'attendanceCount' => $attendanceLogs->count(),
            'behaviorCount' => $behaviorLogs->count(),
            'activeTeacherCount' => $activeTeacherCount,
            'attendanceMatrixDates' => $attendanceMatrixDates,
            'attendanceMatrixMonthSpans' => $attendanceMatrixMonthSpans,
            'attendanceMatrixRows' => $attendanceMatrixRows,
        ];
    }

    private function buildAttendanceMatrixData($attendanceLogs): array
    {
        $logs = collect($attendanceLogs)
            ->map(function ($item) {
                $activityDate = $item['activity_date']
                    ?? $this->toIsoDate($item['activity_at_raw'] ?? null, $item['activity_at'] ?? null);

                $studentName = trim((string) ($item['student_name'] ?? ''));
                if ($activityDate === null || $studentName === '' || $studentName === '-') {
                    return null;
                }

                $status = trim((string) ($item['status'] ?? ''));

                return [
                    'student_name' => $studentName,
                    'class_id' => is_numeric($item['class_id'] ?? null) ? (int) $item['class_id'] : null,
                    'teacher_name' => trim((string) ($item['teacher_name'] ?? '')),
                    'class_name' => trim((string) ($item['class_name'] ?? '')),
                    'course_name' => trim((string) ($item['course_name'] ?? '')),
                    'activity_at' => trim((string) ($item['activity_at'] ?? '')),
                    'activity_date' => $activityDate,
                    'status' => $status,
                    'status_code' => $this->toAttendanceStatusCode($status),
                ];
            })
            ->filter()
            ->values();

        $dates = $logs
            ->pluck('activity_date')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(function ($isoDate) {
                return [
                    'key' => $isoDate,
                    'day_number' => (int) substr($isoDate, 8, 2),
                    'month_key' => substr($isoDate, 0, 7),
                    'month_label' => $this->monthLabelFromIsoDate($isoDate),
                    'day_label' => $this->dayLabelFromIsoDate($isoDate),
                ];
            })
            ->values();

        $monthSpans = $dates
            ->groupBy('month_key')
            ->map(function ($group) {
                $first = collect($group)->first();

                return [
                    'month_key' => (string) ($first['month_key'] ?? ''),
                    'month_label' => (string) ($first['month_label'] ?? ''),
                    'colspan' => collect($group)->count(),
                ];
            })
            ->values();

        $presentCode = "\u{0E21}";
        $absentCode = "\u{0E02}";

        $rows = $logs
            ->groupBy(function ($log) {
                $studentName = trim((string) ($log['student_name'] ?? ''));
                $classId = is_numeric($log['class_id'] ?? null) ? (int) $log['class_id'] : 0;
                $className = trim((string) ($log['class_name'] ?? '-'));

                return $studentName . '|' . $classId . '|' . $className;
            })
            ->map(function ($studentLogs) use ($presentCode, $absentCode) {
                $studentLogs = collect($studentLogs);

                $cells = $studentLogs
                    ->groupBy('activity_date')
                    ->map(function ($dateLogs) {
                        $dateLogs = collect($dateLogs)->values();

                        $codes = $dateLogs->pluck('status_code')->filter()->values();
                        $marks = $this->summarizeAttendanceMarks($codes);

                        $tooltip = $dateLogs
                            ->map(function ($log) {
                                $parts = collect([
                                    $log['activity_at'] ?? '',
                                    $log['course_name'] ?? '',
                                    $log['status'] ?? '',
                                ])->filter(fn ($value) => $value !== '' && $value !== '-');

                                return $parts->implode(' | ');
                            })
                            ->filter()
                            ->implode("\n");

                        return [
                            'mark' => $marks,
                            'status' => $dateLogs->pluck('status')->filter()->unique()->implode(', '),
                            'tooltip' => $tooltip,
                            'count' => $dateLogs->count(),
                        ];
                    })
                    ->toArray();

                $studentName = $studentLogs
                    ->pluck('student_name')
                    ->filter(fn ($value) => $value !== '' && $value !== '-')
                    ->first() ?? '-';

                $teacherName = $studentLogs
                    ->pluck('teacher_name')
                    ->filter(fn ($value) => $value !== '' && $value !== '-')
                    ->first() ?? '-';

                $className = $studentLogs
                    ->pluck('class_name')
                    ->filter(fn ($value) => $value !== '' && $value !== '-')
                    ->first() ?? '-';

                return [
                    'student_name' => (string) $studentName,
                    'teacher_name' => (string) $teacherName,
                    'class_name' => (string) $className,
                    'cells' => $cells,
                    'total_logs' => $studentLogs->count(),
                    'present_logs' => $studentLogs->where('status_code', $presentCode)->count(),
                    'absent_logs' => $studentLogs->where('status_code', $absentCode)->count(),
                ];
            })
            ->sortBy(function ($row) {
                $studentName = trim((string) ($row['student_name'] ?? ''));
                $className = trim((string) ($row['class_name'] ?? ''));
                $sortKey = $studentName . '|' . $className;

                return function_exists('mb_strtolower')
                    ? mb_strtolower($sortKey, 'UTF-8')
                    : strtolower($sortKey);
            }, SORT_NATURAL)
            ->values();

        return [$dates, $monthSpans, $rows];
    }

    private function toIsoDate($dateTimeValue, $fallbackDateValue = null): ?string
    {
        foreach ([$dateTimeValue, $fallbackDateValue] as $value) {
            $text = trim((string) $value);
            if ($text === '') {
                continue;
            }

            foreach ([
                'Y-m-d H:i:s',
                'Y-m-d H:i',
                'd/m/Y H:i:s',
                'd/m/Y H:i',
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
            ] as $format) {
                try {
                    return Carbon::createFromFormat($format, $text)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // Skip invalid format and continue trying.
                }
            }

            try {
                return Carbon::parse($text)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Continue with next fallback value.
            }
        }

        return null;
    }

    private function toAttendanceStatusCode(string $status): string
    {
        $normalized = $this->decodeLegacyThai($status);
        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($normalized, 'UTF-8')
            : strtolower($normalized);
        $normalized = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        if ($normalized === '') {
            return '-';
        }

        $absentCode = "\u{0E02}";
        $leaveCode = "\u{0E25}";
        $lateCode = "\u{0E2A}";
        $presentCode = "\u{0E21}";

        if (preg_match('/(?:\x{0E44}\x{0E21}\x{0E48}\x{0E21}\x{0E32}|\x{0E02}\x{0E32}\x{0E14}|absent|miss)/u', $normalized) === 1) {
            return $absentCode;
        }
        if (preg_match('/(?:\x{0E25}\x{0E32}|leave)/u', $normalized) === 1) {
            return $leaveCode;
        }
        if (preg_match('/(?:\x{0E2A}\x{0E32}\x{0E22}|late)/u', $normalized) === 1) {
            return $lateCode;
        }
        if (preg_match('/(?:\x{0E21}\x{0E32}|present|checkin|attend)/u', $normalized) === 1) {
            return $presentCode;
        }

        return '-';
    }

    private function summarizeAttendanceMarks($codes): string
    {
        $absentCode = "\u{0E02}";
        $leaveCode = "\u{0E25}";
        $lateCode = "\u{0E2A}";
        $presentCode = "\u{0E21}";

        $priority = [
            $absentCode => 1,
            $leaveCode => 2,
            $lateCode => 3,
            $presentCode => 4,
        ];

        $uniqueCodes = collect($codes)
            ->filter(fn ($code) => is_string($code) && $code !== '' && $code !== '-')
            ->unique()
            ->sortBy(fn ($code) => $priority[$code] ?? 99)
            ->values();

        if ($uniqueCodes->isEmpty()) {
            return '';
        }

        if ($uniqueCodes->count() === 1) {
            return (string) $uniqueCodes->first();
        }

        return $uniqueCodes->take(2)->implode('/');
    }

    private function monthLabelFromIsoDate(string $isoDate): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $isoDate)->format('m/Y');
        } catch (\Throwable $e) {
            return $isoDate;
        }
    }

    private function dayLabelFromIsoDate(string $isoDate): string
    {
        try {
            $day = Carbon::createFromFormat('Y-m-d', $isoDate)->dayOfWeekIso;
        } catch (\Throwable $e) {
            return '-';
        }

        return [
            1 => "\u{0E08}",
            2 => "\u{0E2D}",
            3 => "\u{0E1E}",
            4 => "\u{0E1E}\u{0E24}",
            5 => "\u{0E28}",
            6 => "\u{0E2A}",
            7 => "\u{0E2D}\u{0E32}",
        ][$day] ?? '-';
    }

    public function courseDetail(Course $course)
    {
        $course->load('teacher:id,name,email');

        $studentCount = Student::count();
        $teacherCount = $this->countTeachersFromLegacyTable()
            ?? User::whereHas('role', fn ($q) => $q->where('name', 'teacher'))->count();
        $roomsCount = collect($course->rooms ?? [])->unique()->count();

        if (request()->ajax()) {
            $html = view('director.partials.course-detail-modal', [
                'course'       => $course,
                'studentCount' => $studentCount,
                'teacherCount' => $teacherCount,
                'roomsCount'   => $roomsCount,
                'hours'        => collect($course->teaching_hours ?? []),
                'lessons'      => collect($course->lessons ?? []),
                'assignments'  => collect($course->assignments ?? []),
            ])->render();

            return response()->json(['html' => $html]);
        }

        return view('director.course-detail', [
            'course'       => $course,
            'studentCount' => $studentCount,
            'teacherCount' => $teacherCount,
            'roomsCount'   => $roomsCount,
        ]);
    }

    private function fetchLegacyCourseNamesFromTable()
    {
        if (! Schema::hasTable('tb_course') || ! Schema::hasColumn('tb_course', 'name_course')) {
            return collect();
        }

        return DB::table('tb_course')
            ->select('name_course')
            ->whereNotNull('name_course')
            ->orderBy('name_course')
            ->get()
            ->map(function ($row) {
                $decoded = $this->decodeLegacyThai((string) ($row->name_course ?? ''));
                $normalized = trim(preg_replace('/\s+/u', ' ', $decoded) ?? '');

                return $normalized !== '' ? $normalized : null;
            })
            ->filter()
            ->unique()
            ->values();
    }

    private function countTeachersFromLegacyTable(): ?int
    {
        if (! Schema::hasTable('tb_teacher')) {
            return null;
        }

        return (int) DB::table('tb_teacher')->count();
    }

    private function fetchTeacherDirectoryFromLegacyTable()
    {
        if (! Schema::hasTable('tb_teacher')) {
            return collect();
        }

        return DB::table('tb_teacher')
            ->selectRaw("
                id_teacher AS id,
                TRIM(id_title_name) AS title_name,
                TRIM(name) AS first_name,
                TRIM(surname) AS last_name
            ")
            ->orderBy('id_teacher')
            ->get()
            ->map(function ($teacher) {
                $title = $this->decodeLegacyThai((string) ($teacher->title_name ?? ''));
                $firstName = $this->decodeLegacyThai((string) ($teacher->first_name ?? ''));
                $lastName = $this->decodeLegacyThai((string) ($teacher->last_name ?? ''));
                $fullName = trim(collect([$title, $firstName, $lastName])->filter()->implode(' '));

                return [
                    'id' => (int) ($teacher->id ?? 0),
                    'name' => $fullName !== '' ? $fullName : '-',
                    'email' => '',
                ];
            })
            ->values();
    }

    private function buildTeacherStatusFromLegacyDirectory($teacherDirectoryAll, $courses): array
    {
        $courseLookupByTeacherName = $this->buildCourseLookupByTeacherName($courses);

        $teacherStatus = collect($teacherDirectoryAll)->map(function ($teacher) use ($courseLookupByTeacherName) {
            $teacherName = trim((string) ($teacher['name'] ?? ''));
            $lookupKeys = collect([$teacherName])
                ->map(fn ($name) => $this->normalizeTeacherName((string) $name))
                ->filter()
                ->unique()
                ->values();

            $courseDetails = $lookupKeys
                ->flatMap(fn ($lookupKey) => $courseLookupByTeacherName->get($lookupKey, collect()))
                ->unique('id')
                ->values();

            $isComplete = $courseDetails->isNotEmpty()
                && $courseDetails->every(fn ($detail) => (bool) ($detail['complete'] ?? false));

            return [
                'teacher' => [
                    'id' => (int) ($teacher['id'] ?? 0),
                    'name' => $teacherName !== '' ? $teacherName : '-',
                    'email' => (string) ($teacher['email'] ?? ''),
                ],
                'courses' => $courseDetails->all(),
                'complete' => $isComplete,
            ];
        });

        return [
            $teacherStatus->where('complete', true)->values(),
            $teacherStatus->where('complete', false)->values(),
        ];
    }

    private function buildCourseLookupByTeacherName($courses)
    {
        $courseRoomsByCourse = Schema::hasTable('course_rooms')
            ? DB::table('course_rooms')
                ->select('course_id', 'teacher_id', 'teacher_name')
                ->get()
                ->groupBy('course_id')
            : collect();

        $userNamesById = User::query()
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id');

        $lookup = collect();

        foreach (collect($courses) as $course) {
            $isLegacyGroupCourse = (bool) ($course->is_legacy_group_course ?? false);
            $hasHours = ! empty($course->teaching_hours);
            $hasAssignments = ! empty($course->assignments);
            $isComplete = isset($course->is_complete)
                ? (bool) $course->is_complete
                : ($hasHours && $hasAssignments);

            $courseDetail = [
                'id' => (int) ($course->id ?? 0),
                'name' => (string) ($course->name ?? ''),
                'grade' => (string) ($course->grade ?? ''),
                'complete' => $isComplete,
                'has_hours' => $hasHours,
                'has_assignments' => $hasAssignments,
            ];

            $nameCandidates = collect([
                $course->teacher->name ?? null,
                $userNamesById->get((int) ($course->user_id ?? 0)),
            ])->filter();

            $roomRows = $isLegacyGroupCourse
                ? collect()
                : collect($courseRoomsByCourse->get($course->id, collect()));
            $nameCandidates = $nameCandidates
                ->merge($roomRows->pluck('teacher_name')->filter())
                ->merge(
                    $roomRows
                        ->pluck('teacher_id')
                        ->filter()
                        ->map(fn ($teacherId) => $userNamesById->get((int) $teacherId))
                        ->filter()
                );

            $legacyCourseRooms = $isLegacyGroupCourse
                ? collect()
                : collect($course->course_rooms ?? [])
                    ->map(function ($item) {
                        if (! is_array($item)) {
                            return null;
                        }

                        return $item['teacher_name'] ?? $item['teacher'] ?? null;
                    })
                    ->filter();

            $nameCandidates = $nameCandidates->merge($legacyCourseRooms);

            $lookupKeys = $nameCandidates
                ->map(fn ($name) => $this->normalizeTeacherName((string) $name))
                ->filter()
                ->unique()
                ->values();

            foreach ($lookupKeys as $lookupKey) {
                $current = collect($lookup->get($lookupKey, collect()))
                    ->push($courseDetail)
                    ->unique('id')
                    ->values();
                $lookup->put($lookupKey, $current);
            }
        }

        return $lookup;
    }
    private function normalizeTeacherName(string $name): string
    {
        $normalized = $this->decodeLegacyThai($name);
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized)) ?? '';
        if ($normalized === '') {
            return '';
        }

        $prefixPattern = '/^(?:(?:\x{0E19}\x{0E32}\x{0E22})|(?:\x{0E19}\x{0E32}\x{0E07}\x{0E2A}\x{0E32}\x{0E27})|(?:\x{0E19}\x{0E32}\x{0E07})|(?:\x{0E04}\x{0E23}\x{0E39})|(?:\x{0E1C}\x{0E28})\.?|(?:\x{0E23}\x{0E28})\.?|(?:\x{0E28})\.?|(?:\x{0E14}\x{0E23})\.?|(?:\x{0E2D})\.?|(?:\x{0E27}\x{0E48}\x{0E32}\x{0E17}\x{0E35}\x{0E48}\x{0E23}\x{0E49}\x{0E2D}\x{0E22}\x{0E15}\x{0E23}\x{0E35})|mr\.?|mrs\.?|ms\.?)\s*/iu';
        $normalized = preg_replace($prefixPattern, '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', '', trim($normalized)) ?? '';

        return function_exists('mb_strtolower')
            ? mb_strtolower($normalized, 'UTF-8')
            : strtolower($normalized);
    }

    private function normalizeIsoDate($value): ?string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatDateTimeForDisplay($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '-';
        }

        try {
            return Carbon::parse($text)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $text;
        }
    }

    private function formatLegacyFullName($title, $firstName, $lastName): string
    {
        $titleText = $this->decodeLegacyThai((string) ($title ?? ''));
        $firstNameText = $this->decodeLegacyThai((string) ($firstName ?? ''));
        $lastNameText = $this->decodeLegacyThai((string) ($lastName ?? ''));

        return trim(collect([$titleText, $firstNameText, $lastNameText])->filter()->implode(' '));
    }
    private function decodeLegacyThai(?string $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $candidates = collect([$text])
            ->merge($this->decodeEncodingChain($text, 'ISO-8859-1', 3))
            ->merge($this->decodeEncodingChain($text, 'Windows-1252', 3))
            ->map(fn ($candidate) => trim((string) $candidate))
            ->filter(fn ($candidate) => $candidate !== '')
            ->unique()
            ->values();

        $best = $text;
        $bestScore = $this->scoreThaiDecodeCandidate($text);

        foreach ($candidates as $candidate) {
            $score = $this->scoreThaiDecodeCandidate($candidate);
            if ($score > $bestScore) {
                $best = $candidate;
                $bestScore = $score;
            }
        }

        return $best;
    }

    private function decodeEncodingChain(string $value, string $sourceEncoding, int $maxRounds = 2): array
    {
        $results = [];
        $current = $value;

        for ($round = 0; $round < $maxRounds; $round++) {
            $next = @mb_convert_encoding($current, $sourceEncoding, 'UTF-8');
            if (! is_string($next)) {
                break;
            }

            $next = trim($next);
            if ($next === '' || $next === $current || ! mb_check_encoding($next, 'UTF-8')) {
                break;
            }

            $results[] = $next;
            $current = $next;
        }

        return $results;
    }

    private function scoreThaiDecodeCandidate(string $value): int
    {
        $thaiChars = preg_match_all('/\p{Thai}/u', $value) ?: 0;
        $latinChars = preg_match_all('/[A-Za-z0-9]/u', $value) ?: 0;

        $mojibakeMarkers = preg_match_all('/(?:\x{00C3}|\x{00C2}|\x{00E0}\x{00B8}|\x{00E0}\x{00B9}|\x{00E0}\x{00BA}|\x{00E2}\x{20AC})/u', $value) ?: 0;
        $replacementChars = substr_count($value, "\u{FFFD}");

        return ($thaiChars * 12) + $latinChars - ($mojibakeMarkers * 8) - ($replacementChars * 10);
    }
}
