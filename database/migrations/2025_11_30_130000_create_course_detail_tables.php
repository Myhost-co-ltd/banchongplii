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
        Schema::create('course_teaching_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('term', 5)->nullable();
            $table->string('category')->nullable();
            $table->decimal('hours', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('course_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('term', 5)->nullable();
            $table->string('category')->nullable();
            $table->string('title');
            $table->decimal('hours', 10, 2)->nullable();
            $table->string('period', 100)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });

        Schema::create('course_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('term', 5)->nullable();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Backfill from existing JSON columns (if any)
        $now = now();
        DB::table('courses')
            ->select('id', 'term', 'teaching_hours', 'lessons', 'assignments')
            ->orderBy('id')
            ->chunk(100, function ($courses) use ($now) {
                foreach ($courses as $course) {
                    $courseTerm = $course->term ? (string) $course->term : null;

                    $hours = json_decode($course->teaching_hours ?? '[]', true);
                    if (is_array($hours)) {
                        foreach ($hours as $item) {
                            DB::table('course_teaching_hours')->insert([
                                'id'         => $item['id'] ?? (string) Str::uuid(),
                                'course_id'  => $course->id,
                                'term'       => $item['term'] ?? $courseTerm,
                                'category'   => $item['category'] ?? null,
                                'hours'      => isset($item['hours']) ? (float) $item['hours'] : null,
                                'unit'       => $item['unit'] ?? null,
                                'note'       => $item['note'] ?? null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }

                    $lessons = json_decode($course->lessons ?? '[]', true);
                    if (is_array($lessons)) {
                        foreach ($lessons as $item) {
                            DB::table('course_lessons')->insert([
                                'id'         => $item['id'] ?? (string) Str::uuid(),
                                'course_id'  => $course->id,
                                'term'       => $item['term'] ?? $courseTerm,
                                'category'   => $item['category'] ?? null,
                                'title'      => $item['title'] ?? '',
                                'hours'      => isset($item['hours']) ? (float) $item['hours'] : null,
                                'period'     => $item['period'] ?? null,
                                'details'    => $item['details'] ?? null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }

                    $assignments = json_decode($course->assignments ?? '[]', true);
                    if (is_array($assignments)) {
                        foreach ($assignments as $item) {
                            DB::table('course_assignments')->insert([
                                'id'         => $item['id'] ?? (string) Str::uuid(),
                                'course_id'  => $course->id,
                                'term'       => $item['term'] ?? $courseTerm,
                                'title'      => $item['title'] ?? '',
                                'due_date'   => $item['due_date'] ?? null,
                                'score'      => isset($item['score']) ? (float) $item['score'] : null,
                                'notes'      => $item['notes'] ?? null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_assignments');
        Schema::dropIfExists('course_lessons');
        Schema::dropIfExists('course_teaching_hours');
    }
};
