<?php

namespace CurrencyFair\AppleId;

use CurrencyFair\AppleId\Response\AuthCodeVerifyResponse;
use CurrencyFair\AppleId\Response\JwtVerifyResponse;
use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

/**
 * Class Client
 * @package CurrencyFair\AppleId
 */
class Client
{
    /** @var int */
    const HTTP_OK = 200;

    /** @var ClientInterface */
    private $httpClient;

    /** @var Config */
    private $config;

    /**
     * @param ClientInterface $httpClient
     * @param Config $config
     */
    public function __construct(ClientInterface $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    /**
     * Verifies and decodes Apple JWTs and returns the decoded token information
     *
     * @param string $jwtToken
     * @return JwtVerifyResponse
     *
     * @throws Exception
     */
    public function verifyAndDecodeJwt($jwtToken)
    {
        return new JwtVerifyResponse(
            JWT::decode($jwtToken, $this->getApplePublicKey())
        );
    }

    /**
     * Verifies an Authorisation Code provided by Apple and returns token information
     *
     * @param string $authCode
     * @return AuthCodeVerifyResponse
     *
     * @throws Exception | RequestException
     */
    public function verifyAuthCode($authCode)
    {
        $response = $this->httpClient->post(
            $this->config->get(Config::API_TOKEN_ENDPOINT),
            [
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'authorization_code',
                    'code' => $authCode,
                    'redirect_uri' => $this->config->get(Config::REDIRECT_URI),
                    'client_id' => $this->config->get(Config::CLIENT_ID),
                    'client_secret' => $this->generateClientSecret(),
                ],
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        if ($response->getStatusCode() !== self::HTTP_OK) {
            throw new Exception(
                sprintf(
                    'Received %d response while verifying Authorisation Code. Response Body: %s',
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                )
            );
        }

        return new AuthCodeVerifyResponse(
            json_decode($response->getBody()->getContents(), true)
        );
    }

    /**
     * Returns a URL used to create a Sign-In with Apple Link
     *
     * @param string $state This will be POSTed back by to the redirect_uri.
     * @return string
     *
     * @throws Exception
     */
    public function getAuthoriseUrl($state = '')
    {
        return $this->config->get(Config::API_AUTH_ENDPOINT) . '?' . http_build_query(
            [
                'response_type' => 'code id_token',
                'response_mode' => 'form_post',
                'client_id' => $this->config->get(Config::CLIENT_ID),
                'redirect_uri' => $this->config->get(Config::REDIRECT_URI),
                'state' => $state,
                'scope' => $this->config->get(Config::DEFAULT_SCOPES),
            ]
        );
    }

    /**
     * Generate a client secret using the private key downloaded from the
     * Apple Developer area
     *
     * @see https://developer.apple.com/account/resources/authkeys/list
     *
     * @return string
     *
     * @throws Exception
     */
    private function generateClientSecret()
    {
        return JWT::encode(
            [
                'iss' => $this->config->get(Config::TEAM_ID),
                'iat' => time(),
                'exp' => time() + 3600,
                'aud' => 'https://appleid.apple.com',
                'sub' => $this->config->get(Config::CLIENT_ID),
            ],
            $this->getPrivateKey(),
            'ES256',
            $this->config->get(Config::KEY_ID)
        );
    }

    /**
     * Retrieves Apple's JWK public key
     *
     * @return array
     * @throws Exception | RequestException
     */
    private function getApplePublicKey()
    {
        $response = $this->httpClient->get($this->config->get(Config::API_KEYS_ENDPOINT));
        if ($response->getStatusCode() !== self::HTTP_OK) {
            throw new Exception(
                sprintf(
                    'Received %d response while fetching Apple\'s Public Key. Response Body: %s',
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                )
            );
        }

        $appleJwkKeyArray = json_decode($response->getBody()->getContents(), true);
        if (!is_array($appleJwkKeyArray)) {
            throw new Exception('Failed to decode JSON - Invalid data returned');
        }

        return JWK::parseKeySet($appleJwkKeyArray);
    }

    /**
     * @return resource
     *
     * @throws Exception
     */
    private function getPrivateKey()
    {
        $key = $this->config->get(Config::PRIVATE_KEY);
        $keyContents = is_file($key) ? file_get_contents($key) : $key;

        if (!$keyContents) {
            throw new Exception('Private key must be a string or a valid file path.');
        }

        if ($keyResource = openssl_pkey_get_private($keyContents)) {
            return $keyResource;
        }

        throw new Exception(
            sprintf(
                'Error processing private key. Please check your \'%s\' config value',
                Config::PRIVATE_KEY
            )
        );
    }
}
