<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Contracts\StrategyInterface;
use Piper\Core\Pipe;
use Piper\Core\Receipt;
use Piper\Core\Sequence;

final class PipeTest extends TestCase
{
    public function testAliasSetterAndGetter(): void
    {
        $pipe = new Pipe();
        $this->assertNull($pipe->getAlias());
        $pipe->setAlias('foo');
        $this->assertSame('foo', $pipe->getAlias());
    }

    public function testInputSetterAndGetter(): void
    {
        $pipe = new Pipe();
        $pipe->setInput('bar');
        $this->assertSame('bar', $pipe->getInput());
        $pipe->setInput(['baz']);
        $this->assertSame(['baz'], $pipe->getInput());
    }

    public function testPipeAddsSequence(): void
    {
        $pipe = new Pipe();
        $sequence = $this->createMock(Sequence::class);
        $result = $pipe->pipe($sequence);
        $this->assertInstanceOf(Pipe::class, $result);
    }

    public function testCreateSetsAlias(): void
    {
        $pipe = Pipe::create('alias');
        $this->assertSame('alias', $pipe->getAlias());
    }

    public function testGetReceiptReturnsReceipt(): void
    {
        $pipe = new Pipe();
        $this->assertInstanceOf(Receipt::class, $pipe->getReceipt());
    }

    public function testAiTextAddsSequence(): void
    {
        $pipe = new Pipe();
        $result = $pipe->aiText('prompt');
        $this->assertInstanceOf(Pipe::class, $result);
    }

    public function testTranslateAddsSequence(): void
    {
        $pipe = new Pipe();
        $result = $pipe->translate('en', 'de');
        $this->assertInstanceOf(Pipe::class, $result);
    }

    public function testFilterAddsSequence(): void
    {
        $pipe = new Pipe();
        $result = $pipe->filter('trim');
        $this->assertInstanceOf(Pipe::class, $result);
    }

    public function testSearchAddsSequence(): void
    {
        $pipe = new Pipe();
        $result = $pipe->search('test');
        $this->assertInstanceOf(Pipe::class, $result);
    }

    public function testRunExecutesSequences(): void
    {
        $pipe = new Pipe();
        $mockStrategy = $this->createMock(StrategyInterface::class);
        $mockStrategy->method('process')->willReturnCallback(fn($carry, $fn) => $fn($carry));

        $mockSequence = $this->createMock(Sequence::class);
        $mockSequence->method('getStrategy')->willReturn($mockStrategy);
        $mockSequence->method('getDependencies')->willReturn([]);
        $mockSequence->expects($this->once())->method('setData');
        $mockSequence->expects($this->once())->method('touchReceipt');
        $mockSequence->method('resolve')->willReturn('result');

        $pipe->setInput('input');
        $pipe->pipe($mockSequence);

        $result = $pipe->run();
        $this->assertSame('result', $result);
    }
}

// TODO
//    public function testTopologicalSortDetectsCircularDependency(): void
//    {
//        $this->expectException(\RuntimeException::class);
//        $this->expectExceptionMessage('Circular dependency detected');
//
//        $seqA = $this->createMock(SequenceInterface::class);
//        $seqB = $this->createMock(SequenceInterface::class);
//
//        $depA = $this->createConfiguredMock(\stdClass::class, [
//            'getAlias' => 'A',
//            'getSequence' => $seqB,
//        ]);
//        $depB = $this->createConfiguredMock(\stdClass::class, [
//            'getAlias' => 'B',
//            'getSequence' => $seqA,
//        ]);
//
//        $seqA->method('getDependencies')->willReturn([$depA]);
//        $seqB->method('getDependencies')->willReturn([$depB]);
//
//        $ref = new \ReflectionClass(Pipe::class);
//        $method = $ref->getMethod('topologicalSort');
//        $method->setAccessible(true);
//
//        $method->invoke(null, [$seqA, $seqB]);
//    }
