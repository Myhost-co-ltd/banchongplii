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
        WHEN c.rooms IS NOT NULL AND JSON_VALID(c.rooms) THEN JSON_PRETTY(c.rooms)
        ELSE c.rooms
    END AS rooms_pretty,
    CASE
        WHEN c.teaching_hours IS NOT NULL AND JSON_VALID(c.teaching_hours) THEN JSON_PRETTY(c.teaching_hours)
        ELSE c.teaching_hours
    END AS teaching_hours_pretty,
    CASE
        WHEN c.lessons IS NOT NULL AND JSON_VALID(c.lessons) THEN JSON_PRETTY(c.lessons)
        ELSE c.lessons
    END AS lessons_pretty,
    CASE
        WHEN c.assignments IS NOT NULL AND JSON_VALID(c.assignments) THEN JSON_PRETTY(c.assignments)
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
