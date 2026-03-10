<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = env('DB_DATABASE');
$rows = Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND COLUMN_NAME IN ('id_course','id_class','id_teacher','id_work','work_name','title_work','name_work') ORDER BY TABLE_NAME, COLUMN_NAME", [$db]);
$group = [];
foreach ($rows as $r) {
  $group[$r->TABLE_NAME][] = $r->COLUMN_NAME;
}
foreach ($group as $table => $cols) {
  $count = Illuminate\Support\Facades\DB::table($table)->count();
  echo $table . ' | count=' . $count . ' | cols=' . implode(',', $cols) . PHP_EOL;
}
