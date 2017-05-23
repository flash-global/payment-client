<?php
namespace Tests\Fei\Service\Payment\Client\Utils\Builder;

use Codeception\Test\Unit;
use Fei\Service\Payment\Client\Utils\Builder\OperatorBuilder;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

class OperatorBuilderTest extends Unit
{
    public function testLike()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->setMethods(['build'])->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->setMethods(['build'])
            ->getMockForAbstractClass();

        $operatorBuilder->expects($this->once())->method('build')->with('%str%', 'LIKE')->willReturn(true);

        $this->assertEquals($operatorBuilder, $operatorBuilder->like('str'));
    }

    public function testBeginsWith()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->setMethods(['build'])->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->setMethods(['build'])
            ->getMockForAbstractClass();

        $operatorBuilder->expects($this->once())->method('build')->with('str%', 'LIKE')->willReturn(true);

        $this->assertEquals($operatorBuilder, $operatorBuilder->beginsWith('str'));
    }

    public function testEndsWith()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->setMethods(['build'])->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->setMethods(['build'])
            ->getMockForAbstractClass();

        $operatorBuilder->expects($this->once())->method('build')->with('%str', 'LIKE')->willReturn(true);

        $this->assertEquals($operatorBuilder, $operatorBuilder->endsWith('str'));
    }

    public function testEqual()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->setMethods(['build'])->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->setMethods(['build'])
            ->getMockForAbstractClass();

        $operatorBuilder->expects($this->once())->method('build')->with('str', '=')->willReturn(true);

        $this->assertEquals($operatorBuilder, $operatorBuilder->equal('str'));
    }

    public function testIn()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->setMethods(['build'])->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->setMethods(['build'])
            ->getMockForAbstractClass();

        $operatorBuilder->expects($this->once())->method('build')->with(['str', 'str2'], 'IN')->willReturn(true);

        $this->assertEquals($operatorBuilder, $operatorBuilder->in(['str', 'str2']));
    }

    public function testInCacheAccessors()
    {
        $builder = $this->getMockBuilder(SearchBuilder::class)->getMock();

        /** @var OperatorBuilder|\PHPUnit_Framework_MockObject_MockObject $operatorBuilder */
        $operatorBuilder = $this->getMockBuilder(OperatorBuilder::class)
            ->setConstructorArgs([$builder])
            ->getMockForAbstractClass();

        $operatorBuilder->setInCache(['cache']);

        $this->assertEquals(['cache'], $operatorBuilder->getInCache());
        $this->assertAttributeEquals($operatorBuilder->getInCache(), 'inCache', $operatorBuilder);
    }
}
