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
use Phunkie\Effect\Concurrent\FiberExecutionContext;

class FiberExecutionContextTest extends TestCase
{
    #[Test]
    public function it_runs_thunks_in_a_fiber()
    {
        $context = new FiberExecutionContext();
        $thunk = function () {
            return 42;
        };

        $result = $context->execute($thunk);

        $this->assertEquals(42, $result);
    }

    //#[Test]
    public function it_preserves_fiber_state()
    {
        $context = new FiberExecutionContext();
        $counter = 0;

        $thunk = function () use (&$counter) {
            $counter++;

            return $counter;
        };

        $result1 = $context->execute($thunk);
        $result2 = $context->execute($thunk);

        $this->assertEquals(1, $result1);
        $this->assertEquals(2, $result2);
    }
}
