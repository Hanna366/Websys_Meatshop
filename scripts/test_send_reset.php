<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// Bootstrap the application (register facades, providers, etc.)
$kernel->bootstrap();

// Create a request that mimics the forgot-password POST (bypasses HTTP CSRF middleware)
$request = Illuminate\Http\Request::create(
    '/forgot-password',
    'POST',
    ['email' => 'areshanna088@gmail.com'],
    [],
    [],
    ['HTTP_HOST' => 'localhost']
);

// Bind the request into the container so URL generator and other services work
$app->instance('request', $request);

// Provide a lightweight validate() helper on Request when running from CLI
if (! method_exists(Illuminate\Http\Request::class, 'validate')) {
    Illuminate\Http\Request::macro('validate', function ($rules) {
        $validator = Illuminate\Support\Facades\Validator::make($this->all(), $rules);
        if ($validator->fails()) {
            throw new Illuminate\Validation\ValidationException($validator);
        }
        return $validator->validated();
    });
}

try {
    $controller = new App\Http\Controllers\PasswordResetController();
    $response = $controller->sendResetLink($request);

    echo "Response class: " . get_class($response) . PHP_EOL;
    if (method_exists($response, 'getStatusCode')) {
        echo "Status: " . $response->getStatusCode() . PHP_EOL;
    }
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        echo "Content (head):\n" . substr((string) $content, 0, 1000) . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
