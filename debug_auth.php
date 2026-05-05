<?php

use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::create([
    'name' => 'Debug User',
    'email' => 'debug-user@example.com',
    'password' => 'password',
    'role' => 'employee',
]);

echo 'created id=' . json_encode($user->getAuthIdentifier()) . "\n";
Auth::login($user);
echo 'auth check=' . (Auth::check() ? 'true' : 'false') . "\n";
