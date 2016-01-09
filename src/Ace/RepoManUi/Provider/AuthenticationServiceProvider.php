<?php
declare(strict_types=1);
namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\AuthenticationService;
use Silex\Application;
use Silex\ServiceProviderInterface;
use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 13/12/15
 */
class AuthenticationServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => $app['config']->getServiceName()
            ]
        ]);

        $app['authentication-service'] = new AuthenticationService(
            $app['config']->getRemoteHost(),
            $app['config']->getRemoteApiHost(),
            $app['config']->getApiClientId(),
            $app['config']->getApiClientSecret(),
            $client
        );
    }
}
