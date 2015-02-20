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
$rcsdk = new RC\RCSDK(new RC\core\cache\MemoryCache(), 'appKey', 'appSecret', 'server (optional)');
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

## Performing API call

```php
$ajax = $rcsdk->getPlatform()->apiCall(new Request('GET', '/account/~/extension/~'));

print_r($call->getResponse()->getData());
```