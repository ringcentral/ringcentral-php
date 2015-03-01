# Installation

## PHP >= 5.3 with Composer *(recommended)*
  
  1. Add ```ringcentral/php-sdk``` package to your ```composer.json``` file:
  
    ```json
    {
        "require": {
            "ringcentral/php-sdk": "*"
        }
    }
    ```
    
  2. Install dependencies:
    
    ```sh
    $ composer install
    ```

## PHP >= 5.3 without Composer

  1. Clone the repo:
  
    ```sh
    $ git clone https://github.com/ringcentral/php-sdk.git ./ringcentral-php-sdk
    ```
    
  2. Require autoloader:
  
    ```php
    require_once('path-to/ringcentral-php-sdk/lib/autoload.php');
    ```
    
# Basic Usage

## Initialization

```php
$rcsdk = new RC\SDK('appKey', 'appSecret', 'server');
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

```php
$response = $rcsdk->getPlatform()->get('/account/~/extension/~');
$response = $rcsdk->getPlatform()->post('/account/~/extension/~', null, {foo: 'bar'}); // QueryParameters are null
$response = $rcsdk->getPlatform()->put('/account/~/extension/~', null, {foo: 'bar'}); // QueryParameters are null
$response = $rcsdk->getPlatform()->delete('/account/~/extension/~', null, {foo: 'bar'}); // QueryParameters are null

print_r($response->getData());
print_r($response->getHeaders());
```

### Multipart response

Loading of multiple comma-separated IDs will result in HTTP 207 with `Content-Type: multipart/mixed`. This response will
be parsed into multiple sub-responses:

```php
$presences = $platform->get('/account/~/extension/id_1,id_2/presence')
                      ->getResponses();

print $extensions[0]->name . ' - ' . $presences[0]->getData()->presenceStatus . ', ' .
      $extensions[1]->name . ' - ' . $presences[1]->getData()->presenceStatus;
```