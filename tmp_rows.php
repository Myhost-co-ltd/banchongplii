<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

function dumpRows($table, $limit = 10) {
    echo "=== $table ===" . PHP_EOL;
    print_r(DB::table($table)->limit($limit)->get()->toArray());
}

dumpRows('tb_class_schedule');
dumpRows('tb_ex_class');
dumpRows('tb_class');
dumpRows('tb_course');
dumpRows('tb_teacher');
