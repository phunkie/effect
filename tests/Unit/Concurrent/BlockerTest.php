<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Phunkie\Effect\Concurrent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
