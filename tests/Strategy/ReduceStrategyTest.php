<?php

namespace Strategy;

use PHPUnit\Framework\TestCase;
use Piper\Core\Strategy\ReduceStrategy;

class ReduceStrategyTest extends TestCase
{
    public function testGeneral()
    {
        $values = [1, 2, 3, 4, 5];

        $candidate = new ReduceStrategy();

        $result = $candidate->process($values, function ($carry, $item) {
            return $carry + $item;
        });

        $this->assertEquals(15, $result);
    }
}
