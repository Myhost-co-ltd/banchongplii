<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cols = Illuminate\Support\Facades\Schema::getColumnListing('tb_work');
echo implode(',', $cols) . PHP_EOL;
