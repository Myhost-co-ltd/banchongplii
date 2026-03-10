<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$html = view('auth.login')->render();
echo (str_contains($html, 'type="text" name="email"') ? 'ok' : 'missing') . PHP_EOL;
