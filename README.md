# omnipay-pay360

**Pay360 driver for the Omnipay PHP payment processing library**

Omnipay implementation of the Pay360 payment gateway.

[![Build Status](https://travis-ci.org/digitickets/omnipay-pay360.png?branch=master)](https://travis-ci.org/digitickets/omnipay-pay360)
[![Coverage Status](https://coveralls.io/repos/github/digitickets/omnipay-pay360/badge.svg?branch=master)](https://coveralls.io/github/digitickets/omnipay-pay360?branch=master)
[![Latest Stable Version](https://poser.pugx.org/digitickets/omnipay-pay360/version.png)](https://packagist.org/packages/digitickets/omnipay-pay360)
[![Total Downloads](https://poser.pugx.org/digitickets/omnipay-pay360/d/total.png)](https://packagist.org/packages/digitickets/omnipay-pay360)

## Installation

**Important: Driver requires [PHP's Intl extension](http://php.net/manual/en/book.intl.php) and [PHP's SOAP extension](http://php.net/manual/en/book.soap.php) to be installed.**

The Pay360 Omnipay driver is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "digitickets/omnipay-pay360": "~1.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## What's Included

This driver handles transactions being processed by the Simple Interface of Pay360.

## What's Not Included

It does not (currently) handle refunds.

## Basic Usage

For general Omnipay usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

### Purchase

```php
use Omnipay\Omnipay;

// Create the gateway object
$gateway = Omnipay::create('\\DigiTickets\\Pay360\\SimpleInterfaceGateway')->initialize(
    [
        'testMode' => true,
    ]
);

// Create the collection of items that this purchase is for
$itemBag = new \Omnipay\Common\ItemBag(
    [
        new \Omnipay\Common\Item(
            [
                'name' => 'Item A',
                'description' => 'An item for sale',
                'price' => '1.00',
                'quantity' => 1,
            ]
        ),
    ]
);

// Generate a unique ID for your transaction
$transactionId = time();

// These are the parameters needed to make a Pay360 purchase
$config = [
    'subjectId' => '12345678',
    'signatureHmacKeyID' => '123',
    'credentialsIdentifier' => '123456789',
    'routingScpId' => '12345678',
    'routingSiteId' => '1',
    'secretKey' => 'LONG_SECRET_KEY_PROVIDED_BY_THE_GATEWAY==',
    'reference' => 'AA123456789',
    'fundCode' => '1',
    'returnUrl' => 'http://example.com/return.php?transactionId='.$transactionId,
    'cancelUrl' => 'http://example.com/return.php?transactionId='.$transactionId,
];
$purchaseDetails = [
    'transactionId' => $transactionId,
    'description' => 'Test transaction '.$transactionId,
    'amount' => '1.00',
    'currency' => 'GBP',
    'items' => $itemBag,
];

// Send purchase request
$request = $gateway->purchase(array_merge($config, $purchaseDetails));
$response = $request->send();

// Pay360 returns a transaction reference at this stage. You'll need to store 
// this (probably in a database) until the customer returns to your site
// so that you can verify the outcome of the transaction. 
$transactionRef = $response->getTransactionReference();
```

At this stage `$response->isRedirect()` should be `true` and you can call `$response->redirect()` to 
send the customer off to the hosted card form page.

You will then need a script to handle the returning customer. The following is a simple example:

```php
use Omnipay\Omnipay;

// Create the gateway object
$gateway = Omnipay::create('\\DigiTickets\\Pay360\\SimpleInterfaceGateway')->initialize(
    [
        'testMode' => true,
    ]
);

$transactionId = $_GET['transactionId'];

// Retrieve the transactionRef from your database using $transactionId
$transactionRef = $valueFromDatabase; 

$config = [
    'subjectId' => '12345678',
    'signatureHmacKeyID' => '123',
    'credentialsIdentifier' => '123456789',
    'routingScpId' => '12345678',
    'routingSiteId' => '1',
    'secretKey' => 'LONG_SECRET_KEY_PROVIDED_BY_THE_GATEWAY==',
    'reference' => 'AA123456789',
];
$purchaseDetails = [
    'transactionReference' => $transactionRef,
];

// Send complete purchase request
$request = $gateway->completePurchase(array_merge($config, $purchaseDetails));
$response = $request->send();
```

At this stage, you can then check the result of `$response->isSuccessful()` which should 
return `true` if the transaction succeeded. 

If the transaction failed, any errors should be provided in `$response->getMessage()`.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you believe you have found a bug in this driver, please report it using the [GitHub issue tracker](https://github.com/digitickets/omnipay-pay360/issues),
or better yet, fork the library and submit a pull request.
