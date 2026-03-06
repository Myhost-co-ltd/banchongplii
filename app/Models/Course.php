<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'temporary_teacher_id',
        'temporary_until',
        'temporary_assigned_by',
        'name',
        'grade',
        'rooms',
        'term',
        'year',
        'description',
        'teaching_hours',
        'lessons',
        'assignments',
        'assignment_cap',
    ];

    protected $casts = [
        'rooms' => 'array',
        'temporary_until' => 'date',
        'teaching_hours' => 'array',
        'lessons' => 'array',
        'assignments' => 'array',
    ];

    /**
     * Keep Unicode and slashes unescaped when storing JSON fields (rooms, etc.)
     * so values stay readable like "ป.1/1" instead of "\u0e1b.1\/1".
     */
    protected $jsonEncodingOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function temporaryTeacher()
    {
        return $this->belongsTo(User::class, 'temporary_teacher_id');
    }

    public function temporaryAssigner()
    {
        return $this->belongsTo(User::class, 'temporary_assigned_by');
    }

    public function scopeAccessibleByTeacher(Builder $query, int $teacherId, ?string $onDate = null): Builder
    {
        $date = $onDate ?: now(config('app.timezone', 'Asia/Bangkok'))->toDateString();

        return $query->where(function (Builder $outer) use ($teacherId, $date): void {
            $outer->where(function (Builder $owner) use ($teacherId, $date): void {
                $owner->where('user_id', $teacherId)
                    ->where(function (Builder $substituteState) use ($teacherId, $date): void {
                        $substituteState->whereNull('temporary_teacher_id')
                            ->orWhereNull('temporary_until')
                            ->orWhereDate('temporary_until', '<', $date)
                            ->orWhere('temporary_teacher_id', $teacherId);
                    });
            })->orWhere(function (Builder $activeSubstitute) use ($teacherId, $date): void {
                $activeSubstitute->where('temporary_teacher_id', $teacherId)
                    ->whereNotNull('temporary_until')
                    ->whereDate('temporary_until', '>=', $date);
            });
        });
    }

    public function scopeVisibleToTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where(function (Builder $visibility) use ($teacherId): void {
            $visibility->where('user_id', $teacherId)
                ->orWhere('temporary_teacher_id', $teacherId);
        });
    }

    public static function clearExpiredTemporaryAssignments(?string $onDate = null): int
    {
        $date = $onDate ?: now(config('app.timezone', 'Asia/Bangkok'))->toDateString();

        return static::query()
            ->whereNotNull('temporary_teacher_id')
            ->whereNotNull('temporary_until')
            ->whereDate('temporary_until', '<', $date)
            ->update([
                'temporary_teacher_id' => null,
                'temporary_until' => null,
                'temporary_assigned_by' => null,
                'updated_at' => now(),
            ]);
    }

    public function isTemporaryTeacherActive(?string $onDate = null): bool
    {
        if (! $this->temporary_teacher_id || ! $this->temporary_until) {
            return false;
        }

        $date = $onDate ?: now(config('app.timezone', 'Asia/Bangkok'))->toDateString();
        $untilDate = $this->temporary_until instanceof Carbon
            ? $this->temporary_until->toDateString()
            : Carbon::parse($this->temporary_until, config('app.timezone', 'Asia/Bangkok'))->toDateString();

        return $untilDate >= $date;
    }

    public function responsibleTeacherId(?string $onDate = null): ?int
    {
        if ($this->isTemporaryTeacherActive($onDate)) {
            return (int) $this->temporary_teacher_id;
        }

        return $this->user_id ? (int) $this->user_id : null;
    }

    public function isTeacherResponsible(int $teacherId, ?string $onDate = null): bool
    {
        return $this->responsibleTeacherId($onDate) === $teacherId;
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
        });

        static::saved(function (Course $course): void {
            if ($course->wasChanged('teaching_hours')) {
                $course->syncTeachingHoursTable();
            }

            if ($course->wasChanged('lessons')) {
                $course->syncLessonsTable();
            }

            if ($course->wasChanged('assignments')) {
                $course->syncAssignmentsTable();
            }

            if ($course->wasChanged('rooms')) {
                $course->syncCourseRoomsTable();
            }
        });
    }

    public function syncTeachingHoursTable(): void
    {
        $rows = $this->mapCourseArrayToRows(
            $this->teaching_hours ?? [],
            [
                'term'     => fn ($item) => $item['term'] ?? $this->term,
                'category' => fn ($item) => $item['category'] ?? null,
                'hours'    => fn ($item) => isset($item['hours']) ? (float) $item['hours'] : null,
                'note'     => fn ($item) => $item['note'] ?? null,
            ]
        );

        DB::transaction(function () use ($rows): void {
            DB::table('course_teaching_hours')
                ->where('course_id', $this->id)
                ->delete();

            if ($rows->isNotEmpty()) {
                DB::table('course_teaching_hours')->insert($rows->all());
            }
        });
    }

    public function syncLessonsTable(): void
    {
        $rows = $this->mapCourseArrayToRows(
            $this->lessons ?? [],
            [
                'term'     => fn ($item) => $item['term'] ?? $this->term,
                'category' => fn ($item) => $item['category'] ?? null,
                'title'    => fn ($item) => $item['title'] ?? '',
                'hours'    => fn ($item) => isset($item['hours']) ? (float) $item['hours'] : null,
                'period'   => fn ($item) => $item['period'] ?? null,
                'details'  => fn ($item) => $item['details'] ?? null,
            ]
        );

        DB::transaction(function () use ($rows): void {
            DB::table('course_lessons')
                ->where('course_id', $this->id)
                ->delete();

            if ($rows->isNotEmpty()) {
                DB::table('course_lessons')->insert($rows->all());
            }
        });
    }

    public function syncAssignmentsTable(): void
    {
        $rows = $this->mapCourseArrayToRows(
            $this->assignments ?? [],
            [
                'term'     => fn ($item) => $item['term'] ?? $this->term,
                'title'    => fn ($item) => $item['title'] ?? '',
                'due_date' => fn ($item) => $item['due_date'] ?? null,
                'score'    => fn ($item) => isset($item['score']) ? (float) $item['score'] : null,
                'notes'    => fn ($item) => $item['notes'] ?? null,
            ]
        );

        DB::transaction(function () use ($rows): void {
            DB::table('course_assignments')
                ->where('course_id', $this->id)
                ->delete();

            if ($rows->isNotEmpty()) {
                DB::table('course_assignments')->insert($rows->all());
            }
        });
    }

    public function syncCourseRoomsTable(): void
    {
        $items = $this->rooms ?? [];
        $now = now();
        $courseId = $this->id;
        $hasTeacherId = Schema::hasColumn('course_rooms', 'teacher_id');
        $hasTeacherName = Schema::hasColumn('course_rooms', 'teacher_name');
        $hasTerm = Schema::hasColumn('course_rooms', 'term');

        $rows = collect($items)->map(function ($item) use ($courseId, $now) {
            $isArray = is_array($item);
            $room = $isArray ? ($item['room'] ?? $item['name'] ?? null) : (string) $item;
            $term = $isArray ? ($item['term'] ?? null) : null;

            return [
                'id'         => $isArray ? ($item['id'] ?? (string) Str::uuid()) : (string) Str::uuid(),
                'course_id'  => $courseId,
                'teacher_id' => $this->user_id,
                'term'       => $term ?? $this->term,
                'room'       => $room,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->map(function ($row) use ($hasTeacherId, $hasTeacherName, $hasTerm) {
            if (! $hasTeacherId) {
                unset($row['teacher_id']);
            }
            if (! $hasTeacherName) {
                unset($row['teacher_name']);
            }
            if (! $hasTerm) {
                unset($row['term']);
            }
            return $row;
        })->filter(fn ($row) => $row['room'] !== null && $row['room'] !== '');

        DB::transaction(function () use ($rows): void {
            DB::table('course_rooms')->where('course_id', $this->id)->delete();

            if ($rows->isNotEmpty()) {
                DB::table('course_rooms')->insert($rows->all());
            }
        });
    }

    private function mapCourseArrayToRows(array $items, array $fieldMapping): Collection
    {
        $now = now();
        $courseId = $this->id;

        return collect($items)->map(function ($item) use ($fieldMapping, $courseId, $now) {
            $id = $item['id'] ?? (string) Str::uuid();

            $base = [
                'id'         => $id,
                'course_id'  => $courseId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($fieldMapping as $field => $resolver) {
                $base[$field] = $resolver($item);
            }

            return $base;
        });
    }
}
