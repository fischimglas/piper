<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Contracts\SequenceInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Core\Dependency;
use RuntimeException;

final class DependencyTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $sequence = $this->createMock(SequenceInterface::class);
        $strategy = $this->createMock(StrategyInterface::class);
        $alias = 'alias1';

        $dep = new Dependency($sequence, $strategy, $alias);

        $this->assertSame($sequence, $dep->getSequence());
        $this->assertSame($strategy, $dep->getStrategy());
        $this->assertSame($alias, $dep->getAlias());
    }

    public function testSetters(): void
    {
        $sequence1 = $this->createMock(SequenceInterface::class);
        $sequence2 = $this->createMock(SequenceInterface::class);
        $strategy1 = $this->createMock(StrategyInterface::class);
        $strategy2 = $this->createMock(StrategyInterface::class);
        $alias1 = 'alias1';
        $alias2 = 'alias2';

        $dep = new Dependency($sequence1, $strategy1, $alias1);

        $dep->setSequence($sequence2);
        $dep->setStrategy($strategy2);
        $dep->setAlias($alias2);

        $this->assertSame($sequence2, $dep->getSequence());
        $this->assertSame($strategy2, $dep->getStrategy());
        $this->assertSame($alias2, $dep->getAlias());
    }

    public function testSetStrategyWithClassName(): void
    {
        $sequence = $this->createMock(SequenceInterface::class);

        // Dummy Strategy-Klasse für den Test
        $strategyClass = new class implements StrategyInterface {
            public function process(mixed $value, callable $processor): mixed
            {
                return $value;
            }
        };

        $dep = new Dependency($sequence, get_class($strategyClass), 'alias');
        $this->assertInstanceOf(StrategyInterface::class, $dep->getStrategy());
    }

    public function testSetStrategyThrowsOnInvalidClass(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/must implement/');

        $sequence = $this->createMock(SequenceInterface::class);

        // Klasse, die NICHT StrategyInterface implementiert
        $invalidClass = new class {
        };

        new Dependency($sequence, get_class($invalidClass), 'alias');
    }

    public function testCreateStatic(): void
    {
        $sequence = $this->createMock(SequenceInterface::class);
        $strategy = $this->createMock(StrategyInterface::class);
        $alias = 'alias';

        $dep = Dependency::create($sequence, $strategy, $alias);

        $this->assertInstanceOf(Dependency::class, $dep);
        $this->assertSame($sequence, $dep->getSequence());
        $this->assertSame($strategy, $dep->getStrategy());
        $this->assertSame($alias, $dep->getAlias());
    }

    public function testGetResultReturnsNull(): void
    {
        $sequence = $this->createMock(SequenceInterface::class);
        $strategy = $this->createMock(StrategyInterface::class);
        $dep = new Dependency($sequence, $strategy, 'alias');

        $this->assertNull($dep->getResult());
    }
}
