<?php namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 13/12/15
 */
class TokenService
{
    /**
     * @var string
     */
    private $token_service;

    /**
     * @var string
     */
    private $service_name;

    /**
     * @param $token_service string
     * @param $service_name string
     */
    public function __construct($token_service, $service_name)
    {
        $this->token_service = $token_service;
        $this->service_name = $service_name;
    }

    /**
     * @param $name
     * @return string
     */
    public function getToken($name)
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => $this->service_name
            ]
        ]);

        // trim any white space from the response body
        $endpoint = sprintf('%s/tokens/%s', $this->token_service, $name);

        return trim(
            $client->request('GET', $endpoint)->getBoody()
        );

    }
}