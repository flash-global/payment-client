<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Entity\Payment;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([
    Payer::OPTION_BASEURL => 'http://payment.dev:8005',
    Payer::OPTION_HEADER_AUTHORIZATION => 'key'
]);
$payer->setTransport(new BasicTransport());

try {
    $payment = new Payment();
    $payment->setExpirationDate(new \DateTime())
        ->setStatus(Payment::STATUS_PENDING)
        ->setRequiredPrice(456)
        ->setVat(0.5)
        ->setAuthorizedPayment(Payment::PAYMENT_PAYPAL|Payment::PAYMENT_STRIPE|Payment::PAYMENT_OGONE)
        ->setCallbackUrl([
            "succeeded" => 'http://127.0.0.1/succeeded',
            "failed" => 'http://127.0.0.1/failed',
            "cancelled" => 'http://127.0.0.1/cancelled',
            "saved" => 'http://127.0.0.1/cancelled',
        ]);

    $payer->request($payment);

    $payment = $payer->retrieve($payment->getUuid());

    var_dump($payment);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
