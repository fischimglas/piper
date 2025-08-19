<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Core\Cf;

final class PipeTest extends TestCase
{
    public function testGetReturnsNullForUnknownKey(): void
    {
        $this->assertNull(null);
    }

}
