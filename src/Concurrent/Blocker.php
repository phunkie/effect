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

/**
 * Provides an execution context for blocking operations.
 *
 * Blocker is designed to isolate blocking code from the main execution context,
 * typically by running it on a separate thread or fiber pool to prevent
 * starving the main event loop.
 */
class Blocker
{
    /**
     * @param \Closure(): mixed $thunk The computation to execute
     * @param ExecutionContext|null $context The execution context to use (defaults to FiberExecutionContext)
     */
    public function __construct(
        private readonly \Closure $thunk,
        private ?ExecutionContext $context = null
    ) {
        if ($this->context === null) {
            $this->context = new FiberExecutionContext();
        }
    }

    /**
     * Executes the blocking operation asynchronously.
     *
     * @return AsyncHandle A handle to await the result
     */
    public function __invoke(): AsyncHandle
    {
        return $this->context->executeAsync($this->thunk);
    }

    /**
     * Executes the blocking operation synchronously.
     *
     * @return mixed The result of the computation
     */
    public function runSync(): mixed
    {
        return $this->context?->execute($this->thunk);
    }

    /**
     * Returns the underlying execution context.
     *
     * @return ExecutionContext
     */
    public function blockingContext(): ExecutionContext
    {
        return $this->context ?? new FiberExecutionContext();
    }
}
