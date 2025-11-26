<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sample = DB::table('courses')->select('id','rooms')->orderBy('id')->limit(5)->get();
foreach ($sample as $row) {
    $rooms = json_decode($row->rooms, true);
    echo "Course {$row->id}: ".json_encode($rooms, JSON_UNESCAPED_UNICODE)."\n";
}
