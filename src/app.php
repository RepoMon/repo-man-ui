<?php

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Silex\Provider\TwigServiceProvider;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__.'/views',
]);

/**
 * show a list of repositories
 */
$app->get('/', function(Request $request) use ($app){

    // make a request to the repo-man service /repositories endpoint

    $data = ['one' => 'https://github.com/timothy-r/rndr-twig'];

    return $app['twig']->render('index.html', [
        'repositories' => $data,
    ]);
});


/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage());
});

return $app;