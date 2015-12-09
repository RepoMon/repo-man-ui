<?php namespace Ace\RepoManUi;

/*
 * @author tim rodger
 * Date: 29/11/15
 *
 * @todo use env vars for the remote git host variables
 */
class Configuration
{
    /**
     * @return string
     */
    public function getRepoManHost()
    {
        return 'http://repoman';
    }

    /**
     * @return string
     */
    public function getTokenHost()
    {
        return 'http://token';
    }


    /**
     * @return string
     */
    public function getRemoteHost()
    {
        return 'https://github.com';
    }

    /**
     * @return string
     */
    public function getAuthnServiceName()
    {
        return 'GitHub';
    }

    /**
     * @return string
     */
    public function getRemoteApiHost()
    {
        return 'https://api.github.com';
    }

    /**
     * @return string
     */
    public function getApiClientId()
    {
        return getenv('GH_BASIC_CLIENT_ID');
    }

    /**
     * @return string
     */
    public function getApiClientSecret()
    {
        return getenv('GH_BASIC_CLIENT_SECRET');
    }

    /**
     * @return string
     */
    public function getRabbitHost()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_ADDR');
    }

    /**
     * @return string
     */
    public function getRabbitPort()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_PORT');
    }

    /**
     * @return string
     */
    public function getRabbitChannelName()
    {
        // use an env var for the channel name too
        return 'repo-mon.main';
    }
}