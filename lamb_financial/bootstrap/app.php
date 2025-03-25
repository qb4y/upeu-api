<?php
/*use Illuminate\Contracts\Debug\ExceptionHandler;
use Anik\ElasticApm\Exceptions\Handler;
use App\Exceptions\Handler as AppExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GuzzleHttp\Exception\ConnectException;*/
/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__ . '/../')
);

/*if (!in_array($_SERVER['SERVER_NAME'], ['0.0.0.0', 'localhost', '127.0.0.1', 'upeu.dev'])) {
    $app->loadEnvironmentFrom('.env.prod');
}*/

/*$app->detectEnvironment(function () use ($app) {
    $env = env('APP_ENV', 'local');
    $file = ".env.{$env}";
    $app->loadEnvironmentFrom($file);
}); */

$host = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
}
// $host = $_SERVER['HTTP_HOST'];
$char = ".";
$ex = explode($char, $host);
$root = array_shift($ex);
switch ($root) {

    case 'upeu.dev':
        $app->loadEnvironmentFrom('.env');
        break;
    case 'api-lamb-financial':
        $app->loadEnvironmentFrom('.env.prod');
        break;
    case 'api-lamb':
        $app->loadEnvironmentFrom('.env.prod');
        break;
    default:
        $app->loadEnvironmentFrom('.env');
        break;
}

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);
/*
$app->singleton(ExceptionHandler::class,function($app) {
	return new Handler(new AppExceptionHandler($app), [
    	// NotFoundHttpException::class, //(1)
    	// ConnectException::class, //(2)
	]);
});
*/

//$app->register(Yajra\Oci8\Oci8ServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
