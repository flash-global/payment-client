<?php

use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Entity\Payment;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.localhost:8085']);
$payer->setTransport(new BasicTransport());

try {
    $payment = new Payment();
    $payment->setExpirationDate(new \DateTime('+10 days'))
        ->setStatus(Payment::STATUS_PENDING)
        ->setRequiredPrice(456)
        ->setVat(0.2)
        ->setAuthorizedPayment(Payment::PAYMENT_PAYZEN)
        ->setCallbackUrl([
            "succeeded" => 'http://127.0.0.1/succeeded',
            "failed" => 'http://127.0.0.1/failed',
            "cancelled" => 'http://127.0.0.1/cancelled',
            "saved" => 'http://127.0.0.1:7700/saved',
        ]);

    $payer->request($payment);
    var_dump($payment->getId());
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
