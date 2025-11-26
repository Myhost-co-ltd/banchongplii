<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_rooms')) {
            return;
        }

        Schema::table('course_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('course_rooms', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('course_id')->constrained('users')->nullOnDelete();
            }
        });

        // Backfill with course owner
        if (Schema::hasColumn('course_rooms', 'teacher_id')) {
            DB::table('course_rooms')
                ->join('courses', 'course_rooms.course_id', '=', 'courses.id')
                ->update(['course_rooms.teacher_id' => DB::raw('courses.user_id')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_rooms')) {
            return;
        }

        Schema::table('course_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('course_rooms', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
        });
    }
};
