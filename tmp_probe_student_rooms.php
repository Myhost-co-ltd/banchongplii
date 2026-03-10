<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('students')
    ->select('room', Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))
    ->groupBy('room')
    ->orderBy('room')
    ->get();
foreach ($rows as $r) {
    echo ($r->room ?? 'NULL') . '|' . $r->c . PHP_EOL;
}
