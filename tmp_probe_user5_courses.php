<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$courses = App\Models\Course::where('user_id', 5)->select('id','name','rooms')->get();
foreach ($courses as $c) {
    $rooms = json_encode($c->rooms, JSON_UNESCAPED_UNICODE);
    echo $c->id . '|' . $c->name . '|rooms=' . $rooms . PHP_EOL;
}
