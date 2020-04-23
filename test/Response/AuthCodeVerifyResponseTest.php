<?php

namespace Test\Response;

use CurrencyFair\AppleId\Response\AuthCodeVerifyResponse;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AuthCodeVerifyResponseTest extends MockeryTestCase
{
    public function testCorrectDataReturned()
    {
        $expectedValues = [
            'access_token' => 'abc',
            'refresh_token' => 'abc',
            'token_type' => 'Bearer',
            'expires_in' => 360,
            'id_token' => 'abc'
        ];

        $authVerifyResponse = new AuthCodeVerifyResponse($expectedValues);

        $actualValues = [
            'access_token' => $authVerifyResponse->getAccessToken(),
            'refresh_token' => $authVerifyResponse->getRefreshToken(),
            'token_type' => $authVerifyResponse->getTokenType(),
            'expires_in' => $authVerifyResponse->getExpiresIn(),
            'id_token' => $authVerifyResponse->getIdToken(),
        ];

        $this->assertSame($expectedValues, $actualValues);
    }

    public function testNullsReturnedForMissingData()
    {
        $expectedValues = [
            'access_token' => null,
            'refresh_token' => null,
            'token_type' => null,
            'expires_in' => null,
            'id_token' => null,
        ];

        $authVerifyResponse = new AuthCodeVerifyResponse([]);

        $actualValues = [
            'access_token' => $authVerifyResponse->getAccessToken(),
            'refresh_token' => $authVerifyResponse->getRefreshToken(),
            'token_type' => $authVerifyResponse->getTokenType(),
            'expires_in' => $authVerifyResponse->getExpiresIn(),
            'id_token' => $authVerifyResponse->getIdToken(),
        ];

        $this->assertSame($expectedValues, $actualValues);
    }
}
