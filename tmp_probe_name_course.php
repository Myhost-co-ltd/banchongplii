<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('tb_course')->select('id_course','name_course')->orderBy('name_course')->limit(10)->get();
foreach ($rows as $r) {
    echo $r->id_course . ' | ' . $r->name_course . PHP_EOL;
}
