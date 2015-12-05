<?php namespace Ace\RepoManUi\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Ace\RepoManUi\RabbitClient;

/**
 * @author timrodger
 * Date: 05/12/15
 */
class RabbitClientProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {

    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $config = $app['config'];

        $app['rabbit-client'] = new RabbitClient(
            $config->getRabbitHost(),
            $config->getRabbitPort(),
            $config->getRabbitChannelName()
        );
    }
}
