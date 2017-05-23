# Payment Client


You can use this client to consume the Payment service.

With this client you can use two kind of transports to send the requests :

* Asynchronous transport implemented by `BeanstalkProxyTransport`
* Synchronous transport implemented by `BasicTransport`

`BeanstalkProxyTransport` delegates the API consumption to workers by sending payments entities to a Beanstalkd queue.

`BasicTransport` uses the classic HTTP layer to send payments synchronously.

You can find examples of how to use payment client methods in the examples folder.

# Installation

Payer client needs at least PHP 5.5 to work properly.

Add this requirement to your composer.json: "fei/payment-client": : "^1.0"

Or execute composer.phar require fei/payment-client in your terminal.

If you want use the asynchronous functionality of the Payer client (and we know you want), you need an instance of Beanstalkd which running properly and an instance of api-client-worker.php which will consume the Beanstalk's pipe and forward messages payload to the Payment API:

```
Payer Client -> Beanstalkd -> api-client-worker.php -> Payment API server
```

## Beanstalkd configuration

Running Beanstalkd is very simple. However, you must pay attention to the z option which set the maximum job (or message) size in bytes.

## Run `api-client-worker.php`

You could see below an example of running `api-client-worker.php`:

```bash
php /path/to/payment-client/vendor/bin/api-client-worker.php --host 127.0.0.1 --port 11300 --delay 3
```

| Options | Shortcut | Description                                   | Default     |
|---------|----------|-----------------------------------------------|-------------|
| host    | `-h`     | The host of Beanstalkd instance               | `localhost` |
| port    | `-p`     | The port which Beanstalkd instance listening  | `11300`     |
| delay   | `-d`     | The delay between two treatment of the worker | 3 seconds   |
| verbose | `-v`     | Print verbose information                     | -           |


You can control the api-client-worker.php process by using Supervisor.

# Entities and classes

## Payment entity

In addition to traditional `id` and `createdAt` fields, Payment entity has eleven important properties:

| Properties    			| Type              |
|---------------------|-------------------|
| id            			| `integer`         |
| uuid          			| `string`          |
| createdAt     			| `datetime`        |
| payedAt     				| `datetime`        |
| expirationDate 			| `datetime`        |
| status 							| `integer`         |
| cancellationReason	| `string`         	|
| requiredPrice       | `float`         	|
| capturedPrice       | `float`         	|
| authorizedPayment 	| `integer`         |
| selectedPayment 		| `integer`         |
| contexts						| `ArrayCollection` |
| callbackUrl					| `ArrayCollection` |

* `uuid` is a string representing a unique identifier of the payment entity
* `createdAt' represent the creation date
* `payedAt' represent the date when the payment has been made
* `expirationDate' represent the date when the payment expires
* `status` indicate in which status the payment currently is
* `cancellationReason` is a string representing the reason of the cancellation of the payment
* `requiredPrice` is a float representing the price required
* `capturedPrice` is a float representing the price captured
* `authorizedPayment` is an int that represent the list of the payment authorised (used like binary flags)
* `selectedPayment` is an integer representing the payment method that has been chosen
* `contexts` is an ArrayCollection of all the contexts for the entity
* `callbackUrl` is an array of callbacks url that will be used is some events in the application (when the payment is saved for example). Here are the possible value and purpose of the callback url:
	* `succeeded` : the url that will be called when an payment authorization successes
	* `failed` : the url that will be called when an payment authorization failed
	* `cancelled` : the url that will be called when an payment is cancelled 

## Context entity

In addition to traditional `id` field, Context entity has three important properties:

| Properties  | Type        |
|-------------|-------------|
| id 					| `integer`   |
| key     		| `string`    |
| value 			| `string`    |
| payment 		| `Payment` 	|

* `key` is a string representing the key of the context
* `value` is a string representing the value attach to this context
* `payment` is a Payment entity representing the Payment related to this context

# Basic usage

In order to consume `Payer` methods, you have to define a new `Payer` instance and set the right transport (Asynchronously or Synchronously).

```php
<?php

