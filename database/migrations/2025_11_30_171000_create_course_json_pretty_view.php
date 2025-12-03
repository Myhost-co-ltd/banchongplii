<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW course_json_pretty_view AS
SELECT
    c.id AS course_id,
    c.name,
    c.grade,
    c.term,
    c.year,
    CASE
        WHEN c.rooms IS NOT NULL AND JSON_VALID(c.rooms) THEN CAST(c.rooms AS CHAR CHARACTER SET utf8mb4)
        ELSE c.rooms
    END AS rooms_pretty,
    CASE
        WHEN c.teaching_hours IS NOT NULL AND JSON_VALID(c.teaching_hours) THEN CAST(c.teaching_hours AS CHAR CHARACTER SET utf8mb4)
        ELSE c.teaching_hours
    END AS teaching_hours_pretty,
    CASE
        WHEN c.lessons IS NOT NULL AND JSON_VALID(c.lessons) THEN CAST(c.lessons AS CHAR CHARACTER SET utf8mb4)
        ELSE c.lessons
    END AS lessons_pretty,
    CASE
        WHEN c.assignments IS NOT NULL AND JSON_VALID(c.assignments) THEN CAST(c.assignments AS CHAR CHARACTER SET utf8mb4)
        ELSE c.assignments
    END AS assignments_pretty,
    c.created_at,
    c.updated_at
FROM courses c;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS course_json_pretty_view');
    }
};
