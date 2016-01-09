<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\TokenService;
use GuzzleHttp\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;


/**
 * @author timrodger
 * Date: 13/12/15
 */
class TokenProvider implements ServiceProviderInterface
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


        $app['token-service'] = new TokenService(
            $app['config']->getTokenService(),
            $client
        );
    }
}
