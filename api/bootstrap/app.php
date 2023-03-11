<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(Laravel\Lumen\Providers\EventServiceProvider::class);
$app->register(Dusterio\PlainSqs\Integrations\LumenServiceProvider::class);
$app->register(Torann\GeoIP\GeoIPServiceProvider::class);

$app->middleware([
    \App\Http\Middleware\CorsMiddleware::class, //cross origin support
    Fruitcake\Cors\HandleCors::class //cross origin support
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'jwt.auth' => App\Http\Middleware\JwtMiddleware::class, //jwt auth
    'cors' => \App\Http\Middleware\CorsMiddleware::class,
    'tenant.connection' => App\Http\Middleware\TenantConnectionMiddleware::class, // Middle ware that connect tenant user with their tenant
    'auth.tenant.admin' => App\Http\Middleware\AuthTenantAdminMiddleware::class,
    'localization' => App\Http\Middleware\LocalizationMiddleware::class,
    'JsonApiMiddleware' => App\Http\Middleware\JsonApiMiddleware::class,
    'PaginationMiddleware' => App\Http\Middleware\PaginationMiddleware::class,
    'user.profile.complete' => App\Http\Middleware\UserProfileCompleteMiddleware::class,
    'TenantHasSettingsMiddleware' => App\Http\Middleware\TenantHasSettingsMiddleware::class,
    'throttle' => \App\Http\Middleware\ThrottleRequestsMiddleware::class,
    'DonationIpWhitelistMiddleware' => App\Http\Middleware\DonationIpWhitelistMiddleware::class,
]);

/**
 * cross origin api call support
 */
$app->register(Fruitcake\Cors\CorsServiceProvider::class);

$app->configure('app'); //default authentication
$app->configure('auth'); //default authentication
$app->configure('mail'); //SMTP and PHP mail
$app->configure('constants'); //constant file config
$app->configure('cors');  //cross origin support
$app->configure('messages');  //Message Constants config
$app->configure('mail');  //Mail Constants config
$app->configure('filesystems');
$app->configure('services');
$app->configure('queue');
$app->configure('sqs-plain');
$app->configure('geoip');

/**
 * mailer package registration
 */
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(\LaravelMandrill\MandrillServiceProvider::class);
$app->alias('mailer', \Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', \Illuminateminate\Mail\Mailer::class);
$app->alias('mailer', \Illuminate\Contracts\Mail\MailQueue::class);
$app->withFacades(true, ['Illuminate\Support\Facades\Notification' => 'Notification']);
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);
$app->withFacades();
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

//AMQP Service Provider :
$app->configure('amqp');
$app->register(Bschmitt\Amqp\LumenServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
