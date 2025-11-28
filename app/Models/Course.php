<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'grade',
        'rooms',
        'course_rooms',
        'term',
        'year',
        'description',
        'teaching_hours',
        'lessons',
        'assignments',
    ];

    protected $casts = [
        'rooms' => 'array',
        'course_rooms' => 'array',
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

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            // Keep rooms and course_rooms in sync so both columns reflect the same array.
            if (! is_null($course->rooms)) {
                $course->course_rooms = $course->course_rooms ?? $course->rooms;
            } elseif (! is_null($course->course_rooms)) {
                $course->rooms = $course->course_rooms;
            }
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

            if ($course->wasChanged('rooms') || $course->wasChanged('course_rooms')) {
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
        $items = $this->course_rooms ?? $this->rooms ?? [];
        $now = now();
        $courseId = $this->id;
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
