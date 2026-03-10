<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $kernel->handle($request);
$content = $response->getContent();
echo (str_contains($content, 'type="text" name="email"') ? 'ok' : 'missing') . PHP_EOL;
$kernel->terminate($request, $response);
