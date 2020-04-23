<?php

namespace CurrencyFair\AppleId;

use Exception;
use InvalidArgumentException;

class Config
{
    const CLIENT_ID = 'clientId';
    const PRIVATE_KEY = 'privateKey';
    const KEY_ID = 'keyId';
    const TEAM_ID = 'teamId';
    const REDIRECT_URI = 'redirectUri';
    const DEFAULT_SCOPES = 'defaultScopes';
    const API_KEYS_ENDPOINT = 'apiKeysEndpoint';
    const API_TOKEN_ENDPOINT = 'apiTokenEndpoint';
    const API_AUTH_ENDPOINT = 'apiAuthEndpoint';

    /** @var array */
    private static $allowedKeys = [
        self::CLIENT_ID,
        self::PRIVATE_KEY,
        self::KEY_ID,
        self::TEAM_ID,
        self::REDIRECT_URI,
        self::DEFAULT_SCOPES,
        self::API_KEYS_ENDPOINT,
        self::API_TOKEN_ENDPOINT,
        self::API_AUTH_ENDPOINT
    ];

    /** @var array */
    private $config = [
        self::DEFAULT_SCOPES => 'name email',
        self::API_KEYS_ENDPOINT => 'https://appleid.apple.com/auth/keys',
        self::API_TOKEN_ENDPOINT => 'https://appleid.apple.com/auth/token',
        self::API_AUTH_ENDPOINT => 'https://appleid.apple.com/auth/authorize',
    ];

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param string $configKey
     * @return string
     *
     * @throws Exception
     */
    public function get($configKey)
    {
        if (isset($this->config[$configKey])) {
            return $this->config[$configKey];
        }

        throw new Exception(sprintf('config value \'%s\' is not set.', $configKey));
    }

    /**
     * @param array $config
     *
     * @throws InvalidArgumentException
     */
    private function validateConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (!in_array($key, self::$allowedKeys)) {
                throw new InvalidArgumentException(sprintf('%s is not a valid config value.', $key));
            }
        }
    }
}
