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

class FiberExecutionContext implements ExecutionContext
{
    /**
     * @throws FiberError|Throwable
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
