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

class ParallelExecutionContext implements ExecutionContext
{
    /**
     * @throws Throwable
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
