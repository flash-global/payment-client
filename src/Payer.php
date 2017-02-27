<?php
namespace Fei\Service\Payment\Client;

use Fei\ApiClient\AbstractApiClient;
use Fei\ApiClient\RequestDescriptor;
use Fei\ApiClient\ResponseDescriptor;
use Fei\Service\Payment\Client\Utils\SearchBuilder;
use Fei\Service\Payment\Entity\Payment;
use Fei\Service\Payment\Exception\PaymentException;
use Guzzle\Http\Exception\BadResponseException;

/**
 * Class Payer
 *
 * @package Fei\Service\Payment\Client
 */
class Payer extends AbstractApiClient implements PayerInterface
{
    const API_PAYMENT_PATH_INFO = '/api/payments';

    /**
     * Send a payment request
     *
     * @param Payment $payment
     *
     * @return int
     */
    public function request(Payment $payment)
    {
        // TODO: Implement request() method.
    }

    /**
     * Retrieve one payment entity according to an unique payment id
     *
     * @param int $paymentId
     *
     * @return Payment
     */
    public function retrieve($paymentId)
    {
        // TODO: Implement retrieve() method.
    }

    /**
     * Search for payments entities according to criteria
     *
     * @param SearchBuilder $search
     *
     * @return array
     */
    public function search(SearchBuilder $search)
    {
        // TODO: Implement search() method.
    }

    /**
     * Cancel one payment request by the client
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param int $reason
     *
     * @return void
     */
    public function cancel($payment, $reason)
    {
        // TODO: Implement cancel() method.
    }

    /**
     * Reject one payment request by the provider
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param int $reason
     *
     * @return void
     */
    public function reject($payment, $reason)
    {
        // TODO: Implement reject() method.
    }

    /**
     * Update the amount of a payment
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param float $amount
     *
     * @return void
     */
    public function updateAmount($payment, $amount)
    {
        // TODO: Implement updateAmount() method.
    }

    /**
     * Notify people according to the status of the payment
     *
     * @param mixed $payment can be an int or a Payment entity
     *
     * @return void
     */
    public function notify($payment)
    {
        // TODO: Implement notify() method.
    }

    /**
     * Capture one payment. If provided, capture only the $amount
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param float $amount
     *
     * @return void
     */
    public function capture($payment, $amount)
    {
        // TODO: Implement capture() method.
    }

    public function send(RequestDescriptor $request, $flags = 0)
    {
        try {
            $response = parent::send($request, $flags);

            if ($response instanceof ResponseDescriptor) {
                //$body = \json_decode($response->getBody(), true);

                return $response;
            }
        } catch (\Exception $e) {
            $previous = $e->getPrevious();

            if ($previous instanceof BadResponseException) {
                $data = \json_decode($previous->getResponse()->getBody(true), true);
                if (isset($data['code']) && isset($data['error'])) {
                    throw new PaymentException($data['error'], $data['code'], $e);
                }
            }

            throw new PaymentException($e->getMessage(), $e->getCode(), $e);
        }

        return null;
    }
}
