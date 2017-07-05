<?php
namespace Fei\Service\Payment\Client;

use Fei\ApiClient\AbstractApiClient;
use Fei\ApiClient\RequestDescriptor;
use Fei\ApiClient\ResponseDescriptor;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;
use Fei\Service\Payment\Entity\Payment;
use Fei\Service\Payment\Client\Exception\PaymentException;
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
        $this->ensureTransportIsSet();

        $request = (new RequestDescriptor())
            ->setMethod('POST')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO));

        $request->setBodyParams(['payment' => \json_encode($payment->toArray())]);

        $response = $this->send($request);

        $paymentId = \json_decode($response->getBody(), true);

        $payment->setId($paymentId);

        return $paymentId;
    }

    /**
     * Retrieve one payment entity according to an unique payment id
     *
     * @param string|int $paymentId the id or uuid of the payment
     *
     * @return Payment
     */
    public function retrieve($paymentId)
    {
        $this->ensureTransportIsSet();
        $name = (is_string($paymentId) && strlen($paymentId) === 36) ? 'uuid' : 'id';

        $request = (new RequestDescriptor())
            ->setMethod('GET')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO . '?' . $name . '=' . urlencode($paymentId)));

        /** @var Payment $payment */
        $payment = $this->fetch($request);

        return $payment;
    }

    /**
     * Search for payments entities according to criteria
     *
     * @param SearchBuilder $search
     *
     * @return Payment[]
     */
    public function search(SearchBuilder $search)
    {
        $this->ensureTransportIsSet();

        $request = (new RequestDescriptor())
            ->setMethod('GET')
            ->setUrl($this->buildUrl(
                self::API_PAYMENT_PATH_INFO . '?criteria=' . urlencode(json_encode($search->getParams()))
            ));

        $response = $this->send($request);
        $payments = \json_decode($response->getBody(), true);
        $payments = (isset($payments['payments'])) ? $payments['payments'] : [];

        foreach ($payments as &$payment) {
            $payment = new Payment($payment);
        }

        return $payments;
    }

    /**
     * Cancel one payment request by the client
     *
     * @param int|Payment $payment can be an int or a Payment entity
     * @param string      $reason
     *
     * @return int
     */
    public function cancel($payment, $reason)
    {
        return $this->updateStatusWithReason($payment, Payment::STATUS_CANCELLED, $reason);
    }

    /**
     * Reject one payment request by the provider
     *
     * @param int|Payment $payment can be an int or a Payment entity
     * @param string      $reason
     *
     * @return int
     */
    public function reject($payment, $reason)
    {
        return $this->updateStatusWithReason($payment, Payment::STATUS_REJECTED, $reason);
    }

    /**
     * Update one payment with a status that needs a reason
     *
     * @param int|Payment $payment can be an int or a Payment entity
     * @param int         $status
     * @param string      $reason
     *
     * @return int
     */
    protected function updateStatusWithReason($payment, $status, $reason)
    {
        if (is_int($payment)) {
            return $this->updateStatusWithReason(
                $this->retrieve($payment),
                $status,
                $reason
            );
        }

        // bad request, we need an integer
        if (!$payment instanceof Payment) {
            throw new PaymentException(
                '`payment` parameter has to be either an integer or an instance of `Payment`!',
                400
            );
        }

        $this->ensureTransportIsSet();

        $request = (new RequestDescriptor())
            ->setMethod('PUT')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO));

        $payment->setStatus($status);
        $payment->setCancellationReason($reason);

        $request->setBodyParams(['payment' => \json_encode($payment->toArray())]);

        $response = $this->send($request);

        $paymentId = \json_decode($response->getBody(), true);

        return $paymentId;
    }

    /**
     * Capture one payment. If provided, capture only the $amount
     *
     * @param int|Payment $payment can be an int or a Payment entity
     * @param float $amount
     *
     * @return int
     */
    public function capture($payment, $amount)
    {
        $this->ensureTransportIsSet();

        $request = (new RequestDescriptor())
            ->setMethod('PATCH')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO));

        $request->setBodyParams([
            'payment' => (is_int($payment)) ? $payment : \json_encode($payment->toArray()),
            'amount' => $amount
        ]);

        $response = $this->send($request);

        if ($payment instanceof Payment) {
            $payment->setCapturedPrice($amount);
        }

        $paymentId = \json_decode($response->getBody(), true);

        return $paymentId;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentLink($payment)
    {
        $this->ensureTransportIsSet();

        if ($payment instanceof Payment) {
            $payment = $payment->getUuid();
        }

        $request = (new RequestDescriptor())
            ->setMethod('GET')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO . '/link?id=' . urlencode($payment)));

        /** @var Payment $payment */
        $link = $this->send($request);
        $link = \json_decode($link->getBody(), true);


        return $link;
    }

    /**
     * @param Payment $payment
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public function sendPaymentLinkByMail(Payment $payment, string $from, string $to)
    {
        $this->ensureTransportIsSet();

        $request = (new RequestDescriptor())
            ->setMethod('POST')
            ->setUrl($this->buildUrl(self::API_PAYMENT_PATH_INFO . '/mail-link'));

        $request->setBodyParams([
            'payment' => $payment->getId(),
            'from' => $from,
            'to' => $to
        ]);

        return $this->send($request);
    }

    /**
     * @inheritdoc
     */
    public function send(RequestDescriptor $request, $flags = 0)
    {
        try {
            $response = $this->callSendInParent($request, $flags);

            if ($response instanceof ResponseDescriptor) {
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

    /**
     * Call the send method of the parent (method that can be mocked)
     *
     * @param RequestDescriptor $request
     * @param int $flags
     *
     * @return bool|ResponseDescriptor
     */
    protected function callSendInParent(RequestDescriptor $request, $flags = 0)
    {
        return parent::send($request, $flags);
    }

    /**
     * Check if a transport has been set. Otherwise, throw an exception
     *
     * @throws PaymentException
     */
    protected function ensureTransportIsSet()
    {
        if (!$this->getTransport()) {
            throw new PaymentException('No transport has been set!');
        }
    }
}
