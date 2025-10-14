<?php

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
