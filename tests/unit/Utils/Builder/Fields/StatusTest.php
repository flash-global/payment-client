<?php
namespace Tests\Fei\Service\Payment\Client\Utils\Builder;

use Codeception\Test\Unit;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Status;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Uuid;
use Fei\Service\Payment\Client\Utils\Builder\OperatorBuilder;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

class StatusTest extends Unit
{
    public function testBuild()
    {
        $searchBuilder = $this->getMockBuilder(SearchBuilder::class)
            ->setMethods(['getParams', 'SetParams'])
            ->getMock();

        $searchBuilder->expects($this->once())->method('getParams')->willReturn([]);
        $searchBuilder->expects($this->once())->method('setParams')->with([
            'status' => 'fake-fake-status'
        ]);

        $uuid = new Status($searchBuilder);
        $uuid->build('fake-fake-status');
    }

    public function testBuilderAccessors()
    {
        /** @var SearchBuilder $searchBuilder */
        $searchBuilder = $this->getMockBuilder(SearchBuilder::class)
            ->getMock();

        $uuid = new Status($searchBuilder);
        $uuid->setBuilder($searchBuilder);

        $this->assertEquals($searchBuilder, $uuid->getBuilder());
        $this->assertAttributeEquals($uuid->getBuilder(), 'builder', $uuid);
    }
}
