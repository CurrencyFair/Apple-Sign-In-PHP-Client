<?php

namespace CurrencyFair\AppleId;

use GuzzleHttp\Client as HttpClient;

class ClientFactory
{
    /**
     * @param Config $config
     * @return Client
     */
    public static function create(Config $config)
    {
        return new Client(new HttpClient(), $config);
    }
}
