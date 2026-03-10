<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$targets = ['tb_class','tb_course','tb_class_schedule','students'];
foreach ($targets as $table) {
    if (! Illuminate\Support\Facades\Schema::hasTable($table)) {
        echo "no {$table}\n";
        continue;
    }
    echo "=== {$table} ===\n";
    $cols = Illuminate\Support\Facades\Schema::getColumnListing($table);
    echo implode(',', $cols) . "\n";
    $rows = Illuminate\Support\Facades\DB::table($table)->limit(10)->get();
    foreach ($rows as $r) {
        echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
