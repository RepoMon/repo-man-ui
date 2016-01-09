<?php

use Ace\RepoManUi\Remote\LocalRepositoryService;


/**
 * @author timrodger
 * Date: 30/12/15
 */
class LocalRepositoryServiceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var LocalRepositoryService
     */
    private $repository_service;

    private $repo_man_host = 'https://repoman';

    private $mock_http_client;

    public function setUp()
    {
        parent::setUp();

        $this->givenAMockHttpClient();
        $this->givenARepositoryService();
    }

    public function testGetRepositoriesReturnsEmptyWhenNoneExist()
    {
        $user = 'agent-orange';

        $this->whenLocalRepositoriesExist($user, []);
        $repositories = $this->repository_service->getRepositories($user);
        $this->assertInternalType('array', $repositories);
        $this->assertSame([], $repositories);
    }

    public function testGetRepositoriesReturnsExistingRepositories()
    {
        $user = 'agent-orange';
        $repos = [
            [
                'url' => 'https://github.com/apps/service-a',
                'description' => 'A repository',
                'lang' => 'PHP',
                'dependency_manager' => 'composer',
                'timezone' => 'Europe/London',
                'active' => true
            ]
        ];
        $this->whenLocalRepositoriesExist($user, $repos);

        $repositories = $this->repository_service->getRepositories($user);

        $this->assertInternalType('array', $repositories);

        $repository = current($repositories);

        $this->assertSame($repos[0]['url'], $repository->getUrl());
        $this->assertSame($repos[0]['description'], $repository->getDescription());
        $this->assertSame($repos[0]['lang'], $repository->getLanguage());
        $this->assertSame($repos[0]['dependency_manager'], $repository->getDependencyManager());
        $this->assertSame($repos[0]['timezone'], $repository->getTimezone());
        $this->assertSame($repos[0]['active'], $repository->isActive());
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

        $this->repository_service->getRepositories($user);
    }

    private function whenLocalRepositoriesExist($user, array $repositories)
    {
        $mock_response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->getMock();

        $mock_response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue(json_encode($repositories)));

        $this->mock_http_client->expects($this->any())
            ->method('request')
            ->with('GET', $this->repo_man_host . '/repositories/' . $user, [
                'headers' => [
                    'Accept' => 'application/json'
                ]])
            ->will($this->returnValue($mock_response));
    }

    private function givenAMockHttpClient()
    {
        $this->mock_http_client = $this->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
    }

    private function givenARepositoryService()
    {
        $this->repository_service = new LocalRepositoryService(
            $this->repo_man_host,
            $this->mock_http_client
        );
    }
}
