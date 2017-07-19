<?php
namespace Fei\Service\Payment\Client;

use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;
use Fei\Service\Payment\Entity\Payment;

/**
 * Interface PayerInterface
 * @package Fei\Service\Payment\Client
 */
interface PayerInterface
{
    /**
     * Send a payment request
     *
     * @param Payment $payment
     *
     * @return int
     */
    public function request(Payment $payment);

    /**
     * Update one payment entity
     *
     * @param Payment $payment
     *
     * @return int
     */
    public function update(Payment $payment);

    /**
     * Retrieve one payment entity according to an unique payment id
     *
     * @param int $paymentId
     *
     * @return Payment
     */
    public function retrieve($paymentId);

    /**
     * Search for payments entities according to criteria
     *
     * @param SearchBuilder $search
     *
     * @return array
     */
    public function search(SearchBuilder $search);

    /**
     * Cancel one payment request by the client
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param int $reason
     *
     * @return int
     */
    public function cancel($payment, $reason);

    /**
     * Reject one payment request by the provider
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param int $reason
     *
     * @return int
     */
    public function reject($payment, $reason);

    /**
     * Capture one payment. If provided, capture only the $amount
     *
     * @param mixed $payment can be an int or a Payment entity
     * @param float $amount
     *
     * @return void
     */
    public function capture($payment, $amount);

    /**
     * Get the public link to process one payment
     *
     * @param mixed $payment can be an int (the id of the entity) ; a string (the uuid) or a Payment entity
     *
     * @return string
     */
    public function getPaymentLink($payment);
}
