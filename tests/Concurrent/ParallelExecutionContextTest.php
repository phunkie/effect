<?php

namespace Tests\Phunkie\Effect\Concurrent;

use parallel\Runtime;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

class ParallelExecutionContextTest extends TestCase
{
    #[Test]
    public function it_runs_thunks_in_a_parallel_thread()
    {
        if (!class_exists(Runtime::class)) {
            $this->markTestSkipped("The 'parallel' extension is not available.");
        }

        $context = new ParallelExecutionContext();
        $thunk = function() { return 42; };

        $result = $context->execute($thunk);

        $this->assertEquals(42, $result);
    }

    #[Test]
    public function it_executes_multiple_thunks_in_parallel()
    {
        if (!class_exists(Runtime::class)) {
            $this->markTestSkipped("The 'parallel' extension is not available.");
        }

        $context = new ParallelExecutionContext();

        $thunk1 = fn() => (usleep(100_000) ?: 1); // 100ms
        $thunk2 = fn() => (usleep(100_000) ?: 2); // 100ms

        $start = microtime(true);

        $handle1 = $context->executeAsync($thunk1);
        $handle2 = $context->executeAsync($thunk2);

        $result1 = $handle1->await();
        $result2 = $handle2->await();

        $duration = microtime(true) - $start;

        $this->assertEquals(1, $result1);
        $this->assertEquals(2, $result2);
        $this->assertLessThan(0.2, $duration, "Thunks should have run in parallel (under 200ms).");
    }
}
