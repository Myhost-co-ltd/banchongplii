<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('tb_work')->orderBy('id_work')->get();
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo "--- join ---\n";
$join = Illuminate\Support\Facades\DB::table('tb_work as w')
    ->leftJoin('tb_course as c', 'c.id_course', '=', 'w.id_course')
    ->leftJoin('tb_class as cl', 'cl.id_class', '=', 'w.id_class')
    ->select('w.id_work','w.name_work','w.id_course','w.id_class','c.name_course','cl.class')
    ->orderBy('w.id_work')
    ->get();
foreach ($join as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

$filtered = Illuminate\Support\Facades\DB::table('tb_work as w')
    ->leftJoin('tb_course as c', 'c.id_course', '=', 'w.id_course')
    ->leftJoin('tb_class as cl', 'cl.id_class', '=', 'w.id_class')
    ->select('w.id_work','w.name_work','w.score_work','w.work_timeup','w.work_dateup','c.name_course','cl.class')
    ->where('w.id_course', 17)
    ->where('w.id_class', 8)
    ->orderByDesc('w.id_work')
    ->get();

echo "--- filtered id_course=17 id_class=8 ---\n";
foreach ($filtered as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
