<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\TokenService;
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
        $app['token-service'] = new TokenService(
            $app['config']->getTokenService(),
            $app['config']->getServiceName()
        );
    }
}
