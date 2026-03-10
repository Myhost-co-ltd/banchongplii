<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/director/teacher-plans', 'GET');
$controller = app(App\Http\Controllers\DirectorController::class);
$response = $controller->teacherPlans($request);
$data = method_exists($response, 'getData') ? $response->getData() : [];

$courses = $data['courses'] ?? collect();
$courseOptions = $data['courseOptions'] ?? [];

echo 'courses_count=' . (is_countable($courses) ? count($courses) : 0) . PHP_EOL;
echo 'course_options_count=' . (is_countable($courseOptions) ? count($courseOptions) : 0) . PHP_EOL;
if (is_countable($courseOptions) && count($courseOptions) > 0) {
    echo 'first_course_option=' . (string) $courseOptions[0] . PHP_EOL;
}
