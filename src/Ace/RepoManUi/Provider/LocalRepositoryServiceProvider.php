<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\LocalRepositoryService;
use Silex\ServiceProviderInterface;
use Silex\Application;
use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 30/12/15
 */
class LocalRepositoryServiceProvider implements ServiceProviderInterface
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

        $app['local-repository-service'] = new LocalRepositoryService(
            $app['config']->getRepositoryHost(),
            $client
        );
    }
}