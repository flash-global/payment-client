<?php
use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([
    Payer::OPTION_BASEURL => 'http://payment.dev:8005',
    Payer::OPTION_HEADER_AUTHORIZATION => 'key'
]);
$payer->setTransport(new BasicTransport());

try {
    $builder = new SearchBuilder();
    $builder->uuid()->equal('8f8ab217-1e6f-4506-b12e-3e02c45b1b7f');

    $payments = $payer->search($builder);

    print_r($payments);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    $previous = $e->getPrevious();
    if ($previous instanceof Guzzle\Http\Exception\ServerErrorResponseException) {
        var_dump($previous->getRequest());
        var_dump($previous->getResponse()->getBody(true));
    }
}
