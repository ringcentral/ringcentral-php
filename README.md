# RingCentral SDK for PHP

[![Build Status](https://img.shields.io/travis/ringcentral/ringcentral-php/master.svg)](https://travis-ci.org/ringcentral/ringcentral-php)

# Installation

## With [Composer](http://getcomposer.org) *(recommended)*
  
  1. Install composer:
    
    ```sh
    $ curl -sS https://getcomposer.org/installer | php
    ```
  
  2. Run the Composer command to install the latest version of SDK:
  
    ```sh
    $ composer require ringcentral/ringcentral-php
    ```

  3. Require Composer's autoloader:
    
    ```php
    require('vendor/autoload.php');
   ```

## Without Composer

  1. Download [PHAR file](https://github.com/ringcentral/ringcentral-php/blob/master/dist/ringcentral.phar)
  
  2. Follow [PUBNUB installation instructions](https://github.com/pubnub/php#php--53-without-composer)
  
  3. Follow [PhpSecLib installation instructions](https://github.com/phpseclib/phpseclib)
  
  4. Require files:
  
    ```php
    // PUBNUB and PHPSECLIB should be added before
    require('path-to-sdk/ringcentral.phar');
    ```

## Without Composer and PHAR
    
  1. Clone or download [ZIP file](https://github.com/ringcentral/ringcentral-php/archive/master.zip)

  2. Follow [PUBNUB installation instructions](https://github.com/pubnub/php#php--53-without-composer)
  
  3. Follow [PhpSecLib installation instructions](https://github.com/phpseclib/phpseclib)
  
  4. Add autoloaders:
  
    ```php
    // PUBNUB and PHPSECLIB should be added before
    require('path-to-sdk/autoload.php');
    ```
    
# Basic Usage

## Initialization

```php
$sdk = new RingCentral\SDK('appKey', 'appSecret', 'https://platform.devtest.ringcentral.com');
```

## Authentication

Check authentication status:

```php
$sdk->getPlatform()->isAuthorized(); // throws exception if not authorized after automatic refresh
```

Authenticate user:

```php
$sdk->getPlatform()->authorize('username', 'extension (or leave blank)', 'password', true); // change true to false to not remember user
```

### Authentication lifecycle

Platform class performs token refresh procedure if needed. You can save authentication between requests in CGI mode:

```js
// when application is going to be stopped
file_put_contents($file, json_encode($platform->getAuthData(), JSON_PRETTY_PRINT));

// and then next time during application bootstrap before any authentication checks:
$sdk->getPlatform()->setAuthData(json_decode(file_get_contents($file));
```

**Important!** You have to manually maintain synchronization of SDK's between requests if you share authentication.
When two simultaneous requests will perform refresh, only one will succeed. One of the solutions would be to have
semaphor and pause other pending requests while one of them is performing refresh.

## Performing API call

```php
$response = $sdk->getPlatform()->get('/account/~/extension/~');
$response = $sdk->getPlatform()->post('/account/~/extension/~');
$response = $sdk->getPlatform()->put('/account/~/extension/~');
$response = $sdk->getPlatform()->delete('/account/~/extension/~');

print_r($response->getJson()); // stdClass will be returned or exception if Content-Type is not JSON
```

### Multipart response

Loading of multiple comma-separated IDs will result in HTTP 207 with `Content-Type: multipart/mixed`. This response will
be parsed into multiple sub-responses:

```php
$presences = $sdk->getPlatform()
                 ->get('/account/~/extension/id1,id2/presence')
                 ->getResponses();

print 'Presence loaded ' .
      $presences[0]->getJson()->presenceStatus . ', ' .
      $presences[1]->getJson()->presenceStatus . PHP_EOL;
```

### Send SMS - Make POST request

```php

$response = $sdk->getPlatform()->post('/account/~/extension/~/sms', null, array(
    'from' => array('phoneNumber' => 'your-ringcentral-sms-number'),
    'to'   => array(
        array('phoneNumber' => 'mobile-number'),
    ),
    'text' => 'Test from PHP',
));
```

### Get Platform error message

```php
try {

    $platform->get('/account/~/whatever');

} catch (RingCentral\http\HttpException $e) {

    print 'Expected HTTP Error: ' . $e->getResponse()->getError() . PHP_EOL;

}
```

# Subscriptions

```php
$s = ;

$sdk->getSubscription()
    ->addEvents(array('/restapi/v1.0/account/~/extension/~/presence'))
    ->on(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) {

        print_r($e->getPayload());

    })
    ->register();
```

# How to demo?

Clone the repo and create a file `demo/_credentials.php`:

```php
return array(
    'username'     => '18881112233', // your RingCentral account phone number
    'extension'    => null, // or number
    'password'     => 'yourPassword',
    'appKey'       => 'yourAppKey',
    'appSecret'    => 'yourAppSecret',
    'server'       => 'https://platform.devtest.ringcentral.com', // for production - https://platform.ringcentral.com
    'smsNumber'    => '18882223344', // any of SMS-enabled numbers on your RingCentral account
    'mobileNumber' => '16502746490', // your own mobile number to which script will send sms
);
```

Then execute:

```sh
$ php index.php
```

Should output:

```
Auth exception: Refresh token has expired
Authorized
Refreshing
Refreshed
Users loaded 10
Presence loaded Something New - Available, Something New - Available
Expected HTTP Error: Not Found (from backend)
SMS Phone Number: 12223334455
Sent SMS https://platform.ringcentral.com/restapi/v1.0/account/111/extension/222/message-store/333
Subscribing
```

After that script will wait for any presence notification. Make a call to your account or make outbound call from your
account. When you will make a call, script will print notification and exit.

Please take a look in `demo` folder to see all the demos.