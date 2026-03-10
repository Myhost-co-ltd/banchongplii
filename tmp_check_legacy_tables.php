<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = ['tb_course','tb_group_course','tb_class','tb_class_schedule','tb_ex_class'];
foreach ($tables as $t) {
    echo "=== $t ===" . PHP_EOL;
    $has = Illuminate\Support\Facades\Schema::hasTable($t);
    echo ($has ? '1' : '0') . PHP_EOL;
    if ($has) {
        print_r(Illuminate\Support\Facades\Schema::getColumnListing($t));
    }
}
