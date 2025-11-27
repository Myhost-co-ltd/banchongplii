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
            if (! Schema::hasColumn('course_rooms', 'teacher_name')) {
                $table->string('teacher_name', 255)->nullable()->after('teacher_id');
            }
        });

        if (Schema::hasColumn('course_rooms', 'teacher_name')) {
            DB::table('course_rooms')
                ->leftJoin('users', 'course_rooms.teacher_id', '=', 'users.id')
                ->update(['course_rooms.teacher_name' => DB::raw('users.name')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_rooms')) {
            return;
        }

        Schema::table('course_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('course_rooms', 'teacher_name')) {
                $table->dropColumn('teacher_name');
            }
        });
    }
};
