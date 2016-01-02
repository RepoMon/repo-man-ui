<?php namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * Handles access to git repositories from a repo-man service
 * @author timrodger
 * Date: 30/12/15
 */
class LocalRepositoryService
{
    /**
     * @var string
     */
    private $repo_man_host;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param $repo_man_host
     * @param Client $client
     */
    public function __construct($repo_man_host, Client $client)
    {
        $this->repo_man_host = $repo_man_host;
        $this->client = $client;
    }

    /**
     * Return repository data used by UI
     * [
     *  'url' => url of repository,
     *  'language' => language in use
     *  'dependency_manager' => eg composer or npm
     *  'timezone' => 'gmt'
     *  'active' => boolean "is configured"?
     * ]
     * @param string $user
     * @return array
     */
    public function getRepositories($user)
    {
        try {
            $response = $this->client->request('GET', $this->repo_man_host . '/repositories/' . $user, [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (TransferException $ex) {
            throw new UnavailableException($ex->getMessage());
        }
    }
}