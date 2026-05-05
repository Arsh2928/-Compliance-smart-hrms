<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/register', 'POST', [
    'name' => 'Debug Request',
    'email' => 'debug-request@example.com',
    'password' => 'password',
    'password_confirmation' => 'password',
]);

$session = $app['session']->driver();
$session->start();
$request->setLaravelSession($session);

try {
    $response = $app['router']->dispatch($request);
    echo 'status=' . $response->getStatusCode() . "\n";
    echo 'auth=' . ($app['auth']->check() ? 'true' : 'false') . "\n";
    $content = $response->getContent();
    $search = ['Exception', 'Error', 'Message', 'Trace', 'Stack trace', 'BadMethodCallException', 'InvalidArgumentException'];
    foreach ($search as $term) {
        if (stripos($content, $term) !== false) {
            echo $term . ': found\n';
        }
    }
    $snippet = substr($content, 0, 4000);
    echo 'snippet=' . preg_replace('/\s+/', ' ', $snippet) . "\n";
    echo 'content_length=' . strlen($content) . "\n";
    $cookieNames = array_map(fn($cookie)=>$cookie->getName(), $response->headers->getCookies());
    echo 'cookies=' . implode(',', $cookieNames) . "\n";
} catch (Throwable $e) {
    echo 'exception=' . get_class($e) . "\n";
    echo 'message=' . $e->getMessage() . "\n";
    echo 'file=' . $e->getFile() . ':' . $e->getLine() . "\n";
    echo 'trace=' . $e->getTraceAsString() . "\n";
}
