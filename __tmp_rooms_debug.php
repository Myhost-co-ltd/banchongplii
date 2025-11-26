<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select('select JSON_UNQUOTE(JSON_EXTRACT(rooms, "$[0]")) as r from courses where rooms is not null limit 1');
var_dump($res[0]->r);
