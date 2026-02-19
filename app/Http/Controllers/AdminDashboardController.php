<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $allTeachers = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
            : collect();
        $teacherIds = $allTeachers->pluck('id');
        $teacherIdLookup = $teacherIds->flip();

        $teacherCount = $allTeachers->count();
        $teacherListPayload = $allTeachers
            ->map(fn ($teacher) => [
                'name' => trim((string) ($teacher->name ?? '')) !== '' ? $teacher->name : '-',
                'email' => $teacher->email,
            ])
            ->values()
            ->all();

        $studentCount = Student::count();

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
        $hasClassroomCol = Schema::hasColumn('students', 'classroom');
        $studentColumns = $hasClassroomCol
            ? ['id', 'student_code', 'title', 'first_name', 'last_name', 'room', 'classroom']
            : ['id', 'student_code', 'title', 'first_name', 'last_name', 'room'];

        $students = Student::query()
            ->select($studentColumns)
            ->orderBy('student_code')
            ->get();

        $studentListPayload = $students
            ->map(function ($student) {
                $fullName = trim(($student->title ? $student->title . ' ' : '') . $student->first_name . ' ' . $student->last_name);

                return [
                    'student_code' => $student->student_code,
                    'name' => $fullName !== '' ? $fullName : '-',
                ];
            })
            ->values()
            ->all();

        $studentsByRoom = $students
            ->map(function ($student) use ($normalizeRoom) {
                $student->room_normalized = $normalizeRoom($student->classroom ?? $student->room ?? null);
                return $student;
            })
            ->groupBy(fn ($student) => $student->room_normalized ?? $unknownRoomLabel)
            ->sortKeys();

        $roomOptions = $studentsByRoom
            ->keys()
            ->reject(fn ($room) => $room === $unknownRoomLabel)
            ->values();

        $classroomCount = $roomOptions->count();

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

        // นับครูที่มี/ไม่มีชั่วโมงสอนครบจากข้อมูลหลักสูตร พร้อมรายการชื่อครู
        $courses = Course::select('id', 'user_id', 'name', 'grade', 'teaching_hours', 'assignments')
            ->with('teacher:id,name,email')
            ->get();

        $teacherStatus = $courses
            ->filter(fn ($course) => $course->teacher && $teacherIdLookup->has((int) $course->teacher->id))
            ->groupBy(fn ($course) => $course->teacher->id)
            ->map(function ($teacherCourses) {
                $hasIncompleteCourse = $teacherCourses->contains(function ($course) {
                    $hasHours = ! empty($course->teaching_hours);
                    $hasAssignments = ! empty($course->assignments);
                    return ! ($hasHours && $hasAssignments);
                });

                return [
                    'teacher' => $teacherCourses->first()->teacher,
                    'complete' => ! $hasIncompleteCourse,
                ];
            });

        $teacherIdsWithCourses = $teacherStatus
            ->keys()
            ->map(fn ($teacherId) => (int) $teacherId)
            ->values();

        $completeTeacherCount = $teacherStatus->filter(fn ($status) => $status['complete'])->count();
        $incompleteTeacherCount = $teacherStatus->filter(fn ($status) => ! $status['complete'])->count();

        $completeTeachers = $teacherStatus
            ->filter(fn ($status) => $status['complete'])
            ->pluck('teacher')
            ->filter();

        $teachersWithoutCourses = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)
                ->whereNotIn('id', $teacherIdsWithCourses)
                ->get(['id', 'name', 'email'])
            : collect();

        $incompleteTeachers = $teacherStatus
            ->filter(fn ($status) => ! $status['complete'])
            ->pluck('teacher')
            ->filter()
            ->merge($teachersWithoutCourses)
            ->values();

        $incompleteTeacherCount = $incompleteTeachers->count();

        return view('dashboards.admin', compact(
            'teacherCount',
            'teacherListPayload',
            'studentCount',
            'studentListPayload',
            'classroomCount',
            'roomOptions',
            'studentsByRoomPayload',
            'completeTeacherCount',
            'incompleteTeacherCount',
            'completeTeachers',
            'incompleteTeachers'
        ));
    }
}
