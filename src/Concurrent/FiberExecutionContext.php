<?php

namespace Phunkie\Effect\Concurrent;

class FiberExecutionContext implements ExecutionContext
{
    public function execute(callable $thunk): mixed
    {
        $fiber = new \Fiber($thunk);
        $result = $fiber->start();

        while (!$fiber->isTerminated()) {
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
