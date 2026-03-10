<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Auth::loginUsingId(5);
$controller = $app->make(App\Http\Controllers\StudentController::class);
$view = $controller->index();
$data = $view->getData();
$students = collect($data['students'] ?? []);

echo 'studentCount=' . ($data['studentCount'] ?? -1) . PHP_EOL;
echo 'assignedRooms=' . collect($data['assignedRooms'] ?? [])->implode(',') . PHP_EOL;
echo 'roomGroups=' . $students->groupBy(fn($s) => $s->room_normalized ?? '-')->map->count()->toJson(JSON_UNESCAPED_UNICODE) . PHP_EOL;
