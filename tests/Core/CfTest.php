<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Core\Cf;

final class CfTest extends TestCase
{
    public function testGetReturnsNullForUnknownKey(): void
    {
        $this->assertNull(Cf::get('thisKeyDoesNotExist'));
    }

    public function testConfigFileLoads(): void
    {
        $value = Cf::get('GoogleAiAdapter.model');
        $this->assertEquals($value, 'gemini-2.0-flash');
    }
}
