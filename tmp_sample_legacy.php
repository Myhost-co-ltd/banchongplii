<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo "tb_group_course sample" . PHP_EOL;
print_r(DB::table('tb_group_course')->limit(10)->get()->toArray());

echo "tb_course sample" . PHP_EOL;
print_r(DB::table('tb_course')->select('id_course','name_course','id_group')->limit(15)->get()->toArray());

echo "tb_ex_class count" . PHP_EOL;
echo DB::table('tb_ex_class')->count() . PHP_EOL;
