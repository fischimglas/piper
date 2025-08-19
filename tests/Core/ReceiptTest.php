<?php

declare(strict_types=1);

namespace Core;

use PHPUnit\Framework\TestCase;
use Piper\Core\Receipt;

final class ReceiptTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $receipt = new Receipt();
        $receipt->set('foo', 'bar');
        $this->assertSame('bar', $receipt->get('foo'));
    }

    public function testGetReturnsNullForUnknownKey(): void
    {
        $receipt = new Receipt();
        $this->assertNull($receipt->get('unknown'));
    }

    public function testAllReturnsAllData(): void
    {
        $receipt = new Receipt();
        $receipt->set('a', 1);
        $receipt->set('b', 2);
        $this->assertSame(['a' => 1, 'b' => 2], $receipt->all());
    }

    public function testLogAndGetLogs(): void
    {
        $receipt = new Receipt();
        $receipt->log('seq1', 'message1');
        $receipt->log('seq2', ['msg' => 2]);

        $logs = $receipt->getLogs();
        $this->assertCount(2, $logs);

        $this->assertSame('seq1', $logs[0]['sequence']);
        $this->assertSame('message1', $logs[0]['message']);
        $this->assertIsFloat($logs[0]['time']);

        $this->assertSame('seq2', $logs[1]['sequence']);
        $this->assertSame(['msg' => 2], $logs[1]['message']);
    }
}
