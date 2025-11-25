<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$lessons = DB::table('course_lessons')->count();
$assignments = DB::table('course_assignments')->count();
$hours = DB::table('course_teaching_hours')->count();

echo "course_lessons rows: {$lessons}\n";
echo "course_assignments rows: {$assignments}\n";
echo "course_teaching_hours rows: {$hours}\n";
