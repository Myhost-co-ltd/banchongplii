<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE ALGORITHM=MERGE SQL SECURITY INVOKER VIEW course_rooms_view AS
SELECT
    c.id AS course_id,
    c.name AS course_name,
    c.grade,
    c.term AS course_term,
    c.year AS course_year,
    JSON_UNQUOTE(JSON_EXTRACT(c.rooms, CONCAT('$[', seq.idx, ']'))) AS room
FROM courses c
JOIN (
    SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS idx
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) AS seq ON seq.idx < JSON_LENGTH(c.rooms)
WHERE c.rooms IS NOT NULL
  AND JSON_TYPE(c.rooms) = 'ARRAY'
  AND JSON_LENGTH(c.rooms) > 0;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS course_rooms_view');
    }
};
