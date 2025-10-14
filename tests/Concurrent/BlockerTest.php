<?php

namespace Tests\Phunkie\Effect\Concurrent;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ExecutionContext;

class BlockerTest extends TestCase
{
    #[Test]
    public function it_delegates_to_execution_context()
    {
        $thunk = function () {
            return 42;
        };

        $context = $this->createMock(ExecutionContext::class);

        $context->expects($this->once())
            ->method('execute')
            ->with($thunk)
            ->willReturn(42);

        $blocking = new Blocker($thunk, $context);
        $result = $blocking->runSync();

        $this->assertEquals(42, $result);
    }
}
