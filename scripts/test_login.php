<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\SimpleAuthController;

$email = $argv[1] ?? 'areshanna088@gmail.com';
$password = $argv[2] ?? 'TestPass123!';
$host = $argv[3] ?? 'localhost';

echo "Testing login for {$email} @ host={$host}\n";

$server = [
    'HTTP_HOST' => $host,
    'SERVER_NAME' => $host,
    'REQUEST_URI' => '/login',
    'REMOTE_ADDR' => '127.0.0.1',
];

$request = Request::create('/login', 'POST', ['email' => $email, 'password' => $password], [], [], $server);

// Attach session store so controller can use session()
try {
    $request->setLaravelSession(app('session.store'));
} catch (Throwable $e) {
    echo "Warning: failed to attach session store: " . $e->getMessage() . "\n";
}

$controller = new SimpleAuthController();
try {
    $response = $controller->login($request);
    if (is_string($response)) {
        echo "Response string:\n" . $response . "\n";
    } elseif (method_exists($response, 'getStatusCode')) {
        echo "Response status: " . $response->getStatusCode() . "\n";
        if (method_exists($response, 'getContent')) {
            echo "Content snippet: " . substr($response->getContent(), 0, 800) . "\n";
        }
    } else {
        echo "Response class: " . get_class($response) . "\n";
    }
} catch (Throwable $e) {
    echo "Login invocation error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
