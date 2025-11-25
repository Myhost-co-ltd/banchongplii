<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW course_teaching_hours_view AS
SELECT
    c.id AS course_id,
    c.name AS course_name,
    c.grade,
    c.term AS course_term,
    c.year AS course_year,
    JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].id'))) AS item_id,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].term'))), c.term) AS term,
    JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].category'))) AS category,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].hours'))) AS DECIMAL(10,2)) AS hours,
    JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].unit'))) AS unit,
    JSON_UNQUOTE(JSON_EXTRACT(c.teaching_hours, CONCAT('$[', seq.idx, '].note'))) AS note,
    c.created_at AS course_created_at,
    c.updated_at AS course_updated_at
FROM courses c
JOIN (
    SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS idx
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) AS seq ON seq.idx < JSON_LENGTH(c.teaching_hours)
WHERE c.teaching_hours IS NOT NULL
  AND JSON_TYPE(c.teaching_hours) = 'ARRAY'
  AND JSON_LENGTH(c.teaching_hours) > 0;
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW course_lessons_view AS
SELECT
    c.id AS course_id,
    c.name AS course_name,
    c.grade,
    c.term AS course_term,
    c.year AS course_year,
    JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].id'))) AS item_id,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].term'))), c.term) AS term,
    JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].category'))) AS category,
    JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].title'))) AS title,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].hours'))) AS DECIMAL(10,2)) AS hours,
    JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].period'))) AS period,
    JSON_UNQUOTE(JSON_EXTRACT(c.lessons, CONCAT('$[', seq.idx, '].details'))) AS details,
    c.created_at AS course_created_at,
    c.updated_at AS course_updated_at
FROM courses c
JOIN (
    SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS idx
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) AS seq ON seq.idx < JSON_LENGTH(c.lessons)
WHERE c.lessons IS NOT NULL
  AND JSON_TYPE(c.lessons) = 'ARRAY'
  AND JSON_LENGTH(c.lessons) > 0;
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW course_assignments_view AS
SELECT
    c.id AS course_id,
    c.name AS course_name,
    c.grade,
    c.term AS course_term,
    c.year AS course_year,
    JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].id'))) AS item_id,
    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].term'))), c.term) AS term,
    JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].title'))) AS title,
    JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].due_date'))) AS due_date,
    CAST(JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].score'))) AS DECIMAL(10,2)) AS score,
    JSON_UNQUOTE(JSON_EXTRACT(c.assignments, CONCAT('$[', seq.idx, '].notes'))) AS notes,
    c.created_at AS course_created_at,
    c.updated_at AS course_updated_at
FROM courses c
JOIN (
    SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS idx
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) AS seq ON seq.idx < JSON_LENGTH(c.assignments)
WHERE c.assignments IS NOT NULL
  AND JSON_TYPE(c.assignments) = 'ARRAY'
  AND JSON_LENGTH(c.assignments) > 0;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS course_teaching_hours_view');
        DB::statement('DROP VIEW IF EXISTS course_lessons_view');
        DB::statement('DROP VIEW IF EXISTS course_assignments_view');
    }
};
