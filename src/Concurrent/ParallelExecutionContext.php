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

use parallel\Future;
use parallel\Runtime;
use Throwable;

/**
 * Execution context using ext-parallel for true parallelism.
 *
 * This implementation utilizes the `parallel` extension (requires ZTS PHP)
 * to execute computations in separate threads.
 */
class ParallelExecutionContext implements ExecutionContext
{
    /**
     * Executes the thunk in a new thread and blocks until completion.
     *
     * @param callable(): mixed $thunk The operation to run
     * @return mixed The result of running the thunk
     * @throws Throwable If execution fails or ext-parallel is missing
     */
    public function execute(callable $thunk): mixed
    {
        if (\extension_loaded('parallel')) {
            $runtime = new Runtime(); // creates an isolated thread
            $future = $runtime->run($thunk);

            return $future->value(); // blocks until finished
        } else {
            throw new \RuntimeException("The 'parallel' extension is required for ParallelExecutionContext.");
        }
    }

    /**
     * Executes the thunk asynchronously in a new thread.
     *
     * Returns an AsyncHandle that can be awaited to retrieve the result.
     *
     * @template A
     * @param callable(): A $thunk The operation to run
     * @return AsyncHandle<A> A handle for the result
     * @throws \RuntimeException If ext-parallel is missing
     */
    public function executeAsync(callable $thunk): AsyncHandle
    {
        if (\extension_loaded('parallel')) {
            $runtime = new Runtime(); // one thread per async task
            $future = $runtime->run($thunk);

            return new class ($future) implements AsyncHandle {
                public function __construct(private readonly Future $future)
                {
                }

                public function await(): mixed
                {
                    return $this->future->value(); // blocks when awaited
                }
            };
        } else {
            throw new \RuntimeException("The 'parallel' extension is required for ParallelExecutionContext.");
        }
    }
}
