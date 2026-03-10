<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$total = Illuminate\Support\Facades\DB::table('tb_teacher')->count();
$withUsername = Illuminate\Support\Facades\DB::table('tb_teacher')->whereNotNull('username')->whereRaw("TRIM(username) <> ''")->count();
$withCard = Illuminate\Support\Facades\DB::table('tb_teacher')->whereNotNull('id_card_number')->whereRaw("TRIM(id_card_number) <> ''")->count();
$withPhone = Illuminate\Support\Facades\DB::table('tb_teacher')->whereNotNull('phone_number')->whereRaw("TRIM(phone_number) <> ''")->count();
echo "total=$total\n";
echo "with_username=$withUsername\n";
echo "with_id_card_number=$withCard\n";
echo "with_phone=$withPhone\n";
$examples = Illuminate\Support\Facades\DB::table('tb_teacher')->select('id_teacher','username','id_card_number','phone_number','password')->whereNotNull('username')->whereRaw("TRIM(username) <> ''")->limit(10)->get();
foreach ($examples as $r) {
  echo ($r->id_teacher ?? '') . '|u=' . ($r->username ?? '') . '|card=' . ($r->id_card_number ?? '') . '|phone=' . ($r->phone_number ?? '') . '|pw=' . ($r->password ?? '') . PHP_EOL;
}