use Fei\Service\Payment\Client\Payer;
use Fei\ApiClient\Transport\BasicTransport;
use Fei\ApiClient\Transport\BeanstalkProxyTransport;
use Pheanstalk\Pheanstalk;

$payer = new Payer([Payer::OPTION_BASEURL => 'https://payment.api']); // Put your payment API base URL here
$payer->setTransport(new BasicTransport());

$proxy = new BeanstalkProxyTransport();
$proxy->setPheanstalk(new Pheanstalk('127.0.0.1'));

$payer->setAsyncTransport($proxy);

// Use the payer client methods...
```

`Payer` client instance will first attempt to transfer the messages with Beanstalkd, if the process fail then the client will try to send Payment payload directly to the right API endpoint.

There are several methods in `Payer` class, all listed in the following table:

| Method         | Parameters                             | Return   |
|----------------|----------------------------------------|----------|
| request        | `Payment $payment`                     | `integer`|
| retrieve       | `int $paymentId`            						| `Payment`|
| search         | `SearchBuilder $search`                | `array`  |
| cancel         | `Payment|int $payment, int $reason`    | `intger` |
| reject         | `Payment|int $payment, int $reason`    | `intger` |
| capture        | `Payment|int $payment, float $reason`  | `intger` |
| getPaymentLink | `Payment|int|string $payment`  				| `string` |

## Client option

Only one option is available which can be passed either by the constructor or by calling the `setOptions` method `Payer::setOptions(array $options)`:

| Option         | Description                                    | Type   | Possible Values                                | Default |
|----------------|------------------------------------------------|--------|------------------------------------------------|---------|
| OPTION_BASEURL | This is the server to which send the requests. | string | Any URL, including protocol but excluding path | -       |

**Note**: All the examples below are also available in the examples directory.

## Request

You can create new Payment by using the `request()` method of the `Payer` client:

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Entity\Payment;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$payment = new Payment();
$payment->setExpirationDate(new \DateTime())
		->setStatus(Payment::STATUS_PENDING)
		->setRequiredPrice(456)
		->setAuthorizedPayment(Payment::PAYMENT_PAYPAL)
		->setCallbackUrl([
				"succeeded" => 'http://url-succeeded.fr',
				"failed" => 'http://url-failed.fr',
				"saved" => 'http://url-saved.fr',
				"cancelled" => 'http://url-cancelled.fr',
		]);

$payment = $payer->request($payment);
```

## Request

You can retrieve one Payment by using the `retrieve()` method of the `Payer` client that takes one parameter: the `id` the the payment entity OR the `uuid` of the payment.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$payment = $payer->retrieve(1); // retrieve by it's integer id
$payment = $payer->retrieve('81db1f4e-e938-440a-aae0-95b5994db015'); // retrieve by it's string uuid
```

## Search

You can search for payments by using the `search()` method of the `Payer` client that takes a `SearchBuilder` instance.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$builder = new SearchBuilder();
$builder->uuid()->equal('81db1f4e-e938-440a-aae0-95b5994db012');
$payments = $payer->search($builder);

echo '<pre>';
print_r($payments);
echo '</pre>';
```

## Cancel

You can cancel one payment by using the `cancel()` method of the `Payer` client that takes a `Payment` instance (or id) and a `string` for the reason of the cancellation.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$paymentId = $payer->cancel(1, 'Cancel by an administrator');
```

## Reject

You can reject one payment by using the `reject()` method of the `Payer` client that takes a `Payment` instance (or id) and a `string` for the reason of the cancellation.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$paymentId = $payer->reject(1, 'Rejected by an administrator');
```

## Capture

You can capture one payment by using the `capture()` method of the `Payer` client that takes a `Payment` instance (or id) and a `float` for the amount to capture.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

$paymentId = $payer->capture(1, 10.36);
```

## GetPaymentLink

You can get the public payment link to process a payment by using the `getPaymentLink()` method of the `Payer` client that takes either a `Payment` instance or an id or an uuid.

**Example**

```php
<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.dev']);
$payer->setTransport(new BasicTransport());

try {
    echo $payer->getPaymentLink(23);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
```
