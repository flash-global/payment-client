<?php
namespace Tests\Fei\Service\Payment\Client\Exception;

use Codeception\Test\Unit;
use Fei\Service\Payment\Client\Exception\ValidationException;

class ValidationExceptionTest extends Unit
{
    public function testErrorsAccessors()
    {
        $exception = new ValidationException();
        $exception->setErrors($expected = ['fake-errors']);

        $this->assertEquals($expected, $exception->getErrors());
        $this->assertAttributeEquals($expected, 'errors', $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
    }
}
