<?php
namespace Tests\Fei\Service\Payment\Client\Utils\Builder;

use Codeception\Test\Unit;
use Fei\Service\Payment\Client\Utils\Builder\Fields\Uuid;
use Fei\Service\Payment\Client\Utils\Builder\SearchBuilder;

class UuidTest extends Unit
{
    public function testBuild()
    {
        $searchBuilder = $this->getMockBuilder(SearchBuilder::class)
            ->setMethods(['getParams', 'SetParams'])
            ->getMock();

        $searchBuilder->expects($this->once())->method('getParams')->willReturn([]);
        $searchBuilder->expects($this->once())->method('setParams')->with([
            'uuid' => 'fake-uuid'
        ]);

        $uuid = new Uuid($searchBuilder);
        $uuid->build('fake-uuid');
    }

    public function testBuilderAccessors()
    {
        /** @var SearchBuilder $searchBuilder */
        $searchBuilder = $this->getMockBuilder(SearchBuilder::class)
            ->getMock();

        $uuid = new Uuid($searchBuilder);
        $uuid->setBuilder($searchBuilder);

        $this->assertEquals($searchBuilder, $uuid->getBuilder());
        $this->assertAttributeEquals($uuid->getBuilder(), 'builder', $uuid);
    }
}
