<?php

namespace Test\Response;

use CurrencyFair\AppleId\Response\JwtVerifyResponse;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class JwtVerifyResponseTest extends MockeryTestCase
{
    /** @var stdClass */
    private $appleJwt;

    protected function setUp()
    {
        $this->appleJwt = json_decode(file_get_contents(__DIR__ . '/../data/appleJwtDecoded.json'));
        parent::setUp();
    }

    public function testCorrectValuesReturned()
    {
        $jwtVerifyResponse = new JwtVerifyResponse($this->appleJwt);

        $this->assertSame($this->appleJwt, $jwtVerifyResponse->getDecodedTokenObject());
        $this->assertSame($this->appleJwt->email, $jwtVerifyResponse->getEmail());
        $this->assertSame($this->appleJwt->aud, $jwtVerifyResponse->getAudience());
        $this->assertSame($this->appleJwt->iat, $jwtVerifyResponse->getIssuedAt());
        $this->assertSame($this->appleJwt->sub, $jwtVerifyResponse->getSubject());
        $this->assertSame($this->appleJwt->c_hash, $jwtVerifyResponse->getCodeHash());
        $this->assertSame($this->appleJwt->at_hash, $jwtVerifyResponse->getAccessTokenHash());
        $this->assertSame($this->appleJwt->exp, $jwtVerifyResponse->getExpiry());
        $this->assertSame($this->appleJwt->iss, $jwtVerifyResponse->getIssuer());
        $this->assertSame($this->appleJwt->auth_time, $jwtVerifyResponse->getAuthTime());
        $this->assertTrue($jwtVerifyResponse->getEmailVerified());
        $this->assertTrue($jwtVerifyResponse->getIsPrivateEmail());
        $this->assertTrue($jwtVerifyResponse->getNonceSupported());
        $this->assertTrue($jwtVerifyResponse->getIsPrivateEmail());
    }
}
