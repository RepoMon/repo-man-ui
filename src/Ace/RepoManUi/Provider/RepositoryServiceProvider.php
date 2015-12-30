<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\RepositoryService;
use Silex\ServiceProviderInterface;
use Silex\Application;
use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 30/12/15
 */
class RepositoryServiceProvider implements ServiceProviderInterface
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

        $app['repository-service'] = new RepositoryService(
            $app['config']->getRemoteApiHost(),
            $app['config']->getRepoManHost(),
            $app['token-service'],
            $client
        );
    }
}