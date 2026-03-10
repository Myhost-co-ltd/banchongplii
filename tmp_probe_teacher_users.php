<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacherRoleId = App\Models\Role::where('name', 'teacher')->value('id');
$users = App\Models\User::where('role_id', $teacherRoleId)->select('id','name','email','homeroom')->orderBy('id')->limit(20)->get();
foreach ($users as $u) {
    echo $u->id . '|' . $u->name . '|' . $u->email . '|homeroom=' . ($u->homeroom ?? '') . PHP_EOL;
}
