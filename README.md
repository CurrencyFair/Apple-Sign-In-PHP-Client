# Apple Sign-In PHP Client
![PHPUnit Test Suite](https://github.com/CurrencyFair/Apple-Sign-In-PHP-Client/workflows/PHPUnit%20Test%20Suite/badge.svg)

Features Include:

 - Generating an Apple authorisation link to use with your Sign-In button
 - Verifying and decoding Apple JWTs
 - Verifying Apple Authorisation Codes and exchanging them with Apple's API for access/refresh tokens
 - Automatic fetching of Apple's public keys and generating of client secrets.
 
## Contents
 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Usage & Examples](#usage--examples)
    - [Verify Auth Code & Fetching Access/Refresh Tokens](#verifying-an-authorisation-code-and-retrieving-the-accessrefresh-tokens)
    - [Verifying & Decoding Apple's JWTs](#verifying-and-decoding-apple-jwts)
    - [Generating an Authorisation URL for your Sign-In button](#generating-an-authorisation-url-for-your-sign-in-button)
    - [End-to-End Sign-In page & Return Page](#end-to-end-sign-in-page--return-page)
 - [FAQ & Troubleshooting](#faq--troubleshooting)
 - [Useful Links](#useful-links)
 - [License](#license)
 
 ## Installation 
 
```bash
composer require currencyfair/apple-sign-in-php-client
```
 
 ## Configuration
 
 | Config Key  | Description |
 | ------------- | ------------- |
 | clientId | Also referred to as Service ID. This can be found [here](https://developer.apple.com/account/resources/identifiers/list/serviceId).  |
 | privateKey | The is required to generate the `Client Secret` which is used to verify Authorisation Codes. You can pass a string or the path to the key file. The key can be created and downloaded [here](https://developer.apple.com/account/resources/authkeys/list). |
 | keyId | The ID associated with the above `privateKey`. This should be available on the page where you downloaded your private key.|
 | teamId | This is usually found in the top right corner under your name in the Apple Developer area. |
 | redirectUri | This is the web page users will be redirect to after (un)successful sign-in. This address must be HTTPS and cannot be localhost. See FAQ for localhost workaround. |
 | defaultScopes | These are the scopes you would like returned from Apple. Apple only supports `name` and `email`. |
 | apiKeysEndpoint (optional) | URL containing Apple's public key in [JWK format](https://tools.ietf.org/html/rfc7517). Unless you have a reason to change this the default should be fine. |
 | apiTokenEndpoint (optional) | The endpoint used to verify Authorisation Codes. Unless you have a reason to change this the default should be fine.  |
 | apiAuthEndpoint (optional) | The authorisation URL used to build the URL users will sign in on. Unless you have a reason to change this the default should be fine. |
   
   See below for examples of passing config values.

## Usage & Examples

### Verifying an Authorisation Code and retrieving the access/refresh tokens

```php
<?php

include './vendor/autoload.php';

use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;

$config = new Config(
    [
        Config::REDIRECT_URI => 'https://your-redirect.com/',
        Config::CLIENT_ID => 'XXX',
        Config::TEAM_ID => 'XXX',
        Config::KEY_ID => 'XXX',
        Config::PRIVATE_KEY => '/full/path/to/key' // Or a string containing your key
    ]
);

$client = ClientFactory::create($config);
$authCodeResponse = $client->verifyAuthCode($_POST['code']);

echo $authCodeResponse->getAccessToken();
echo $authCodeResponse->getExpiresIn();
echo $authCodeResponse->getRefreshToken();
```
See [AuthCodeVerifyResponse](https://github.com/CurrencyFair/Apple-Sign-In-PHP-Client/blob/master/src/Response/AuthCodeVerifyResponse.php) for all available methods. 

## Verifying and Decoding Apple JWTs

```php
<?php

include './vendor/autoload.php';

use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;

$config = new Config(
    [
        Config::REDIRECT_URI => 'https://your-redirect.com/',
        Config::CLIENT_ID => 'XXX',
        Config::TEAM_ID => 'XXX',
        Config::KEY_ID => 'XXX',
        Config::PRIVATE_KEY => '/full/path/to/key' // Or a string containing your key
    ]
);

$client = ClientFactory::create($config);
$jwtResponse = $client->verifyAndDecodeJwt($_POST['id_token']);

echo $jwtResponse->getEmail();
echo $jwtResponse->getSubject(); // Unique user ID provided by Apple
echo $jwtResponse->getIsPrivateEmail();
echo $jwtResponse->getDecodedTokenObject(); // The unmodified JWT object (Example format below)
```
See [JwtVerifyResponse](https://github.com/CurrencyFair/Apple-Sign-In-PHP-Client/blob/master/src/Response/JwtVerifyResponse.php) for all available methods. 

### Example Decoded JWT
```json
{
  "iss": "https://appleid.apple.com",
  "aud": "com.yourApp.web",
  "exp": 1586606495,
  "iat": 1586605895,
  "sub": "000609.fac4e6e9df6a4c1988870f61b86e0b8e.0000",
  "at_hash": "XXX",
  "email": "example@example.com",
  "email_verified": "true",
  "is_private_email": "false",
  "auth_time": 1586605860,
  "nonce_supported": true
}
```

## Generating an Authorisation URL for your Sign-In button

```php
<?php

include './vendor/autoload.php';

use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;

$config = new Config(
    [
        Config::REDIRECT_URI => 'https://your-redirect.com/',
        Config::CLIENT_ID => 'XXX',
    ]
);

$client = ClientFactory::create($config);
$authorisationUrl = $client->getAuthoriseUrl();

echo "<a href='{$authorisationUrl}'> Sign-In With Apple</a>";

```

You can also use [Apple's JS SDK](https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_js/configuring_your_webpage_for_sign_in_with_apple) 
to show Apple's pre-styled button. Using the above method is for when you would like more control over
the style of the button.

## End-to-End Sign-In page & Return Page

### your-sign-in-page.php
```php
<?php

include './vendor/autoload.php';

use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;

$config = new Config(
    [
        Config::REDIRECT_URI => 'https://example.com/your-return-page.php',
        Config::CLIENT_ID => 'XXX',
    ]
);

// We will use this to verify the request came from Apple on
// the return page
$_SESSION['state'] = 'Something Random';
 
$client = ClientFactory::create($config);
$authorisationUrl = $client->getAuthoriseUrl($_SESSION['state']);

echo "<a href='{$authorisationUrl}'> Sign-In With Apple</a>";
```

### your-return-page.php
```php
<?php

include './vendor/autoload.php';

use CurrencyFair\AppleId\ClientFactory;
use CurrencyFair\AppleId\Config;

$state    = $_POST['state'];
$code     = $_POST['code'];
$idToken  = $_POST['id_token'];
$user     = isset($_POST['user']) ? json_decode($_POST['user'], true) : null;

// Check if the state returned from Apple matches the state we passed to 
// Apple from the Sign-In button
if ($_SESSION['state'] !== $state) {
    throw new Exception('State is invalid');
}

// The user will be available the first time a user registers and *not* passed
// on subsequent Sign-Ins 
if ($user) {
    $fullName = $user['name']['firstName'] . ' ' . $user['name']['lastName'];
    $email = $user['email'];
}

$config = new Config(
    [
        Config::REDIRECT_URI => 'https://example.com/your-return-page.php',
        Config::CLIENT_ID => 'XXX',
        Config::TEAM_ID => 'XXX',
        Config::KEY_ID => 'XXX',
        Config::PRIVATE_KEY => '/full/path/to/key'
    ]
);

$client = ClientFactory::create($config);

$authCodeResponse = $client->verifyAuthCode($code);

echo $authCodeResponse->getAccessToken() . PHP_EOL;
echo $authCodeResponse->getExpiresIn() . PHP_EOL;
echo $authCodeResponse->getRefreshToken() . PHP_EOL;

$jwtResponse = $client->verifyAndDecodeJwt($idToken);

echo $jwtResponse->getEmail() . PHP_EOL;
echo $jwtResponse->getSubject() . PHP_EOL; // Unique user ID provided by Apple
echo $jwtResponse->getIsPrivateEmail() . PHP_EOL;
```

## FAQ & Troubleshooting
### I'm developing on localhost, how do I get the redirect URI to work correctly?
Unfortunately even during testing Apple doesn't allow using localhost or non-HTTPS redirect URLs. To get around this you can use a browser extension like [Requestly](https://www.requestly.in/) to intercept the 
redirect and direct it to your localhost URL. You can also use a secure tunneling tool like [Ngrok](https://ngrok.com).

### I'm getting an `invalid_request - Invalid redirect_uri` error
This usually occurs if your Redirect URI isn't configured for use in the Apple Developer area. Or the URI may
be localhost or non-HTTPS.

### I'm getting an `Invalid Grant` error when verifying my Authorisation Code
This usually means your token is expired or malformed. Apple's tokens have a 10 minute expiry, after this you
will need to generate a new token.

### How do I get the user's name from Apple?
Apple will only send the user's name the first time the user registers on your app. The payload is POSTed to 
the Redirect URI along with the authorisation code and the JWT token. The format will look like this:

```json
{"name":{"firstName":"Dave","lastName":"Tester"},"email":"an@email.com"}
```

### I would like the Sign-In to happen in a pop-up window
You can use [Apple's JS SDK](https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_js/configuring_your_webpage_for_sign_in_with_apple) to achieve this.

### I'm getting an `Error processing private key` error?
If you're passing the key as a string ensure the formatting is correct. An example of the correct way to pass the key:

```php
$privateKey = <<<KEY
-----BEGIN PRIVATE KEY-----
XIGTAgEAxBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQg4UQtxp926Ihaslco
k5Pl8Rb8h4GLav9INTdZA4dD6EmgCgYIKoZIzj0DAQehRANCAAQVRW9gj0lXdSfo
EQxtT6zytXXXg64dsb0SGFV2ceYYkvOpVnjXkPCOxSkoYHPhP7eZK65sWyjOiS9
dLRtRpJX
-----END PRIVATE KEY-----
KEY;

$config = new Config(
    [
        Config::PRIVATE_KEY => $privateKey,
    ]
);
```

### Can the request to fetch Apple's public key be cached?
Yes, you can use [Guzzle Middleware](https://github.com/Kevinrob/guzzle-cache-middleware#how) to handle caching. You can also inject your own cache enabled client which implements [ClientInterface](https://github.com/guzzle/guzzle/blob/master/src/ClientInterface.php#L13). 

## Useful links
https://developer.apple.com/documentation/sign_in_with_apple/sign_in_with_apple_js/configuring_your_webpage_for_sign_in_with_apple

https://developer.okta.com/blog/2019/06/04/what-the-heck-is-sign-in-with-apple

https://sarunw.com/posts/sign-in-with-apple-3

## License
<p align="center">
    <a href="https://www.currencyfair.com">
        <img src="https://www.currencyfair.com/cf-content/themes/cfwp-2018/build/assets/svg/logos/logo-currencyfair.svg?x18325" alt="Currencyfair Logo">
    </a>
</p>

Developed by CurrencyFair (https://currencyfair.com) and licensed under the terms of the [Apache License, Version 2.0](https://github.com/CurrencyFair/Apple-Sign-In-PHP-Client/blob/master/LICENSE).
