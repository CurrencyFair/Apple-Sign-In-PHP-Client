<?php

namespace Test;

use CurrencyFair\AppleId\Client;
use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;
use Mockery as m;

class ClientFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testCreateClient()
    {
        $config = m::mock(Config::class);
        $client = ClientFactory::create($config);
        $this->assertInstanceOf(Client::class, $client);
    }
}
