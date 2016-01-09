<?php
declare(strict_types=1);
namespace Ace\RepoManUi\Remote;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

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
     * @var Client
     */
    private $client;

    /**
     * TokenService constructor.
     * @param $token_service
     * @param Client $client
     */
    public function __construct(string $token_service, Client $client)
    {
        $this->token_service = $token_service;
        $this->client = $client;
    }

    /**
     * @param string $name
     * @return string
     * @throws UnavailableException
     */
    public function getToken(string $name) : string
    {
        try {

            // trim any white space from the response body
            return trim(
                $this->client->request(
                    'GET',
                    sprintf('%s/tokens/%s', $this->token_service, $name)
                )->getBody()
                    ->__toString()
            );

        } catch (TransferException $ex) {
            throw new UnavailableException($ex->getMessage());
        }

    }
}