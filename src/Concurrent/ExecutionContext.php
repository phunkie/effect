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
 * Interface for execution contexts.
 *
 * An ExecutionContext defines how computations are executed. Implementations
 * may execute code synchronously (e.g., in a Fiber) or asynchronously
 * (e.g., using ext-parallel threads).
 */
interface ExecutionContext
{
    /**
     * Runs the given thunk in this execution context.
     *
     * @param callable(): mixed $thunk The operation to run
     * @return mixed The result of running the thunk
     */
    public function execute(callable $thunk): mixed;

    /**
     * Runs the given thunk asynchronously in this execution context.
     *
     * @template A
     * @param callable(): A $thunk The operation to run
     * @return AsyncHandle<A> A handle for the result
     */
    public function executeAsync(callable $thunk): AsyncHandle;
}
