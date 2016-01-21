<?php namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * Handles access to git repositories from a GitHub service
 *
 * @author timrodger
 * Date: 30/12/15
 */
class GitRepositoryService
{
    /**
     * @var string
     */
    private $git_api_host;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var TokenService
     */
    private $token_service;

    /**
     * @param $git_api_host
     * @param TokenService $token_service
     * @param Client $client
     */
    public function __construct(string $git_api_host, TokenService $token_service, Client $client)
    {
        $this->git_api_host = $git_api_host;
        $this->token_service = $token_service;
        $this->client = $client;
    }

    /**
     * Return array of \Ace\RepoManUi\Remote\Repository objects
     * @param string $user
     * @param string $timezone
     * @return array
     * @throws UnavailableException
     */
    public function getRepositories(string $user, string $timezone) : array
    {
        try {
            $token = $this->token_service->getToken($user);

            $response = $this->client->request(
                'GET',
                $this->git_api_host . '/user/repos',
                [
                'query' => [
                    'access_token' => $token
                    ],
                'headers' => [
                    'Accept' => 'application/json'
                    ]
                ]
            );

            $repositories = [];

            foreach (json_decode($response->getBody(), true) as $data) {

                $description = $data['description'] ? : '';
                $language = $data['language'] ? : '';
                $private = (bool) $data['private'];
                $repository = new Repository($data['html_url'], $description, $language, $private);
                $repository->setTimezone($timezone);
                $repositories [$repository->getFullName()]= $repository;

            }
            return $repositories;

        } catch (TransferException $ex) {
            throw new UnavailableException($ex->getMessage());
        }
    }
}