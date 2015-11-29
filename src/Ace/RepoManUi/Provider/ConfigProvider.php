<?php namespace Ace\RepoManUi\Provider;

use Ace\RepoManUi\Configuration;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @author timrodger
 * Date: 23/06/15
 */
class ConfigProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['config'] = new Configuration();
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
