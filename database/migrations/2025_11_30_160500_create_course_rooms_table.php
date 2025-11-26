<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('course_rooms');

        Schema::create('course_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('term', 5)->nullable();
            $table->string('room', 100)->nullable();
            $table->timestamps();
        });

        // Backfill from existing course_rooms / rooms JSON columns.
        $now = now();
        $hasCourseRooms = Schema::hasColumn('courses', 'course_rooms');

        DB::table('courses')
            ->select('id', 'term', 'rooms', $hasCourseRooms ? 'course_rooms' : DB::raw('NULL as course_rooms'))
            ->orderBy('id')
            ->chunk(100, function ($courses) use ($now, $hasCourseRooms) {
                foreach ($courses as $course) {
                    $items = $hasCourseRooms ? json_decode($course->course_rooms ?? '[]', true) : [];
                    if (! is_array($items) || empty($items)) {
                        $items = json_decode($course->rooms ?? '[]', true);
                    }

                    if (! is_array($items)) {
                        continue;
                    }

                    foreach ($items as $item) {
                        $isArray = is_array($item);
                        $room = $isArray ? ($item['room'] ?? $item['name'] ?? null) : (string) $item;
                        if ($room === null || $room === '') {
                            continue;
                        }

                        DB::table('course_rooms')->insert([
                            'id'         => $isArray ? ($item['id'] ?? (string) Str::uuid()) : (string) Str::uuid(),
                            'course_id'  => $course->id,
                            'term'       => $isArray ? ($item['term'] ?? $course->term) : $course->term,
                            'room'       => $room,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_rooms');
    }
};
