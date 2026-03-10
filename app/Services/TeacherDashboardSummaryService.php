<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherDashboardSummaryService
{
    public function build(): array
    {
        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $teacherUsers = $teacherRoleId
            ? User::query()
                ->where('role_id', $teacherRoleId)
                ->select('id', 'name', 'email', 'major')
                ->orderBy('name')
                ->get()
            : collect();

        $legacyTeacherDirectory = $this->fetchTeachersFromLegacyTable();

        $courses = Course::query()
            ->select('id', 'user_id', 'name', 'grade', 'teaching_hours', 'assignments')
            ->with('teacher:id,name,email,major')
            ->get();

        return $this->summarize($teacherUsers, $legacyTeacherDirectory, $courses);
    }

    public function summarize(Collection $teacherUsers, Collection $legacyTeacherDirectory, Collection $courses): array
    {
        $teacherUsers = $teacherUsers->values();
        $legacyTeacherDirectory = $legacyTeacherDirectory
            ->map(fn ($teacher) => $this->formatTeacherDirectoryItem($teacher))
            ->values();

        $teacherDirectoryAll = $legacyTeacherDirectory->isNotEmpty()
            ? $legacyTeacherDirectory
            : $teacherUsers
                ->map(fn ($teacher) => $this->formatTeacherDirectoryItem($teacher))
                ->values();

        $teacherIdLookup = $teacherUsers
            ->pluck('id')
            ->map(fn ($teacherId) => (int) $teacherId)
            ->filter(fn ($teacherId) => $teacherId > 0)
            ->flip();

        $teacherStatus = $courses
            ->filter(function ($course) use ($teacherIdLookup) {
                $teacherId = (int) data_get($course, 'teacher.id', 0);

                return $teacherId > 0 && $teacherIdLookup->has($teacherId);
            })
            ->groupBy(fn ($course) => (int) data_get($course, 'teacher.id', 0))
            ->map(function ($teacherCourses) {
                $teacherCourses = collect($teacherCourses)->values();

                return $this->formatTeacherStatus(
                    data_get($teacherCourses->first(), 'teacher'),
                    $teacherCourses
                );
            });

        $teacherIdsWithCourses = $teacherStatus
            ->keys()
            ->map(fn ($teacherId) => (int) $teacherId)
            ->values()
            ->all();

        $completeTeacherStatuses = $teacherStatus
            ->where('complete', true)
            ->values();

        $incompleteTeacherStatuses = $teacherStatus
            ->where('complete', false)
            ->values()
            ->merge(
                $teacherUsers
                    ->whereNotIn('id', $teacherIdsWithCourses)
                    ->map(fn ($teacher) => $this->formatTeacherStatus($teacher, collect()))
                    ->values()
            )
            ->values();

        return [
            'teacherUsers' => $teacherUsers,
            'teacherDirectoryAll' => $teacherDirectoryAll,
            'teacherCount' => $teacherDirectoryAll->count(),
            'teacherListPayload' => $teacherDirectoryAll
                ->map(fn ($teacher) => [
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                ])
                ->values()
                ->all(),
            'completeTeacherCount' => $completeTeacherStatuses->count(),
            'incompleteTeacherCount' => $incompleteTeacherStatuses->count(),
            'completeTeacherStatuses' => $completeTeacherStatuses,
            'incompleteTeacherStatuses' => $incompleteTeacherStatuses,
            'completeTeacherListPayload' => $completeTeacherStatuses
                ->map(fn ($status) => $status['teacher'])
                ->values()
                ->all(),
            'incompleteTeacherListPayload' => $incompleteTeacherStatuses
                ->map(fn ($status) => $status['teacher'])
                ->values()
                ->all(),
        ];
    }

    private function formatTeacherStatus($teacher, Collection $teacherCourses): array
    {
        $courseDetails = $teacherCourses
            ->map(function ($course) {
                $hasHours = ! empty(data_get($course, 'teaching_hours'));
                $hasAssignments = ! empty(data_get($course, 'assignments'));

                return [
                    'id' => (int) data_get($course, 'id', 0),
                    'name' => (string) data_get($course, 'name', ''),
                    'grade' => (string) data_get($course, 'grade', ''),
                    'complete' => $hasHours && $hasAssignments,
                    'has_hours' => $hasHours,
                    'has_assignments' => $hasAssignments,
                ];
            })
            ->values();

        $isComplete = $courseDetails->isNotEmpty()
            && $courseDetails->every(fn ($course) => (bool) ($course['complete'] ?? false));

        return [
            'teacher' => $this->formatTeacherStatusTeacher($teacher),
            'courses' => $courseDetails->all(),
            'complete' => $isComplete,
        ];
    }

    private function formatTeacherStatusTeacher($teacher): array
    {
        return [
            'id' => (int) data_get($teacher, 'id', 0),
            'name' => $this->normalizeDisplayText(data_get($teacher, 'name')),
            'email' => trim((string) data_get($teacher, 'email', '')),
            'major' => data_get($teacher, 'major'),
        ];
    }

    private function formatTeacherDirectoryItem($teacher): array
    {
        return [
            'id' => (int) data_get($teacher, 'id', data_get($teacher, 'id_teacher', 0)),
            'name' => $this->normalizeDisplayText(
                data_get($teacher, 'name', data_get($teacher, 'full_name', ''))
            ),
            'email' => trim((string) data_get($teacher, 'email', '')),
        ];
    }

    private function normalizeDisplayText($value): string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : '-';
    }

    private function fetchTeachersFromLegacyTable(): Collection
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

                return [
                    'id' => (int) ($teacher->id_teacher ?? 0),
                    'name' => $fullName !== '' ? $fullName : '-',
                    'email' => '',
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
