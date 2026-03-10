<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('tb_teacher')->select('id_teacher','username','password','name','surname')->limit(10)->get();
foreach ($rows as $r) {
    $pw = (string)($r->password ?? '');
    echo ($r->id_teacher ?? '') . '|u=' . ($r->username ?? '') . '|pw=' . $pw . '|len=' . strlen($pw) . '|n=' . ($r->name ?? '') . ' ' . ($r->surname ?? '') . PHP_EOL;
}
