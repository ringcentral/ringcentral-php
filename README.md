# RingCentral SDK for PHP

[![Build Status](https://img.shields.io/travis/ringcentral/ringcentral-php/master.svg)](https://travis-ci.org/ringcentral/ringcentral-php)
[![Coverage Status](https://coveralls.io/repos/ringcentral/ringcentral-php/badge.svg?branch=master&service=github)](https://coveralls.io/github/ringcentral/ringcentral-php?branch=master)

# Requirements

- PHP 5.3.29+
- CURL extension
- MCrypt extension

# Installation

Please choose one of the following installation options:

## With [Composer](http://getcomposer.org) **(recommended)**

The installation of composer is local by default. We suggest that you install it at the top level of your application's
directory structure.

1. Install composer:
    
    ```sh
    $ curl -sS https://getcomposer.org/installer | php
    ```
    
    More info about installation on [Linux / Unix / OSX](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
    and [Windows](https://getcomposer.org/doc/00-intro.md#installation-windows).

3. Add the following line to your `composer.json` or create the one from scratch:
  
    ```json
    {
        "minimum-stability": "dev"
    }
    ```
  
2. Run the Composer command to install the latest version of SDK:
  
    ```sh
    $ php composer.phar require ringcentral/ringcentral-php
    ```

3. Require Composer's autoloader in your PHP script (assuming it is in the same directory where you installed Composer):
    
    ```php
    require('vendor/autoload.php');
    ```

## PHAR with bundled dependencies

**This is not recommended! Use [Composer](http://getcomposer.org) as modern way of working with PHP packages.**

1. Download [PHAR file](https://github.com/ringcentral/ringcentral-php/releases/latest)

2. Require files:
  
    ```php
    require('path-to-sdk/ringcentral.phar');
    ```

Please keep in mind that bundled dependencies may interfere with your other dependencies.
  
# Basic Usage

## Initialization

```php
$sdk = new RingCentral\SDK\SDK('appKey', 'appSecret', RingCentral\SDK\SDK::SERVER_SANDBOX);
```

You also may supply custom AppName and AppVersion parameters with your application codename and version. These parameters
are optional but they will help a lot to identify your application in API logs and speed up any potential troubleshooting.
Allowed characters for AppName and AppVersion are: letters, digits, hyphen, dot and underscore.

```php
$sdk = new RingCentral\SDK\SDK('appKey', 'appSecret', RingCentral\SDK\SDK::SERVER_SANDBOX, 'MyApp', '1.0.0');
```

For production use `RingCentral\SDK\SDK::SERVER_PRODUCTION` constant. Or type in the server URL by hand.

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
$transaction = $sdk->getPlatform()->get('/account/~/extension/~');
$transaction = $sdk->getPlatform()->post('/account/~/extension/~');
$transaction = $sdk->getPlatform()->put('/account/~/extension/~');
$transaction = $sdk->getPlatform()->delete('/account/~/extension/~');

print_r($transaction->getJson()); // stdClass will be returned or exception if Content-Type is not JSON
print_r($transaction->getRequest()); // PSR-7's RequestInterface compatible instance used to perform HTTP request 
print_r($transaction->getResponse()); // PSR-7's ResponseInterface compatible instance used as HTTP response 
```

### Multipart response

Loading of multiple comma-separated IDs will result in HTTP 207 with `Content-Type: multipart/mixed`. This response will
be parsed into multiple sub-responses:

```php
$presences = $sdk->getPlatform()
                 ->get('/account/~/extension/id1,id2/presence')
                 ->getMultipart();

print 'Presence loaded ' .
      $presences[0]->getJson()->presenceStatus . ', ' .
      $presences[1]->getJson()->presenceStatus . PHP_EOL;
```

### Send SMS - Make POST request

```php
$transaction = $sdk->getPlatform()->post('/account/~/extension/~/sms', null, array(
    'from' => array('phoneNumber' => 'your-ringcentral-sms-number'),
    'to'   => array(
        array('phoneNumber' => 'mobile-number'),
    ),
    'text' => 'Test from PHP',
));
```

### Get Platform error message

```php
use RingCentral\SDK\Http\HttpException;

try {

    $platform->get('/account/~/whatever');

} catch (HttpException $e) {

    // Getting error messages using PHP native interface
    print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;

    // In order to get Request and Response used to perform transaction:
    $transaction = $e->getTransaction();
    print_r($transaction->getRequest()); 
    print_r($transaction->getResponse());
    
    // Another way to get message, but keep in mind, that there could be no response if request has failed completely
    print '  Message: ' . $e->getTransaction->getResponse()->getError() . PHP_EOL;
    
}
```

# Subscriptions

```php
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Subscription;

$subscription = $sdk->getSubscription()
                     ->addEvents(array('/restapi/v1.0/account/~/extension/~/presence'))
                     ->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) {
                
                         print_r($e->getPayload());
                
                     });
                     
$transaction = $subscription->register();
```

Please keep in mind that due to limitations of PUBNUB library, which is synchronous, subscriptions may expire and must
be re-created manually.

# Multipart Requests

SDK provides a helper to make sending of faxes easier.

```php
$request = $rcsdk->getMultipartBuilder()
                 ->setBody(array(
                     'to'         => array(
                         array('phoneNumber' => '16501112233'),
                     ),
                     'faxResolution' => 'High',
                 ))
                 ->addAttachment('Plain Text', 'file.txt')
                 ->addAttachment(fopen('path/to/file', 'r'))
                 ->getRequest('/account/~/extension/~/fax'); // also has optional $method argument

$response = $platform->apiCall($request);
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
    'mobileNumber' => '16501112233', // your own mobile number to which script will send sms
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