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

        #$this->givenAMockTokenService();
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
            ['url' => 'https://github.com/apps/service-a', 'language' => 'PHP7', 'dependency_manager' => 'composer', 'timezone' => 'GMT', 'active' => true]
        ];
        $this->whenLocalRepositoriesExist($user, $repos);

        $repositories = $this->repository_service->getRepositories($user);

        $this->assertInternalType('array', $repositories);

        $this->assertSame($repos, $repositories);
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
        $this->repository_service = new LocalRepositoryService(
            $this->repo_man_host,
            $this->mock_http_client
        );
    }
}
