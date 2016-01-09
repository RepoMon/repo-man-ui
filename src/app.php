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
use Ace\RepoManUi\Provider\AuthenticationServiceProvider;

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
$app->register(new AuthenticationServiceProvider());

/**
 * @param Request $request
 * @return \Symfony\Component\HttpFoundation\RedirectResponse
 */
$require_authn = function(Request $request) use ($app) {
    if (null === $app['session']->get('user')) {
        return $app->redirect('/login');
    }
};

/**
 * show a list of repositories for the user
 */
$app->get('/', function(Request $request) use ($app){

    $user = $app['session']->get('user');

    $repositories = $app['local-repository-service']->getRepositories($user['login']);

    return $app['twig']->render('index.html', [
        'repositories' => $repositories,
        'user' => $user
    ]);

})->before($require_authn);


/**
 * Respond with a list of repositories in json
 */
$app->get('/repositories', function(Request $request) use ($app) {

    $user = $app['session']->get('user');

    $repositories = $app['local-repository-service']->getRepositories($user['login']);

    return $app->json(
        $repositories
    );

})->before($require_authn);

/**
 * update local repositories with the ones the user has access to on remote git host
 */
$app->post('/refresh', function(Request $request) use ($app) {

    $user = $app['session']->get('user');
    $timezone = $request->get('timezone', 'Europe/London');

    $repositories = $app['git-repository-service']->getRepositories($user['login'], $timezone);

    $local_repositories = $app['local-repository-service']->getRepositories($user['login']);

    foreach($repositories as $full_name => $repository) {

        // post an added event, if not already locally available
        if (!isset($local_repositories[$full_name])) {
            $event = [
                'name' => 'repo-mon.repository.added',
                'data' => [
                    'url' => $repository->getUrl(),
                    'full_name' => $repository->getFullName(),
                    'description' => $repository->getDescription(),
                    'language' => $repository->getLanguage(),
                    'owner' => $user['login'],
                    'dependency_manager' => $repository->getDependencyManager(),
                    'timezone' => $timezone,
                ]
            ];

            $app['rabbit-client']->publish($event);
        }
    }

    return $app->redirect('/');

})->before($require_authn);


/**
 * Activate / deactivate a repository
 */
$app->post('/repositories/{name}', function(Request $request, $name) use ($app){

    if ($request->get('active')) {
        $event = 'repo-mon.repository.activated';
    } else {
        $event = 'repo-mon.repository.deactivated';
    }

    // calculate hour & minute to schedule task here?
    $event = [
        'name' => $event,
        'data' => [
            'full_name' => $name,
            'timezone' => $request->get('timezone'),
        ]
    ];

    $app['rabbit-client']->publish($event);

    return $app->json($event);

})->assert('name', '.+')->before($require_authn);

/**
 * show user link to authenticate
 */
$app->get('/login', function(Request $request) use ($app){

    return $app['twig']->render('login.html', [
        'authentication_service' => $app['config']->getAuthnServiceName(),
        'endpoint' => $app['authentication-service']->getAuthenticationEndPoint()
    ]);
});

/**
 * authentication callback - client is redirected here, authentication is validated
 * get an access token and set up a cookie session
 */
$app->get('/authn-callback', function(Request $request) use ($app) {

    $token = $app['authentication-service']->getAccessTokenFromCode($request->get('code'));
    $user = $app['authentication-service']->getUserDataFromAccessToken($token);

    $app['session']->set('user', $user);

    $event = [
        'name' => 'repo-mon.token.added',
        'data' => [
            'user' => $user['login'],
            'token' => $token
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