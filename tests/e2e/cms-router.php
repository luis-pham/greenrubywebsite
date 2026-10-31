<?php
// Browser E2E only: `php -S … tests/e2e/cms-router.php`. Same as server.php, but every payment method uses the
// fake gateway (tests/Support/FakePaymentGateway.php) and the app refuses to start outside a local test database.
$db = getenv('DB_DATABASE') ?: '';
if (!str_starts_with($db, 'green_ruby_i18n_')) {
    http_response_code(500);
    exit('E2E router: local green_ruby_i18n_* database only');
}
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$public = dirname(__DIR__, 2) . '/public';
if ($uri !== '/' && file_exists($public . $uri)) {
    return false;
}
define('LARAVEL_START', microtime(true));
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
    $fake = \Tests\Support\FakePaymentGateway::class;
    $app['config']->set('payment.gateways', ['stripe' => $fake, 'paypal' => $fake, 'sepay' => $fake]);
});
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture())->send();
$kernel->terminate($request, $response);
