<?php namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
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
    public function __construct($git_api_host, TokenService $token_service, Client $client)
    {
        $this->git_api_host = $git_api_host;
        $this->token_service = $token_service;
        $this->client = $client;
    }

    /**
     * Return repository data used by UI from the git api host
     * [
     *  'url' => url of repository,
     *  'language' => language in use
     *  'dependency_manager' => eg composer or npm
     *  'timezone' => 'gmt'
     *  'active' => boolean "is configured"?
     * ]
     * @param string $user
     * @param string $timezone
     * @return array
     */
    public function getRepositories($user, $timezone)
    {

        try {
            $token = $this->token_service->getToken($user);

            $response = $this->client->request(
                'GET',
                $this->git_api_host . '/user/repos/',
                [
                'query' => [
                    'access_token' => $token
                    ],
                'headers' => [
                    'Accept' => 'application/json'
                    ]
                ]
            );

            $result = [];

            $repos = json_decode($response->getBody(), true);

            foreach ($repos as $repo) {

                $data = [
                    'url' => $repo['html_url'],
                    'language' => $repo['language'],
                    'active' => false,
                    'timezone' => $timezone
                ];

                $data['dependency_manager'] = $this->extractDependencyManager($data['language']);

                $result []= $data;

            }
            return $result;

        } catch (TransferException $ex) {
            throw new UnavailableException($ex->getMessage());
        }
    }

    private function extractDependencyManager($language)
    {
        switch (strtolower($language)) {
            case 'php7':
                'php';
                return 'composer';

            case 'javascript':
                return 'npm';
        }

        return '';
    }
}