<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\AdminStudentController;

$ctrl = new AdminStudentController();
$ref = new ReflectionClass($ctrl);
$readXlsx = $ref->getMethod('readXlsxRows');
$readXlsx->setAccessible(true);
$mapRow = $ref->getMethod('mapRow');
$mapRow->setAccessible(true);

$rows = $readXlsx->invoke($ctrl, 'testimport.xlsx');
var_dump($rows);
$header = $rows[0];
$data = $rows[1] ?? [];
$result = $mapRow->invoke($ctrl, $header, $data);
var_dump($header, $data, $result);
