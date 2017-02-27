<?php
namespace Tests\Fei\Service\Payment\Client;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Fei\ApiClient\RequestDescriptor;
use Fei\ApiClient\ResponseDescriptor;
use Fei\Service\Payment\Client\Exception\PaymentException;
use Fei\Service\Payment\Client\Payer;
use Guzzle\Http\Exception\BadResponseException;

/**
 * Class PayerTest
 *
 * @package Tests\Fei\Service\Payment\Client
 */
class PayerTest extends Unit
{
    public function testRequest()
    {
    }

    public function testRetrieve()
    {
    }

    public function testSearch()
    {
    }

    public function testCancel()
    {
    }

    public function testReject()
    {
    }

    public function testUpdateAmount()
    {
    }

    public function testNotify()
    {
    }

    public function testCapture()
    {
    }

    public function testSendWhenExceptionOfTypeBadResponseIsThrown()
    {
        $responseMock = $this->getMockBuilder(ResponseDescriptor::class)->setMethods(['getBody'])->getMock();
        $responseMock->expects($this->once())->method('getBody')->willReturn(json_encode([
            'error' => 'Bad response exception',
            'code' => 500
        ]));

        $previous = $this->getMockBuilder(BadResponseException::class)->setMethods(['getResponse'])->getMock();
        $previous->expects($this->once())->method('getResponse')->willReturn($responseMock);

        $payer = Stub::make(Payer::class, [
            'callSendInParent' => Stub::once(function () use ($previous) {
                throw new \Exception('Exception thrown', 0, $previous);
            })
        ]);

        $request = new RequestDescriptor();

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Bad response exception');
        $this->expectExceptionCode(500);

        $payer->send($request);
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

    protected function invokeNonPublicMethod($object, $name, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
