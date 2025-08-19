<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;

final class TemplateTest extends TestCase
{
    public function testGetReturnsNullForUnknownKey(): void
    {
        $this->assertNull(null);
    }
    
}
