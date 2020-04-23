<?php

namespace Test;

use CurrencyFair\AppleId\Config;
use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ConfigTest extends MockeryTestCase
{
    public function testInvalidConfig()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid Key is not a valid config value.');
        (new Config(['Invalid Key' => 'Value']));
    }

    public function testDefaultValuesAreSet()
    {
        $expectedValue = 'https://appleid.apple.com/auth/keys';
        $config = new Config([]);
        $this->assertEquals($expectedValue, $config->get(Config::API_KEYS_ENDPOINT));
    }

    public function testCanOverrideDefaultValue()
    {
        $configValue = 'http://override.com';
        $config = new Config(
            [
                Config::API_KEYS_ENDPOINT => $configValue
            ]
        );
        $this->assertEquals($configValue, $config->get(Config::API_KEYS_ENDPOINT));
    }
}
