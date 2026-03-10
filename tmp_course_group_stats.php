<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$distinct = DB::table('tb_course')->select('id_group')->distinct()->orderBy('id_group')->pluck('id_group')->all();
print_r($distinct);
$withGroup = DB::table('tb_course')->where('id_group','>',0)->count();
echo 'with_group=' . $withGroup . PHP_EOL;
if ($withGroup > 0) {
    print_r(DB::table('tb_course')->where('id_group','>',0)->select('id_course','name_course','id_group')->limit(20)->get()->toArray());
}
