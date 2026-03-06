<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');
        $legacyTeachers = $this->fetchTeachersFromLegacyTable();
        $allTeachers = $teacherRoleId
            ? User::where('role_id', $teacherRoleId)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
            : collect();
        $teacherIds = $allTeachers->pluck('id');
        $teacherIdLookup = $teacherIds->flip();

        $teacherCount = $legacyTeachers->isNotEmpty() ? $legacyTeachers->count() : $allTeachers->count();
        $teacherListPayload = $legacyTeachers->isNotEmpty()
            ? $legacyTeachers
                ->map(fn ($teacher) => [
                    'name' => trim((string) ($teacher->full_name ?? '')) !== '' ? $teacher->full_name : '-',
                    'email' => '-',
                ])
                ->values()
                ->all()
            : $allTeachers
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

    private function fetchTeachersFromLegacyTable()
    {
        if (! Schema::hasTable('tb_teacher')) {
            return collect();
        }

        return DB::table('tb_teacher')
            ->selectRaw("
                id_teacher,
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

                return (object) [
                    'id_teacher' => (int) ($teacher->id_teacher ?? 0),
                    'full_name' => $fullName !== '' ? $fullName : '-',
                ];
            })
            ->values();
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
