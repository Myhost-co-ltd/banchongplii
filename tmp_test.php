<?php
require 'vendor/autoload.php';
use App\Http\Controllers\AdminStudentController;

$ctrl = new AdminStudentController();
$ref = new ReflectionClass($ctrl);
$readXlsx = $ref->getMethod('readXlsxRows');
$readXlsx->setAccessible(true);
$mapRow = $ref->getMethod('mapRow');
$mapRow->setAccessible(true);

$rows = $readXlsx->invoke($ctrl, 'tmp_import.xlsx');
print_r($rows);

$header = $rows[0];
$row2 = $rows[1] ?? [];
$result = $mapRow->invoke($ctrl, $header, $row2);
print_r($result);
