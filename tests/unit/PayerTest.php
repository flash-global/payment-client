<?php
namespace Tests\Fei\Service\Payment\Client;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Fei\ApiClient\RequestDescriptor;
use Fei\ApiClient\ResponseDescriptor;
use Fei\ApiClient\Transport\SyncTransportInterface;
use Fei\Service\Payment\Client\Exception\PaymentException;
use Fei\Service\Payment\Client\Payer;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;
use Fei\Service\Payment\Entity\Payment;

/**
 * Class PayerTest
 *
 * @package Tests\Fei\Service\Payment\Client
 */
class PayerTest extends Unit
{
    public function testRequest()
    {
        $payer = new Payer();

        $payment = $this->getPaymentEntity();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->willReturn((new ResponseDescriptor())->setBody(1));

        $payer->setTransport($transport);

        $results = $payer->request($payment);

        $this->assertEquals(1, $results);
    }

    public function testRequestAcceptApiKey()
    {
        $payer = new Payer();
        $payment = $this->getPaymentEntity();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(1)
        );
        $payer->setTransport($transport);
        $payer->setApiKey('toto');

        $results = $payer->request($payment);

        $this->assertEquals(1, $results);
    }

    public function testUpdate()
    {
        $payer = new Payer();

        $payment = $this->getPaymentEntity();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(1)
        );
        $payer->setTransport($transport);

        $results = $payer->update($payment);

        $this->assertEquals(1, $results);
    }

    public function testRetrieve()
    {
        $payment = new Payment([
            'id' => 1,
            'status' => Payment::STATUS_ERRORED
        ]);
        $payment->setCreatedAt($payment->getCreatedAt()->format('c'));

        $payer = new Payer();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(json_encode([
                "data" => [
                    "id" => $payment->getId(),
                    "status" => $payment->getStatus(),
                    'createdAt' => $payment->getCreatedAt()->format('c'),
                    'uuid' => $payment->getUuid()
                ],
                "meta" => [
                    "entity" => "Fei\\Service\\Payment\\Entity\\Payment"
                ]
            ]))
        );
        $payer->setTransport($transport);

        $results = $payer->retrieve(1);

        $this->assertEquals($payment, $results);
    }

    public function testRetrieveNoTransportSet()
    {
        $payer = new Payer();

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('No transport has been set!');

        $payer->retrieve(1);
    }

    public function testSearch()
    {
        $payment = new Payment([
            'id' => 1,
            'status' => Payment::STATUS_ERRORED
        ]);
        $payment->setCreatedAt($payment->getCreatedAt()->format('c'));

        $payer = new Payer();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(json_encode([
                'payments' => [ $payment->toArray() ]
            ]))
        );
        $payer->setTransport($transport);

        $results = $payer->search(new SearchBuilder());

        $this->assertEquals([$payment], $results);
    }

    public function testCancel()
    {
        $payer = Stub::make(Payer::class, [
            'updateStatusWithReason' => Stub::once(function () {
                return 1;
            })
        ]);

        $results = $payer->cancel(1, 'fake-reason');

        $this->assertEquals(1, $results);
    }

    public function testReject()
    {
        $payer = Stub::make(Payer::class, [
            'updateStatusWithReason' => Stub::once(function () {
                return 1;
            })
        ]);

        $results = $payer->reject(1, 'fake-reason');

        $this->assertEquals(1, $results);
    }

    public function testSendWhenExceptionIsThrown()
    {
        $payer = Stub::make(Payer::class, [
            'callSendInParent' => Stub::once(function () {
                throw new \Exception('Exception thrown');
            })
        ]);

        $request = new RequestDescriptor();

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Exception thrown');

        $payer->send($request);
    }

    public function testSendWhenResponseDescriptorIsReturned()
    {
        $respDescriptorMock = $this->getMockBuilder(ResponseDescriptor::class)->getMock();

        $payer = Stub::make(Payer::class, [
            'callSendInParent' => Stub::consecutive($respDescriptorMock, 'fake-result')
        ]);

        $request = new RequestDescriptor();

        $results = $payer->send($request);
        $this->assertEquals($respDescriptorMock, $results);

        $results = $payer->send($request);
        $this->assertNull($results);
    }

    public function testCapture()
    {
        $payment = new Payment([
            'id' => 1,
            'status' => Payment::STATUS_ERRORED,
            'requiredPrice' => 15
        ]);
        $payment->setCreatedAt($payment->getCreatedAt()->format('c'));

        $payer = new Payer();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(json_encode(1))
        );
        $payer->setTransport($transport);

        $results = $payer->capture($payment, 8);

        $this->assertEquals(1, $results);
    }

    public function testGetPaymentLink()
    {
        $payment = new Payment([
            'id' => 1,
            'status' => Payment::STATUS_ERRORED,
            'requiredPrice' => 15
        ]);
        $payment->setCreatedAt($payment->getCreatedAt()->format('c'));

        $payer = new Payer();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send')->willReturn(
            (new ResponseDescriptor())->setBody(json_encode('http://fake-url'))
        );
        $payer->setTransport($transport);

        $results = $payer->getPaymentLink($payment, 8);

        $this->assertEquals('http://fake-url', $results);
    }

    public function testSendPaymentLinkByMail()
    {
        $from = 'from';
        $to = 'to';

        $payment = new Payment([
            'id' => 1,
            'status' => Payment::STATUS_ERRORED,
            'requiredPrice' => 15
        ]);

        $payer = Stub::make(Payer::class, [
            'send' => true,
            'getTransport' => true,
        ]);

        $request = (new RequestDescriptor())
            ->setMethod('POST')
            ->setUrl($payer->buildUrl(Payer::API_PAYMENT_PATH_INFO . '/mail-link'));

        $request->setBodyParams([
            'payment' => 1,
            'from' => $from,
            'to' => $to,
        ]);

        $payer->expects($this->once())->method('send')->with($request);

        $payer->sendPaymentLinkByMail($payment, $from, $to);
    }

    public function testRefund()
    {
        $payment = new Payment([
            'id' => 1,
            'uuid' => 'test',
            'status' => Payment::STATUS_SETTLED,
            'requiredPrice' => 15
        ]);

        $payer = new Payer();

        $request = (new RequestDescriptor())
            ->setMethod('POST')
            ->setUrl($payer->buildUrl(Payer::API_REFUND_PATH_INFO . '/test'))
            ->setRawData(json_encode(['amount' => 8 * 100]));

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->with($request, 0)
            ->willReturn((new ResponseDescriptor())->setBody(json_encode([
                'meta' => [
                    'entity' => Payment::class
                ],
                'data' => (new Payment())->toArray()
            ])));

        $payer->setTransport($transport);

        $payer->refund($payment, 8);
    }

    protected function getPaymentEntity()
    {
        $payment = new Payment();

        $payment->setCallbackUrl(['http://fake-url.fr'])
            ->setAuthorizedPayment(Payment::PAYMENT_PAYPAL)
            ->setCallbackUrlEvent('succeeded', 'http://fake-url.fr/succeeded')
            ->setExpirationDate(new \DateTime())
            ->setRequiredPrice(10.0)
            ->setId(1)
            ->setStatus(Payment::STATUS_PENDING)
            ->setUuid('fake-uuid');

        return $payment;
    }

    protected function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
