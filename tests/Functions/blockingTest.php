<?php

namespace Tests\Phunkie\Effect\Functions;

use parallel\Runtime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

use function Phunkie\Effect\Functions\blocking\blocking;

class BlockingTest extends TestCase
{
    #[Test]
    public function it_runs_a_blocking_operation()
    {
        $io = blocking(function () {
            return 42;
        });

        $this->assertEquals(
            42,
            $io->unsafeRunSync()
        );
    }

    #[Test]
    public function it_runs_blocking_operations_in_parallel()
    {
        if (!class_exists(Runtime::class)) {
            $this->markTestSkipped("The 'parallel' extension is not available.");
        }

        $ec = new ParallelExecutionContext();

        $io1 = blocking(fn() => (usleep(100_000) ?: 1), $ec);
        $io2 = blocking(fn() => (usleep(100_000) ?: 2), $ec);

        $start = microtime(true);

        [$result1, $result2] = $io1->parMap2($io2, fn($a, $b) => [$a, $b])->unsafeRunSync();

        $duration = microtime(true) - $start;

        $this->assertEquals(1, $result1);
        $this->assertEquals(2, $result2);
        $this->assertLessThan(0.2, $duration, "Blocking operations should run in parallel (under 200ms).");
    }
}
