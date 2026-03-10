<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
$key = 'Tables_in_' . env('DB_DATABASE');
$names = collect($tables)->map(fn($r) => $r->{$key} ?? null)->filter()->values();

$targets = $names->filter(function ($name) {
    $name = strtolower((string)$name);
    return str_contains($name, 'student') || str_contains($name, 'tb_std') || str_contains($name, 'tb_student');
})->values();

echo "target_tables=" . $targets->implode(',') . PHP_EOL;
foreach ($targets as $table) {
    $count = Illuminate\Support\Facades\DB::table($table)->count();
    echo "=== {$table} (count={$count}) ===" . PHP_EOL;
    $cols = Illuminate\Support\Facades\Schema::getColumnListing($table);
    echo implode(',', $cols) . PHP_EOL;
    $sample = Illuminate\Support\Facades\DB::table($table)->limit(2)->get();
    foreach ($sample as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
