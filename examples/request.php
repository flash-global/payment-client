<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Entity\Payment;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([Payer::OPTION_BASEURL => 'http://127.0.0.1:8030']);
$payer->setTransport(new BasicTransport());

try {
    $payment = new Payment();
    $payment->setExpirationDate(new \DateTime())
        ->setStatus(Payment::STATUS_PENDING)
        ->setRequiredPrice(456)
        ->setAuthorizedPayment(Payment::PAYMENT_PAYPAL)
        ->setCallbackUrl([
            "succeeded" => 'http://127.0.0.1',
            "failed" => 'http://127.0.0.1',
            "saved" => 'http://127.0.0.1',
            "cancelled" => 'http://127.0.0.1',
        ]);

    $payment = $payer->request($payment);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
