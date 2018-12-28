<?php

use Fei\ApiClient\Transport\BasicTransport;
use Fei\Service\Payment\Client\Payer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$payer = new Payer([Payer::OPTION_BASEURL => 'http://payment.localhost:8085']);
$payer->setTransport(new BasicTransport());

$payment = $payer->refund('f73b5c13-361c-476e-8f98-ad5014f24f7d', 100);
var_dump($payment);
