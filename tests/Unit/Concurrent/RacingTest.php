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

use parallel\Runtime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phunkie\Effect\Concurrent\AsyncHandle;
use Phunkie\Effect\Concurrent\CancellableHandle;
use Phunkie\Effect\Concurrent\ExecutionContext;
use Phunkie\Effect\Concurrent\FiberExecutionContext;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;
use Phunkie\Effect\Concurrent\TimeoutException;

use function Phunkie\Effect\Functions\blocking\blocking;
use function Phunkie\Effect\Functions\io\io;

use RuntimeException;

class RacingTest extends TestCase
{
    #[Test]
    public function race_returns_whichever_finishes_first_and_cancels_the_other()
    {
        $context = new ScriptedContext([3, 1]);

        $winner = io(fn () => 'slow')->race(io(fn () => 'fast'), $context)->unsafeRun();

        $this->assertSame('fast', $winner);
        $this->assertSame([0], $context->cancelled);
    }

    #[Test]
    public function race_can_be_won_by_the_first_effect()
    {
        $context = new ScriptedContext([1, 3]);

        $this->assertSame('first', io(fn () => 'first')->race(io(fn () => 'second'), $context)->unsafeRun());
        $this->assertSame([1], $context->cancelled);
    }

    #[Test]
    public function either_says_which_effect_won()
    {
        $this->assertEquals(Left('a'), io(fn () => 'a')->either(io(fn () => 'b'), new ScriptedContext([1, 2]))->unsafeRun());
        $this->assertEquals(Right('b'), io(fn () => 'a')->either(io(fn () => 'b'), new ScriptedContext([2, 1]))->unsafeRun());
    }

    #[Test]
    public function timeout_returns_the_result_when_it_arrives_in_time()
    {
        $this->assertSame(42, io(fn () => 42)->timeout(50, new ScriptedContext([2]))->unsafeRun());
    }

    #[Test]
    public function timeout_cancels_the_effect_and_fails_when_the_time_is_up()
    {
        $context = new ScriptedContext([PHP_INT_MAX]);

        try {
            io(fn () => 'never')->timeout(10, $context)->unsafeRun();
            $this->fail('Expected a TimeoutException.');
        } catch (TimeoutException $e) {
            $this->assertSame('Timed out after 10 ms.', $e->getMessage());
        }

        $this->assertSame([0], $context->cancelled);
    }

    #[Test]
    public function racing_needs_handles_that_can_be_polled_and_cancelled()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('polled and cancelled');

        io(fn () => 1)->race(io(fn () => 2), new FiberExecutionContext())->unsafeRun();
    }

    #[Test]
    public function nothing_runs_before_the_io_does()
    {
        $runs = 0;
        $counting = function () use (&$runs) {
            $runs++;

            return $runs;
        };

        $race = io($counting)->race(io($counting), new ScriptedContext([1, 1]));

        $this->assertSame(0, $runs);
        $this->assertSame(1, $race->unsafeRun());
    }

    #[Test]
    public function blocking_effects_race_in_the_parallel_context()
    {
        if (! class_exists(Runtime::class)) {
            $this->markTestSkipped("The 'parallel' extension is not available.");
        }

        $slow = blocking(function () {
            usleep(200000);

            return 'slow';
        }, new ParallelExecutionContext());
        $fast = blocking(function () {
            usleep(20000);

            return 'fast';
        }, new ParallelExecutionContext());

        $this->assertSame('fast', $slow->race($fast)->unsafeRun());
    }
}

/**
 * An execution context for the specs: each computation reports itself done after a scripted
 * number of polls, so races and timeouts are decided by the script, not by timing.
 */
final class ScriptedContext implements ExecutionContext
{
    /** @var list<int> */
    public array $cancelled = [];

    private int $started = 0;

    /**
     * @param list<int> $pollsUntilDone one entry per computation started, in order
     */
    public function __construct(private array $pollsUntilDone)
    {
    }

    public function execute(callable $thunk): mixed
    {
        return $thunk();
    }

    public function executeAsync(callable $thunk): AsyncHandle
    {
        $index = $this->started++;
        $polls = $this->pollsUntilDone[$index];

        return new class ($thunk, $polls, function () use ($index) {
            $this->cancelled[] = $index;
        }) implements CancellableHandle {
            public function __construct(private $thunk, private int $polls, private \Closure $onCancel)
            {
            }

            public function isDone(): bool
            {
                return --$this->polls <= 0;
            }

            public function cancel(): void
            {
                ($this->onCancel)();
            }

            public function await(): mixed
            {
                return ($this->thunk)();
            }
        };
    }
}
