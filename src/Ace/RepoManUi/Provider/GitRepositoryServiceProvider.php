<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Remote\GitRepositoryService;
use Silex\ServiceProviderInterface;
use Silex\Application;
use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 30/12/15
 */
class GitRepositoryServiceProvider implements ServiceProviderInterface
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

        $app['git-repository-service'] = new GitRepositoryService(
            $app['config']->getRemoteApiHost(),
            $app['token-service'],
            $client
        );
    }
}