<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class SyncCourseDetails extends Command
{
    protected $signature = 'course:sync-details {course_id? : Limit sync to a single course ID}';

    protected $description = 'Sync course teaching hours, lessons, and assignments JSON into relational tables.';

    public function handle(): int
    {
        $query = Course::query();

        if ($courseId = $this->argument('course_id')) {
            $query->whereKey($courseId);
        }

        $synced = 0;

        $query->orderBy('id')->chunkById(100, function ($courses) use (&$synced): void {
            foreach ($courses as $course) {
                $course->syncTeachingHoursTable();
                $course->syncLessonsTable();
                $course->syncAssignmentsTable();
                $synced++;
            }
        });

        $this->info("Synced {$synced} course(s).");

        return self::SUCCESS;
    }
}
