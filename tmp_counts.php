<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

echo 'courses_count=' . DB::table('courses')->count() . PHP_EOL;
if (DB::table('courses')->count() > 0) {
    print_r(DB::table('courses')->select('id','name','grade','user_id')->limit(5)->get()->toArray());
}

echo 'tb_teacher_count=' . DB::table('tb_teacher')->count() . PHP_EOL;
echo 'tb_class_schedule_count=' . DB::table('tb_class_schedule')->count() . PHP_EOL;
echo 'tb_ex_class_count=' . DB::table('tb_ex_class')->count() . PHP_EOL;
