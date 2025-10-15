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

interface ExecutionContext
{
    /**
     * Runs the given thunk in this execution context.
     *
     * @param callable $thunk The operation to run
     * @return mixed The result of running the thunk
     */
    public function execute(callable $thunk): mixed;
    public function executeAsync(callable $thunk): AsyncHandle;
}
