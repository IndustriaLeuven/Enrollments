<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$loader = require_once __DIR__.'/../app/autoload.php';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

$request = Request::createFromGlobals();
if (file_exists(__DIR__ . '/../maintenance')) {
    Response::create()
        ->setContent(file_get_contents(__DIR__ . '/../maintenance'))
        ->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE)
        ->prepare($request)
        ->send();
} else {

    require_once __DIR__ . '/../app/AppKernel.php';
    //require_once __DIR__.'/../app/AppCache.php';

    $kernel = new AppKernel(file_exists(__DIR__ . '/../.staging') ? 'staging' : 'prod', false);
    $kernel->loadClassCache();
    //$kernel = new AppCache($kernel);

    // When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
    //Request::enableHttpMethodParameterOverride();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}

