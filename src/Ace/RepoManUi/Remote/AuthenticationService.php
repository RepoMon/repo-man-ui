<?php
namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;

/**
 * @author timrodger
 * Date: 02/01/16
 */
class AuthenticationService
{
    /**
     * @var string
     */
    private $authn_host;

    /**
     * @var string
     */
    private $api_host;

    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $authn_host
     * @param string $api_host
     * @param string $client_id
     * @param string $client_secret
     * @param Client $client
     */
    public function __construct(string $authn_host, string $api_host, string $client_id, string $client_secret, Client $client)
    {
        $this->authn_host = $authn_host;
        $this->api_host = $api_host;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getAuthenticationEndPoint() : string
    {
        return sprintf("%s/login/oauth/authorize?scope=user,public_repo&client_id=%s", $this->authn_host, $this->client_id);
    }

    /**
     * Verifies that the code is valid, ie. that the user has authenticated
     * @param string $code
     * @return string
     */
    public function getAccessTokenFromCode(string $code) : string
    {
        // get the access token
        $response = $this->client->request('POST', $this->authn_host . '/login/oauth/access_token', [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $authn_data = json_decode($response->getBody(), true);
        return $authn_data['access_token'];
    }

    /**
     * Retrieve the account data from the access token provided
     * @param string $token
     * @return array
     */
    public function getUserDataFromAccessToken(string $token) : array
    {
        $response = $this->client->request('GET', $this->api_host . '/user', [
            'query' => [
                'access_token' => $token
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}