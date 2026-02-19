<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DirectorController extends Controller
{
    public function dashboard()
    {
        $studentCount = Student::count();

        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $teacherIds = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)->pluck('id')
            : collect();
        $teacherIdLookup = $teacherIds->flip();
        $teacherCount = $teacherIds->count();

        $homeroomTeachers = $teacherRoleId
            ? User::query()
                ->where('role_id', $teacherRoleId)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'major'])
            : collect();

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
            ->groupBy(fn ($student) => $student->room_normalized ?? 'ไม่ระบุห้อง')
            ->sortKeys();

        $roomOptions = $studentsByRoom
            ->reject(fn ($_students, $room) => $room === 'ไม่ระบุห้อง')
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

        $majorPresets = [
            'คณิตศาสตร์',
            'วิทยาศาสตร์',
            'ภาษาไทย',
            'ภาษาอังกฤษ',
            'สังคมศึกษา',
            'สุขศึกษา/พลศึกษา',
            'ศิลปะ',
            'ดนตรี',
            'การงานอาชีพ',
            'คอมพิวเตอร์',
        ];

        $allMajors = collect($majorPresets)
            ->map(fn ($major) => trim($major))
            ->merge(
                $homeroomTeachers
                    ->pluck('major')
                    ->map(fn ($major) => trim((string) $major))
                    ->filter()
            )
            ->unique()
            ->values();

        $teachersByMajor = $homeroomTeachers
            ->filter(fn ($teacher) => $teacher->major)
            ->groupBy(fn ($teacher) => trim($teacher->major));

        $courses = Course::with('teacher:id,name,email')
            ->latest()
            ->get();

        // รวมครูจากรายวิชา (course) เข้ามาใน mapping วิชาเอก ด้วยชื่อวิชาเป็นกุญแจ
        foreach ($courses as $course) {
            $majorKey = trim((string) $course->name);
            if ($majorKey === '') {
                continue;
            }

            $current = collect($teachersByMajor->get($majorKey, collect()));

            if ($course->teacher) {
                $current = $current
                    ->push($course->teacher)
                    ->unique('id')
                    ->values();
            }

            // เก็บไว้แม้ยังไม่มีครู เพื่อให้วิชาเอกปรากฏครบตามชื่อรายวิชา
            $teachersByMajor->put($majorKey, $current);
        }

        // อัปเดตวิชาเอกทั้งหมดให้รวมชื่อรายวิชาที่มีอยู่จริงด้วย
        $allMajors = $allMajors
            ->merge(
                $courses
                    ->pluck('name')
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
            )
            ->unique()
            ->values();

        $teacherWithMajorCount = $teachersByMajor
            ->flatten(1)
            ->unique('id')
            ->count();

        $teacherStatus = $courses
            ->filter(fn ($course) => $course->teacher && $teacherIdLookup->has((int) $course->teacher->id))
            ->groupBy(fn ($course) => $course->teacher->id)
            ->map(function ($teacherCourses) {
                $teacher = $teacherCourses->first()->teacher;

                $courseDetails = $teacherCourses->map(function ($course) {
                    $hasHours = ! empty($course->teaching_hours);
                    $hasAssignments = ! empty($course->assignments);

                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'grade' => $course->grade,
                        'complete' => $hasHours && $hasAssignments,
                        'has_hours' => $hasHours,
                        'has_assignments' => $hasAssignments,
                    ];
                })->values();

                $isComplete = $courseDetails->every(fn ($detail) => $detail['complete']);

                return [
                    'teacher' => [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'major' => $teacher->major,
                    ],
                    'courses' => $courseDetails,
                    'complete' => $isComplete,
                ];
            });

        $teacherIdsWithCourses = $teacherStatus
            ->keys()
            ->map(fn ($teacherId) => (int) $teacherId)
            ->values();

        $completeTeachers = $teacherStatus->where('complete', true)->values();
        $incompleteTeachers = $teacherStatus->where('complete', false)->values();

        $teachersWithoutCourses = $teacherRoleId
            ? User::query()
                ->where('role_id', $teacherRoleId)
                ->whereNotIn('id', $teacherIdsWithCourses)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'major'])
            : collect();

        $incompleteTeachers = $incompleteTeachers
            ->merge(
                $teachersWithoutCourses->map(fn ($teacher) => [
                    'teacher' => [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'major' => $teacher->major,
                    ],
                    'courses' => [],
                    'complete' => false,
                ])
            )
            ->values();

        $completeTeacherCount = $completeTeachers->count();
        $incompleteTeacherCount = $incompleteTeachers->count();

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
        ]);
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

        $unknownRoomLabel = 'ไม่ระบุห้อง';
        $unknownGradeLabel = 'ไม่ระบุชั้น';
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
        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $baseCourseQuery = Course::query()
            ->when($teacherRoleId, fn ($query) => $query->whereIn('user_id', function ($sub) use ($teacherRoleId) {
                $sub->select('id')->from('users')->where('role_id', $teacherRoleId);
            }));

        $search = trim((string) $request->input('q', ''));

        $courseOptions = (clone $baseCourseQuery)
            ->select('name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $selectedCourse = (string) $request->input('course', '');
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

        $selectedGrade = (string) $request->input('grade', '');
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
            // ต้องดึง teaching_hours และ assignments ด้วยเพื่อใช้คำนวณสถานะในหน้า view
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

        $teacherCount = $courses->pluck('teacher')->filter()->unique('id')->count();
        $studentCount = Student::count();
        $roomsCount = $courses->flatMap(fn ($course) => $course->rooms ?? [])->unique()->count();
        $teacherStatus = $courses
            ->filter(fn ($course) => $course->teacher)
            ->groupBy(fn ($course) => $course->teacher->id)
            ->map(function ($teacherCourses) {
                $hasIncompleteCourse = $teacherCourses->contains(function ($course) {
                    $hasHours = ! empty($course->teaching_hours);
                    $hasAssignments = ! empty($course->assignments);
                    return ! ($hasHours && $hasAssignments);
                });

                return [
                    'complete' => ! $hasIncompleteCourse,
                ];
            });

        $completeTeacherCount = $teacherStatus->filter(fn ($status) => $status['complete'])->count();
        $incompleteTeacherCount = $teacherStatus->filter(fn ($status) => ! $status['complete'])->count();

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
        ]);
    }

    public function courseDetail(Course $course)
    {
        $course->load('teacher:id,name,email');

        $studentCount = Student::count();
        $teacherCount = User::whereHas('role', fn ($q) => $q->where('name', 'teacher'))->count();
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
}
