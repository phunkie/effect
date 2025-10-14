<?php

namespace Phunkie\Effect\Concurrent;

use parallel\Future;
use parallel\Runtime;

class ParallelExecutionContext implements ExecutionContext
{
    public function execute(callable $thunk): mixed
    {
        $runtime = new Runtime(); // creates an isolated thread
        $future = $runtime->run($thunk);

        return $future->value(); // blocks until finished
    }

    public function executeAsync(callable $thunk): AsyncHandle
    {
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
    }
}
