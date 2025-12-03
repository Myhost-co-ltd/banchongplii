<?php
require 'vendor/autoload.php';
require 'app/Http/Controllers/AdminStudentController.php';
use App\Http\Controllers\AdminStudentController;
$c = new AdminStudentController();
$ref = new ReflectionClass($c);
$read = $ref->getMethod('readXlsxRows');
$read->setAccessible(true);
$rows = $read->invoke($c, 'public/import_templates/students_sample.xlsx');
var_export($rows);
