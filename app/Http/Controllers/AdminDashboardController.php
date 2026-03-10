<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\TeacherDashboardSummaryService;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function __invoke(TeacherDashboardSummaryService $teacherDashboardSummary)
    {
        $teacherSummary = $teacherDashboardSummary->build();
        $teacherCount = $teacherSummary['teacherCount'];
        $teacherListPayload = $teacherSummary['teacherListPayload'];
        $completeTeacherCount = $teacherSummary['completeTeacherCount'];
        $incompleteTeacherCount = $teacherSummary['incompleteTeacherCount'];
        $completeTeachers = collect($teacherSummary['completeTeacherListPayload'])
            ->map(fn (array $teacher) => (object) $teacher)
            ->values();
        $incompleteTeachers = collect($teacherSummary['incompleteTeacherListPayload'])
            ->map(fn (array $teacher) => (object) $teacher)
            ->values();

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
