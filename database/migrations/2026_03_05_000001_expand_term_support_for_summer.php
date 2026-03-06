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

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'term')) {
            DB::statement("ALTER TABLE `courses` MODIFY COLUMN `term` ENUM('1','2','summer') NULL");
        }

        $this->alterVarcharTermLength('course_teaching_hours', true, 20);
        $this->alterVarcharTermLength('course_lessons', true, 20);
        $this->alterVarcharTermLength('course_assignments', true, 20);
        $this->alterVarcharTermLength('course_rooms', true, 20);
        $this->alterVarcharTermLength('course_attendance_records', false, 20);
        $this->alterVarcharTermLength('course_attendance_holidays', false, 20);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'term')) {
            DB::table('courses')->where('term', 'summer')->update(['term' => null]);
            DB::statement("ALTER TABLE `courses` MODIFY COLUMN `term` ENUM('1','2') NULL");
        }

        $this->normalizeSummerBeforeShrink('course_teaching_hours', true);
        $this->normalizeSummerBeforeShrink('course_lessons', true);
        $this->normalizeSummerBeforeShrink('course_assignments', true);
        $this->normalizeSummerBeforeShrink('course_rooms', true);
        $this->normalizeSummerBeforeShrink('course_attendance_records', false);
        $this->normalizeSummerBeforeShrink('course_attendance_holidays', false);

        $this->alterVarcharTermLength('course_teaching_hours', true, 5);
        $this->alterVarcharTermLength('course_lessons', true, 5);
        $this->alterVarcharTermLength('course_assignments', true, 5);
        $this->alterVarcharTermLength('course_rooms', true, 5);
        $this->alterVarcharTermLength('course_attendance_records', false, 5);
        $this->alterVarcharTermLength('course_attendance_holidays', false, 5);
    }

    private function alterVarcharTermLength(string $table, bool $nullable, int $length): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'term')) {
            return;
        }

        $nullClause = $nullable ? 'NULL' : 'NOT NULL';
        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY COLUMN `term` VARCHAR(%d) %s',
            $table,
            $length,
            $nullClause
        ));
    }

    private function normalizeSummerBeforeShrink(string $table, bool $nullable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'term')) {
            return;
        }

        if ($nullable) {
            DB::table($table)->where('term', 'summer')->update(['term' => null]);
            return;
        }

        DB::table($table)->where('term', 'summer')->update(['term' => '2']);
    }
};

