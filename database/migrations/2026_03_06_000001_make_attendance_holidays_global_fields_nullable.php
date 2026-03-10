<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('course_attendance_holidays')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `course_attendance_holidays` MODIFY COLUMN `course_id` BIGINT UNSIGNED NULL'
        );

        DB::statement(
            'ALTER TABLE `course_attendance_holidays` MODIFY COLUMN `term` VARCHAR(20) NULL'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('course_attendance_holidays')) {
            return;
        }

        DB::table('course_attendance_holidays')
            ->whereNull('course_id')
            ->orWhereNull('term')
            ->delete();

        DB::statement(
            'ALTER TABLE `course_attendance_holidays` MODIFY COLUMN `course_id` BIGINT UNSIGNED NOT NULL'
        );

        DB::statement(
            'ALTER TABLE `course_attendance_holidays` MODIFY COLUMN `term` VARCHAR(20) NOT NULL'
        );
    }
};
