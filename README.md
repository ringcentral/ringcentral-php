# Installation

## With [Composer](http://getcomposer.org) *(recommended)*
  
  1. Install composer:
    
    ```sh
    $ curl -sS https://getcomposer.org/installer | php
    ```
  
  2. Run the Composer command to install the latest version of SDK:
  
    ```sh
    $ composer require ringcentral/php-sdk
    ```

  3. Require Composer's autoloader:
    
    ```php
    require('vendor/autoload.php');
   ```

Also please read [Guzzle Installation Docs](http://docs.guzzlephp.org/en/latest/overview.html#installation).

## Without Composer

  1. Download [PHAR file](https://github.com/ringcentral/php-sdk/blob/master/dist/rcsdk.phar)
  
  2. Download PHAR from [Guzzle Releases](https://github.com/guzzle/guzzle/releases).
  
  3. Require files:
  
    ```php
    require('guzzle.phar');
    require('rcsdk.phar');
    ```
    
# Basic Usage

## Initialization

```php
$rcsdk = new RC\SDK('appKey', 'appSecret', 'https://platform.devtest.ringcentral.com');
```

## Authentication

Check authentication status:

```php
$rcsdk->getPlatform()->isAuthorized(); // throws exception if not authorized after automatic refresh
```

Authenticate user:

```php
$rcsdk->getPlatform()->authorize('username', 'extension (or leave blank)', 'password', true); // change true to false to not remember user
```

### Authentication lifecycle

Platform class performs token refresh procedure if needed. You can save authentication between requests in CGI mode:

```js
// when application is going to be stopped
file_put_contents($file, json_encode($platform->getAuthData(), JSON_PRETTY_PRINT));

// and then next time during application bootstrap before any authentication checks:
$rcsdk->getPlatform()->setAuthData(json_decode(file_get_contents($file));
```

**Important!** You have to manually maintain synchronization of RCSDK's between requests if you share authentication.
When two simultaneous requests will perform refresh, only one will succeed. One of the solutions would be to have
semaphor and pause other pending requests while one of them is performing refresh.

## Performing API call

Platform class extends [Guzzle Client](http://guzzle.readthedocs.org/en/latest/quickstart.html) so anything that can be
done via Guzzle Client can be done via Platform (and more). Guzzle Client is pre-configured when SDK instance is
created, no extra configuration is needed.

```php
$response = $rcsdk->getPlatform()->get('/account/~/extension/~');
$response = $rcsdk->getPlatform()->post('/account/~/extension/~');
$response = $rcsdk->getPlatform()->put('/account/~/extension/~');
$response = $rcsdk->getPlatform()->delete('/account/~/extension/~');

print_r($response->getJson()); // stdClass will be returned or exception if Content-Type is not JSON
```

Also generic `getData()` method can be used, it returns:

- A simple PHP array if response is JSON
- An array of `Response` objects if response is Multipart
- A string if non of the above

### Multipart response

Loading of multiple comma-separated IDs will result in HTTP 207 with `Content-Type: multipart/mixed`. This response will
be parsed into multiple sub-responses:

```php
$presences = $rcsdk->getPlatform()->get('/account/~/extension/id1,id2/presence')->getResponses();

print 'Presence loaded ' .
      $presences[0]->getJson()->presenceStatus . ', ' .
      $presences[1]->getJson()->presenceStatus . PHP_EOL;
```

### Send SMS - Make POST request

```php

$response = $rcsdk->getPlatform()->post('/account/~/extension/~/sms', [
    'json' => [
        'from' => ['phoneNumber' => 'your-RC-sms-number'],
        'to'   => [
            ['phoneNumber' => 'mobile-number'],
        ],
        'text' => 'Test from PHP',
    ]
]);
```

### Get Platform error message

```php
try {

    $platform->get('/account/~/whatever');

} catch (\GuzzleHttp\Exception\RequestException $e) {

    print 'Expected HTTP Error: ' . $e->getResponse()->getError() . PHP_EOL;

}
```
