<?php namespace Ace\RepoManUi;

/*
 * @author tim rodger
 * Date: 29/03/15
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
}