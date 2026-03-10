<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Auth::loginUsingId(5);
$controller = $app->make(App\Http\Controllers\StudentController::class);
$view = $controller->index();
$data = $view->getData();
$studentsByRoom = collect($data['students'] ?? [])->groupBy(function ($s) {
    return $s->room_normalized ?? $s->classroom ?? $s->room ?? '-';
});

echo 'assigned=' . collect($data['assignedRooms'] ?? [])->implode(',') . PHP_EOL;
foreach (collect($data['assignedRooms'] ?? []) as $room) {
    echo $room . '=' . collect($studentsByRoom->get($room, collect()))->count() . PHP_EOL;
}
