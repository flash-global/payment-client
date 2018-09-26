<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([
    Payer::OPTION_BASEURL => 'http://payment.dev:8005',
    Payer::OPTION_HEADER_AUTHORIZATION => 'key'
]);
$payer->setTransport(new BasicTransport());

try {
    $payment = $payer->retrieve(5);

    $payer->capture($payment, 100);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
