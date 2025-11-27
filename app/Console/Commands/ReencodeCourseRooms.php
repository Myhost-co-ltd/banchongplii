<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class ReencodeCourseRooms extends Command
{
    protected $signature = 'course:reencode-rooms {course_id? : Only re-encode a specific course id}';

    protected $description = 'Rewrite rooms JSON using unescaped Unicode/slashes for readability.';

    public function handle(): int
    {
        $query = Course::query();

        if ($id = $this->argument('course_id')) {
            $query->whereKey($id);
        }

        $count = 0;

        $query->orderBy('id')->chunkById(100, function ($courses) use (&$count): void {
            foreach ($courses as $course) {
                $original = $course->rooms;
                $course->rooms = $original ?? [];
                $course->save();
                $count++;
            }
        });

        $this->info("Re-encoded rooms for {$count} course(s).");

        return self::SUCCESS;
    }
}
