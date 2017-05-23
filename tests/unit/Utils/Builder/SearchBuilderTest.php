<?php
namespace Tests\Fei\Service\Payment\Client\Utils\Builder;

use Codeception\Test\Unit;
use Fei\Service\Payment\Client\Exception\PaymentException;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Context;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Status;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Uuid;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

class SearchBuilderTest extends Unit
{
    public function testUuid()
    {
        $builder = new SearchBuilder();

        $this->assertEquals(new Uuid($builder), $builder->uuid());
    }

    public function testContextCondition()
    {
        $builder = new SearchBuilder();

        $builder->contextCondition('OR');

        $this->assertEquals(['context_condition' => 'OR'], $builder->getParams());

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Type has to be either "AND" or "OR"!');

        $builder->contextCondition('FAKE');
    }

    public function testContext()
    {
        $builder = new SearchBuilder();

        $this->assertEquals(new Context($builder), $builder->context());
    }

    public function testParamsAccessors()
    {
        $builder = new SearchBuilder();
        $builder->setParams(['params']);

        $this->assertEquals(['params'], $builder->getParams());
        $this->assertAttributeEquals($builder->getParams(), 'params', $builder);
    }

    public function testToCamelCase()
    {
        $builder = new SearchBuilder();

        $this->assertEquals('FakeStr', $builder->toCamelCase('fake_str'));
        $this->assertEquals('FakeStr-2', $builder->toCamelCase('fake_str-2'));
        $this->assertEquals('ThisIsAString', $builder->toCamelCase('this_is_a_string'));
    }

    public function testCallWhenClassDoesNotExists()
    {
        $builder = new SearchBuilder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot load fakeClass filter!");
        $builder->fakeClass();
    }

    public function testCallWhenClassExists()
    {
        $builder = new SearchBuilder();

        $this->assertEquals(new Status($builder), $builder->status()->equal(2));
    }
}
