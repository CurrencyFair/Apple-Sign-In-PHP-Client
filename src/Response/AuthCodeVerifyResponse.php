<?php

namespace CurrencyFair\AppleId\Response;

class AuthCodeVerifyResponse
{
    /** @var string */
    private $accessToken;

    /** @var string */
    private $tokenType;

    /** @var int */
    private $expiresIn;

    /** @var string */
    private $refreshToken;

    /** @var string */
    private $idToken;

    public function __construct(array $response)
    {
        $this->accessToken = isset($response['access_token']) ? $response['access_token'] : null;
        $this->tokenType = isset($response['token_type']) ? $response['token_type'] : null;
        $this->expiresIn = isset($response['expires_in']) ? $response['expires_in'] : null;
        $this->refreshToken = isset($response['refresh_token']) ? $response['refresh_token'] : null;
        $this->idToken = isset($response['id_token']) ? $response['id_token'] : null;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function getIdToken()
    {
        return $this->idToken;
    }
}
