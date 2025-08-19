<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Contracts\AdapterInterface;
use Piper\Contracts\FilterInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Core\Dependency;
use Piper\Core\Receipt;
use Piper\Core\Sequence;

final class SequenceTest extends TestCase
{
    public function testCreateSetsDefaultsAndAlias(): void
    {
        $seq = Sequence::create();
        $this->assertNotNull($seq->getAlias());
        $this->assertInstanceOf(Sequence::class, $seq);
    }

    public function testSetAndGetAlias(): void
    {
        $seq = new Sequence();
        $seq->setAlias('foo');
        $this->assertSame('foo', $seq->getAlias());
    }

    public function testSetAndGetResult(): void
    {
        $seq = new Sequence();
        $seq->setResult('bar');
        $this->assertSame('bar', $seq->getResult());
    }

    public function testSetAndGetFilter(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $seq = new Sequence();
        $seq->setFilter($filter);
        $this->assertSame($filter, $seq->getFilter());
    }

    public function testSetAndGetTemplate(): void
    {
        $seq = new Sequence();
        $seq->setTemplate('foo');
        $this->assertSame('foo', $seq->getTemplate());
    }

    public function testSetAndGetAdapter(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $seq = new Sequence();
        $seq->setAdapter($adapter);
        $this->assertSame($adapter, $seq->getAdapter());
    }

    public function testSetAndGetStrategy(): void
    {
        $strategy = $this->createMock(StrategyInterface::class);
        $seq = new Sequence();
        $seq->setStrategy($strategy);
        $this->assertSame($strategy, $seq->getStrategy());
    }

    public function testAddAndGetDependencies(): void
    {
        $dep = $this->createMock(Dependency::class);
        $dep->method('getAlias')->willReturn('dep1');
        $seq = new Sequence();
        $seq->addDependency($dep);
        $this->assertArrayHasKey('dep1', $seq->getDependencies());
    }

    public function testSetDependencies(): void
    {
        $dep1 = $this->createMock(Dependency::class);
        $dep1->method('getAlias')->willReturn('dep1');
        $dep2 = $this->createMock(Dependency::class);
        $dep2->method('getAlias')->willReturn('dep2');
        $seq = new Sequence();
        $seq->setDependencies([$dep1, $dep2]);
        $this->assertArrayHasKey('dep1', $seq->getDependencies());
        $this->assertArrayHasKey('dep2', $seq->getDependencies());
    }

    public function testSetAndGetData(): void
    {
        $seq = new Sequence();
        $seq->setData(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $seq->getData());
    }

    public function testTouchAndGetReceipt(): void
    {
        $receipt = new Receipt();
        $seq = new Sequence();
        $seq->touchReceipt($receipt);
        $this->assertSame($receipt, $seq->getReceipt());
    }

    public function testResolveWithAdapterAndFilter(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects($this->once())->method('process')->willReturn('adapted');

        $filter = $this->createMock(FilterInterface::class);
        // FilterResolver::apply wird nicht direkt getestet, daher simulieren wir das Filter-Array
        $seq = new Sequence();
        $seq->setAdapter($adapter);
        $seq->setFilter([$filter]);

        // TemplateResolver wird nicht aufgerufen, da kein Template gesetzt ist
        $result = $seq->resolve('input');
        $this->assertEquals('adapted', $result);
    }

    public function testResolveWithTemplate(): void
    {
        // TemplateResolver::resolve wird nicht direkt getestet, daher simulieren wir das Verhalten
        // Wir setzen ein Template und prüfen, ob resolve ohne Adapter/Filter durchläuft
        $seq = new Sequence();
        $seq->setTemplate('Hello {{input}}');
        $result = $seq->resolve('World');
        $this->assertIsString($result);
    }
}
