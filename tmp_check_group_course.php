<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$has = Illuminate\Support\Facades\Schema::hasTable('tb_group_course');
echo ($has ? '1' : '0') . PHP_EOL;
if ($has) {
    print_r(Illuminate\Support\Facades\Schema::getColumnListing('tb_group_course'));
}
