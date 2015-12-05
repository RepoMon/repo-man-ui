<?php

require_once __DIR__ . '/vendor/autoload.php';

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;

use Ace\RepoManUi\Provider\ConfigProvider;
use Ace\RepoManUi\Provider\RabbitClientProvider;

use GuzzleHttp\Client;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__.'/views',
]);

$app->register(new SessionServiceProvider(), [
    'cookie_lifetime' => 60 * 60 * 24
]);

$app->register(new ConfigProvider());
$app->register(new RabbitClientProvider());

$client = new Client([
    'headers' => [
        'User-Agent' => 'RepoMon'
    ]
]);

$require_authn = function(Request $request) use ($app) {
    if (null === $app['session']->get('user')) {
        return $app->redirect('/login');
    }
};

/**
 * show a list of repositories the user has access to and which ones are configured for updates
 */
$app->get('/', function(Request $request) use ($app, $client){

    // get session token from the tokens service not from the session
    // get a list of repos for the user (in the session) and store them in the session

    $api_host = $app['config']->getRemoteApiHost();

    // get available repositories for the user from remote api
    $available_response = $client->request('GET', $api_host . '/user/repos', [
        'query' => [
            'access_token' => $app['session']->get('access_token')
        ],
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $available_data = json_decode($available_response->getBody(), true);

    $configured_data = [];

    if (!getenv('HIDE_REPOMAN_DATA')) {
        $repo_man_host = $app['config']->getRepoManHost();
        $configured_response = $client->request('GET', $repo_man_host . '/repositories', [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $configured_data = json_decode($configured_response->getBody(), true);
    }


    return $app['twig']->render('index.html', [
        'configured' => $configured_data,
        'available' => $available_data,
        'user' => $app['session']->get('user')
    ]);

})->before($require_authn);

/**
 * add a repository
 */
$app->post('/', function(Request $request) use ($app,  $client){

    $user = $app['session']->get('user');

    $event = [
        'name' => 'repo-mon.repo.configured',
        'data' => [
            'owner' => $user['name'],
            'url' => $request->get('repository'),
            'language' => $request->get('language'),
            'dependency_manager' => $request->get('dependency_manager'),
            'frequency' => $request->get('frequency'),
            'hour' => $request->get('hour'),
            'timezone' => $request->get('timezone'),
        ]
    ];

    $app['rabbit-client']->publish($event);

    return $app->redirect('/');

})->before($require_authn);

/**
 * show user link to authenticate
 */
$app->get('/login', function(Request $request) use ($app){

    $authn_host = $app['config']->getRemoteHost();
    $client_id = $app['config']->getApiClientId();

    $endpoint = sprintf("%s/login/oauth/authorize?scope=user,repo,public_repo&client_id=%s", $authn_host, $client_id);

    return $app['twig']->render('login.html', [
        'authentication_service' => $app['config']->getAuthnServiceName(),
        'endpoint' => $endpoint
    ]);
});

/**
 * authentication callback
 * get an access token and set up a cookie session
 */
$app->get('/authn-callback', function(Request $request) use ($app) {

    $session_code = $request->get('code');
    $client = new Client();

    $authn_host = $app['config']->getRemoteHost();

    // get the access token
    $authn_result = $client->request('POST', $authn_host . '/login/oauth/access_token', [
        'form_params' => [
            'client_id' => $app['config']->getApiClientId(),
            'client_secret' => $app['config']->getApiClientSecret(),
            'code' => $session_code,
        ],
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $authn_data = json_decode($authn_result->getBody(), true);

    $api_host = $app['config']->getRemoteApiHost();

    // get the user details
    $user_result = $client->request('GET', $api_host . '/user', [
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

    $event = [
        'name' => 'repo-mon.token.added',
        'data' => [
            'user' => $user_data['name'],
            'token' => $authn_data['access_token']
        ]
    ];

    $app['rabbit-client']->publish($event);

    return $app->redirect('/');

});

/**
 * show dependency report
 */
$app->get('/report/dependency', function(Request $request) use ($app, $client){

    $repo_man_host = $app['config']->getRepoManHost();

    $response = $client->request('GET', $repo_man_host . '/dependencies/report', [
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