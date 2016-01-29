<?php

use Ace\RepoManUi\Remote\GitRepositoryService;


/**
 * @author timrodger
 * Date: 30/12/15
 */
class GitRepositoryServiceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var GitRepositoryService
     */
    private $repository_service;

    /**
     * @var string
     */
    private $git_api_host = 'https://api.github.com';

    /**
     * @var \Ace\RepoManUi\Remote\TokenService
     */
    private $mock_token_service;

    /**
     * @var string
     */
    private $token = 'abcd1234';

    /**
     * @var
     */
    private $mock_http_client;

    public function setUp()
    {
        parent::setUp();

        $this->givenAMockTokenService();
        $this->givenAMockHttpClient();
        $this->givenARepositoryService();
    }

    public function testGetRepositoriesReturnsEmptyWhenNoneExist()
    {
        $user = 'agent-orange';
        $this->whenGitRepositoriesExist($user, []);

        $repositories = $this->repository_service->getRepositories($user, 'GMT');

        $this->assertInternalType('array', $repositories);
        $this->assertSame([], $repositories);
    }

    public function testGetRepositoriesReturnsExistingRepositories()
    {
        $user = 'agent-orange';
        $repos = [
            ['html_url' => 'https://github.com/apps/service-a', 'language' => 'PHP', 'description' => 'A repository', 'private' => false]
        ];

        $this->whenGitRepositoriesExist($user, $repos);

        $repositories = $this->repository_service->getRepositories($user, 'Europe/London');

        $this->assertInternalType('array', $repositories);
        $repository = current($repositories);

        $this->assertSame($repos[0]['html_url'], $repository->getUrl());
        $this->assertSame($repos[0]['description'], $repository->getDescription());
        $this->assertSame($repos[0]['language'], $repository->getLanguage());
        $this->assertSame('composer', $repository->getDependencyManager());
        $this->assertSame('Europe/London', $repository->getTimezone());
        $this->assertSame(false, $repository->isActive());
    }

    public function testGetRepositoriesHandlesRepositoriesWithoutLanguages()
    {
        $user = 'agent-orange';
        $repos = [
            ['html_url' => 'https://github.com/apps/service-a', 'language' => null, 'description' => 'A repository', 'private' => false]
        ];

        $this->whenGitRepositoriesExist($user, $repos);

        $repositories = $this->repository_service->getRepositories($user, 'Europe/London');

        $this->assertInternalType('array', $repositories);
        $repository = current($repositories);

        $this->assertSame('', $repository->getLanguage());
        $this->assertSame('', $repository->getDependencyManager());
    }

    public function testGetRepositoriesHandlesRepositoriesWithoutADescription()
    {
        $user = 'agent-orange';
        $repos = [
            ['html_url' => 'https://github.com/apps/service-a', 'language' => 'PHP', 'description' => null, 'private' => false]
        ];

        $this->whenGitRepositoriesExist($user, $repos);

        $repositories = $this->repository_service->getRepositories($user, 'Europe/London');

        $this->assertInternalType('array', $repositories);
        $repository = current($repositories);

        $this->assertSame('', $repository->getDescription());
    }

    /**
     * @expectedException Ace\RepoManUi\Remote\UnavailableException
     */
    public function testGetRepositoriesThrowsExceptionOnError()
    {
        $user = 'agent-orange';

        $this->mock_http_client->expects($this->any())
            ->method('request')
            ->will($this->throwException(new GuzzleHttp\Exception\TransferException));

        $this->repository_service->getRepositories($user, 'GMT');
    }

    private function whenGitRepositoriesExist($user, array $repositories)
    {
        $this->mock_token_service->expects($this->any())
            ->method('getToken')
            ->with($user)
            ->will($this->returnValue($this->token));

        $mock_response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->getMock();

        $mock_response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue(json_encode($repositories)));

        $this->mock_http_client->expects($this->any())
            ->method('request')
            ->with('GET', $this->git_api_host . '/user/repos', [
                'query' => [
                    'access_token' => $this->token
                ],
                'headers' => [
                    'Accept' => 'application/json'
                ]])
            ->will($this->returnValue($mock_response));
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
        $this->repository_service = new GitRepositoryService(
            $this->git_api_host,
            $this->mock_token_service,
            $this->mock_http_client
        );
    }
}
