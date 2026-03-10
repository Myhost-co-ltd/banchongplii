<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/director/teacher-plans', 'GET');
$controller = app(App\Http\Controllers\DirectorController::class);
$response = $controller->teacherPlans($request);
$data = method_exists($response, 'getData') ? $response->getData() : [];

echo 'completeTeacherCount=' . (int) ($data['completeTeacherCount'] ?? -1) . PHP_EOL;
echo 'incompleteTeacherCount=' . (int) ($data['incompleteTeacherCount'] ?? -1) . PHP_EOL;
$courses = $data['courses'] ?? collect();
$first = is_countable($courses) && count($courses) > 0 ? $courses[0] : null;
if ($first) {
    echo 'firstCourse=' . (string) ($first->name ?? '-') . PHP_EOL;
    echo 'firstHasHours=' . (!empty($first->teaching_hours) ? '1' : '0') . PHP_EOL;
    echo 'firstHasAssignments=' . (!empty($first->assignments) ? '1' : '0') . PHP_EOL;
    echo 'firstIsComplete=' . ((isset($first->is_complete) && $first->is_complete) ? '1' : '0') . PHP_EOL;
}
