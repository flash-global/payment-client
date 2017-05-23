<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Entity\Payment;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([Payer::OPTION_BASEURL => 'http://translate.dev:8005']);
$payer->setTransport(new BasicTransport());

try {
    $payment = new Payment();
    $payment->setExpirationDate(new \DateTime())
        ->setStatus(Payment::STATUS_AUTHORIZED)
        ->setRequiredPrice(123)
        ->setAuthorizedPayment(Payment::PAYMENT_OGONE)
        ->setCallbackUrl([
            "succeeded" => 'http://127.0.0.1',
            "failed" => 'http://127.0.0.1',
            "cancelled" => 'http://127.0.0.1',
        ]);

    $payment = $payer->request($payment);
    echo $payer->getPaymentLink($payment) . PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
