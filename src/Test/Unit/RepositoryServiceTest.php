<?php

use Ace\RepoManUi\Remote\RepositoryService;

use PHPUnit_Framework_TestCase;

/**
 * @author timrodger
 * Date: 30/12/15
 */
class RepositoryServiceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var RepositoryService
     */
    private $repository_service;

    private $api_host = 'https://api.github.com';

    private $repo_man_host = 'https://repoman';

    private $mock_token_service;

    private $mock_http_client;

    public function setUp()
    {
        parent::setUp();
        $this->givenAMockTokenService();
        $this->givenAMockHttpClient();
        $this->givenARepositoryService();
    }

    public function testGetRepositories()
    {
        $user = 'agent-orange';

        $repositories = $this->repository_service->getRepositories($user);
        $this->assertInternalType('array', $repositories);
    }

    private function givenAMockTokenService()
    {
        $this->mock_token_service = $this->getMockBuilder('Ace\RepoManUi\Remote\TokenService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function givenAMockHttpClient()
    {
        $this->mock_http_client = $this->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
    }

    private function givenARepositoryService()
    {
        $this->repository_service = new RepositoryService(
            $this->api_host,
            $this->repo_man_host,
            $this->mock_token_service,
            $this->mock_http_client
        );
    }
}
