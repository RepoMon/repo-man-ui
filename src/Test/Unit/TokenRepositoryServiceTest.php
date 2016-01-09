<?php namespace Test\Unit;

use Ace\RepoManUi\Remote\TokenService;
use PHPUnit_Framework_TestCase;

/**
 * @author timrodger
 */
class TokenRepositoryServiceTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var TokenService
     */
    private $token_service;

    private $token_host = 'https://token';

    private $mock_http_client;

    public function setUp()
    {
        parent::setUp();

        $this->givenAMockHttpClient();
        $this->givenATokenService();
    }

    public function testGetTokensReturnsToken()
    {
        $user = 'agent-orange';
        $token = 'abcd1234';

        $this->whenATokenExists($user, $token);

        $actual = $this->token_service->getToken($user);

        $this->assertSame($token, $actual);
    }

    /**
     * @expectedException \Ace\RepoManUi\Remote\UnavailableException
     */
    public function testGetRepositoriesThrowsExceptionOnError()
    {
        $user = 'agent-orange';

        $this->mock_http_client->expects($this->any())
            ->method('request')
            ->will($this->throwException(new \GuzzleHttp\Exception\TransferException));

        $this->token_service->getToken($user);
    }

    private function whenATokenExists($user, $token)
    {
        $mock_stream = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $mock_stream->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($token));

        $mock_response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->getMock();

        $mock_response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($mock_stream));

        $this->mock_http_client->expects($this->any())
            ->method('request')
            ->with('GET', $this->token_host . '/tokens/' . $user)
            ->will($this->returnValue($mock_response));
    }

    private function givenAMockHttpClient()
    {
        $this->mock_http_client = $this->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
    }

    private function givenATokenService()
    {
        $this->token_service = new TokenService(
            $this->token_host,
            $this->mock_http_client
        );
    }
}
