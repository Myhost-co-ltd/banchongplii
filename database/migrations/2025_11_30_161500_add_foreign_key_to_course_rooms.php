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

        $constraint = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'course_rooms'
              AND COLUMN_NAME = 'course_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
            DB::statement(sprintf(
                'ALTER TABLE course_rooms DROP FOREIGN KEY `%s`',
                $constraint->CONSTRAINT_NAME
            ));
        }

        Schema::table('course_rooms', function (Blueprint $table) {
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_rooms')) {
            return;
        }

        $constraint = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'course_rooms'
              AND COLUMN_NAME = 'course_id'
              AND REFERENCED_TABLE_NAME = 'courses'
            LIMIT 1
        ");

        if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
            DB::statement(sprintf(
                'ALTER TABLE course_rooms DROP FOREIGN KEY `%s`',
                $constraint->CONSTRAINT_NAME
            ));
        }
    }
};
