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
use Ace\RepoManUi\Provider\TokenProvider;
use Ace\RepoManUi\Provider\LocalRepositoryServiceProvider;
use Ace\RepoManUi\Provider\GitRepositoryServiceProvider;

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
$app->register(new TokenProvider());
$app->register(new LocalRepositoryServiceProvider());
$app->register(new GitRepositoryServiceProvider());

$client = new Client([
    'headers' => [
        'User-Agent' => 'Repository Monitor v4.0.0'
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
$app->get('/', function(Request $request) use ($app){

    $available_repositories = $app['session']->get('available_repositories');

    $user = $app['session']->get('user');

    // don't just store the repositories in the session, store them all in the "local repository service"
    if (!$available_repositories){
        $available_repositories = $app['git-repository-service']->getRepositories($user['login'], 'Europe/London');
        $app['session']->set('available_repositories', $available_repositories);
    }

    $configured_repositories = $app['local-repository-service']->getRepositories($user['login']);

    return $app['twig']->render('index.html', [
        'configured' => $configured_repositories,
        'available' => $available_repositories,
        'user' => $user
    ]);

})->before($require_authn);

/**
 * add a repository
 */
$app->post('/', function(Request $request) use ($app){

    $user = $app['session']->get('user');

    // calculate hour & minute to schedule task here?
    $event = [
        'name' => 'repo-mon.repo.configured',
        'data' => [
            'owner' => $user['login'],
            'url' => $request->get('repository'),
            'description' => $request->get('description'),
            'language' => $request->get('language'),
            'dependency_manager' => $request->get('dependency_manager'),
            'frequency' => '1',
            //'hour' => $request->get('hour'),
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

    $endpoint = sprintf("%s/login/oauth/authorize?scope=user,public_repo&client_id=%s", $authn_host, $client_id);

    return $app['twig']->render('login.html', [
        'authentication_service' => $app['config']->getAuthnServiceName(),
        'endpoint' => $endpoint
    ]);
});

/**
 * authentication callback - client is redirected here, authentication is validated
 * get an access token and set up a cookie session
 */
$app->get('/authn-callback', function(Request $request) use ($app, $client) {

    $session_code = $request->get('code');

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

    $app['session']->set('user', $user_data);

    $event = [
        'name' => 'repo-mon.token.added',
        'data' => [
            'user' => $user_data['login'],
            'token' => $authn_data['access_token']
        ]
    ];

    $app['rabbit-client']->publish($event);

    return $app->redirect('/');

});

/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage());
});

return $app;