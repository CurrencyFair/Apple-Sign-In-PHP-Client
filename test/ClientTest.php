<?php

namespace Test;

use CurrencyFair\AppleId\Client;
use CurrencyFair\AppleId\Config;
use Exception;
use Firebase\JWT\ExpiredException;
use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Mockery as m;

class ClientTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /** @var string */
    private $appleJwk;

    /** @var string */
    private $appleJwt;

    protected function setUp()
    {
        $this->appleJwk = file_get_contents(__DIR__ . '/data/appleJwk.json');
        $this->appleJwt = file_get_contents(__DIR__ . '/data/appleJwt');
        parent::setUp();
    }

    public function testVerifyJwtKeyWithExpiredToken()
    {
        $responseStreamMock = m::mock(StreamInterface::class)
            ->shouldReceive('getContents')
            ->andReturn($this->appleJwk)
            ->getMock();

        $httpResponseMock = m::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->getMock()
            ->shouldReceive('getBody')
            ->andReturn($responseStreamMock)
            ->getMock();

        /** @var HttpClient $httpClientMock */
        $httpClientMock = m::mock(HttpClient::class)
            ->shouldReceive('get')
            ->with('keys_endpoint.com')
            ->andReturn($httpResponseMock)
            ->getMock();

        $service = new Client(
            $httpClientMock,
            new Config(
                [
                    Config::API_KEYS_ENDPOINT => 'keys_endpoint.com'
                ]
            )
        );

        $this->expectException(ExpiredException::class);
        $this->expectExceptionMessage('Expired token');

        $service->verifyAndDecodeJwt($this->appleJwt);
    }

    public function testVerifyJwtFailsWhenAppleApiUnavailable()
    {
        $expectedError = '{"error":"500"}';
        $responseStreamMock = m::mock(StreamInterface::class)
            ->shouldReceive('getContents')
            ->andReturn($expectedError)
            ->getMock();

        $httpResponseMock = m::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(500)
            ->getMock()
            ->shouldReceive('getBody')
            ->andReturn($responseStreamMock)
            ->getMock();

        /** @var HttpClient $httpClientMock */
        $httpClientMock = m::mock(HttpClient::class)
            ->shouldReceive('get')
            ->with('keys_endpoint.com')
            ->andReturn($httpResponseMock)
            ->getMock();

        $config = [
            Config::API_KEYS_ENDPOINT => 'keys_endpoint.com'
        ];

        $service = new Client(
            $httpClientMock, new Config($config)
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Received 500 response while fetching Apple\'s Public Key. Response Body: ' . $expectedError
        );
        $service->verifyAndDecodeJwt($this->appleJwt);
    }

    public function testVerifyJwtFailsWhenInvalidDataReturned()
    {
        $responseStreamMock = m::mock(StreamInterface::class)
            ->shouldReceive('getContents')
            ->andReturn('Invalid Data')
            ->getMock();

        $httpResponseMock = m::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->getMock()
            ->shouldReceive('getBody')
            ->andReturn($responseStreamMock)
            ->getMock();

        /** @var HttpClient $httpClientMock */
        $httpClientMock = m::mock(HttpClient::class)
            ->shouldReceive('get')
            ->with('keys_endpoint.com')
            ->andReturn($httpResponseMock)
            ->getMock();

        $service = new Client(
            $httpClientMock, new Config(
                [
                    Config::API_KEYS_ENDPOINT => 'keys_endpoint.com'
                ]
            )
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to decode JSON - Invalid data returned');
        $service->verifyAndDecodeJwt($this->appleJwt);
    }

    public function testGetAuthoriseUrl()
    {
        $expectedUrl = 'https://appleid.apple.com/auth/authorize?response_type=code+id_token&response_mode=form_post&' .
            'client_id=client&redirect_uri=redirect_url&state=state&scope=scopes';

        /** @var HttpClient $httpClientMock */
        $httpClientMock = m::mock(HttpClient::class);
        $service = new Client(
            $httpClientMock, new Config(
                [
                   Config::CLIENT_ID => 'client',
                   Config::REDIRECT_URI => 'redirect_url',
                   Config::DEFAULT_SCOPES => 'scopes',
                ]
            )
        );

        $actualUrl = $service->getAuthoriseUrl('state');
        $this->assertSame($expectedUrl, $actualUrl);
    }
}
