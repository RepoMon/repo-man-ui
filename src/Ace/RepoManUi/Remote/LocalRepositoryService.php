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
     * @param string $repo_man_host
     * @param Client $client
     */
    public function __construct(string $repo_man_host, Client $client)
    {
        $this->repo_man_host = $repo_man_host;
        $this->client = $client;
    }

    /**
     * Return array of \Ace\RepoManUi\Remote\Repository objects
     *
     * @param string $user
     * @return array
     */
    public function getRepositories(string $user) : array
    {
        try {
            $response = $this->client->request('GET', $this->repo_man_host . '/repositories/' . $user, [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            $repositories = [];
            foreach (json_decode($response->getBody(), true) as $data) {
                $repository = new Repository($data['url'], $data['description'], $data['lang']);
                $repository->setTimezone($data['timezone']);
                $repository->setActive($data['active'] == '1');
                $repositories [$repository->getFullName()]= $repository;
            }
            return $repositories;

        } catch (TransferException $ex) {
            throw new UnavailableException($ex->getMessage());
        }
    }
}