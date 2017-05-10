<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([Payer::OPTION_BASEURL => 'http://127.0.0.1:8030']);
$payer->setTransport(new BasicTransport());

try {
    $payment = $payer->retrieve('81db1f4e-e938-440a-aae0-95b5994db012');

    var_dump($payment);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
