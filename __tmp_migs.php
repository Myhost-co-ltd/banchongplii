<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('migrations')->orderBy('id')->get();
foreach ($rows as $row) {
    echo $row->migration."\n";
}
