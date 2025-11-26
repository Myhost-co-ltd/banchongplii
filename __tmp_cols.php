<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select('SHOW COLUMNS FROM courses');
foreach ($cols as $col) {
    echo $col->Field . "\n";
}
