<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$session = $app['session']->driver();
$session->start();

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => '8',
    'password' => '123',
]);
$request->setLaravelSession($session);

$controller = $app->make(App\Http\Controllers\AuthController::class);
$response = $controller->login($request);

echo 'response_class=' . get_class($response) . PHP_EOL;
if (method_exists($response, 'getTargetUrl')) {
    echo 'target=' . $response->getTargetUrl() . PHP_EOL;
}

echo 'auth_check=' . (Illuminate\Support\Facades\Auth::check() ? '1' : '0') . PHP_EOL;
if (Illuminate\Support\Facades\Auth::check()) {
    $u = Illuminate\Support\Facades\Auth::user();
    echo 'user_id=' . $u->id . PHP_EOL;
    echo 'user_email=' . $u->email . PHP_EOL;
    echo 'role=' . ($u->role_name ?? '') . PHP_EOL;
}
