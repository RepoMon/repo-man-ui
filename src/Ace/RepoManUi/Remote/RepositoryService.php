<?php namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 30/12/15
 */
class RepositoryService
{
    /**
     * @var string
     */
    private $api_host;

    /**
     * @var string
     */
    private $repo_man_host;

    /**
     * @var TokenService
     */
    private $token_service;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $api_host
     * @param string $repo_man_host
     */
    public function __construct($api_host, $repo_man_host, TokenService $token_service, Client $client)
    {
        $this->api_host = $api_host;
        $this->repo_man_host = $repo_man_host;
        $this->token_service = $token_service;
        $this->client = $client;
    }

    /**
     * @param string $user
     */
    public function getRepositories($user)
    {
        $token = $this->token_service->getToken($user);

    }
}