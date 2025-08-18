<?php

namespace Strategy;

use PHPUnit\Framework\TestCase;
use Piper\Strategy\PerItemStrategy;


class PerItemStrategyTest extends TestCase
{
    public function testGeneral()
    {
        $values = [1, 2, 3, 4, 5];

        $candidate = new PerItemStrategy();

        $result = $candidate->process($values, function ($value) {
            return $value * 2;
        });

        $this->assertEquals([2, 4, 6, 8, 10], $result);

    }
}
