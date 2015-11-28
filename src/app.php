<?php

require_once __DIR__ . '/vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;

use GuzzleHttp\Client;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__.'/views',
]);

$app->register(new SessionServiceProvider() , [
    'cookie_lifetime' => 60 * 60 * 24
]);

// get the host from config
$client = new Client([
    'base_uri' => 'http://repoman'
]);

$require_authn = function(Request $request) use ($app) {
    if (null === $app['session']->get('user')) {
        return $app->redirect('/login');
    }
};

/**
 * show a list of repositories
 */
$app->get('/', function(Request $request) use ($app, $client){

    $response = $client->request('GET', '/repositories', [
       'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $data = json_decode($response->getBody(), true);

    return $app['twig']->render('index.html', [
        'repositories' => $data,
        'user' => $app['session']->get('user')
    ]);

})->before($require_authn);

/**
 *
 */
$app->get('/login', function(Request $request) use ($app){

    return $app['twig']->render('login.html', [
        'client_id' => getenv('GH_BASIC_CLIENT_ID')
    ]);
});

/**
 *
 */
$app->get('/authn-callback', function(Request $request) use ($app) {

    $session_code = $request->get('code');
    $client = new Client();

    // get the access token
    $authn_result = $client->request('POST', 'https://github.com/login/oauth/access_token', [
        'form_params' => [
            'client_id' => getenv('GH_BASIC_CLIENT_ID'),
            'client_secret' => getenv('GH_BASIC_CLIENT_SECRET'),
            'code' => $session_code,
        ],
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $authn_data = json_decode($authn_result->getBody(), true);

    // get the user details
    $user_result = $client->request('GET', 'https://api.github.com/user', [
        'query' => [
            'access_token' => $authn_data['access_token']
        ],
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $user_data = json_decode($user_result->getBody(), true);
    $app['session']->set('access_token', $authn_data['access_token']);
    $scopes = explode(',', $authn_data['scope']);
    $app['session']->set('scopes', $scopes);
    $app['session']->set('user', $user_data);

    return $app->redirect('/');

});

/**
 * add a repository
 */
$app->post('/', function(Request $request) use ($app,  $client){

    $client->request('POST', '/repositories', [
        'form_params' => [
            'url' => $request->get('repository')
        ]
    ]);

    // redirect to home page
    return $app->redirect('/');

})->before($require_authn);

/**
 * show dependency report
 */
$app->get('/report/dependency', function(Request $request) use ($app, $client){

    $response = $client->request('GET', '/dependencies/report', [
        'headers' => [
            'Accept' => 'text/html'
        ]
    ]);

    return $app['twig']->render('dependency-report.html', [
        'report' => $response->getBody(),
    ]);

})->before($require_authn);

/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage());
});

return $app;