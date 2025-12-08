<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Concurrent;

use FiberError;
use Throwable;

/**
 * Execution context based on PHP Fibers.
 *
 * This implementation uses PHP 8.1+ Fibers to execute computations cooperatively.
 * It is single-threaded (uses the main thread) but supports non-blocking behavior
 * when used with Fiber-aware primitives.
 */
class FiberExecutionContext implements ExecutionContext
{
    /**
     * Executes the thunk in a new Fiber.
     *
     * @param callable(): mixed $thunk The operation to run
     * @return mixed The result of running the thunk
     * @throws FiberError|Throwable If the fiber fails
     */
    public function execute(callable $thunk): mixed
    {
        $fiber = new \Fiber($thunk);
        $result = $fiber->start();

        while (! $fiber->isTerminated()) {
            $fiber->resume();
        }

        return $fiber->getReturn();
    }

    /**
     * Executes the thunk asynchronously.
     *
     * In this implementation, despite the name, the execution happens
     * when `await()` is called, effectively deferring execution.
     *
     * @template A
     * @param callable(): A $thunk The operation to run
     * @return AsyncHandle<A> A handle for the result
     */
    public function executeAsync(callable $thunk): AsyncHandle
    {
        $that = $this;

        return new class ($that, $thunk) implements AsyncHandle {
            public function __construct(private readonly FiberExecutionContext $that, private $thunk)
            {
            }

            public function await(): mixed
            {
                return $this->that->execute($this->thunk);
            }
        };
    }
}
