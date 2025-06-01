<?php

namespace Phunkie\Effect\Ops;

use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

trait ParallelOps
{
    public function parMap2(Parallel $fb, callable $f): Parallel
    {
        return new static(function() use ($fb, $f) {
            $handle1 = ($this->unsafeRun)();
            $handle2 = ($fb->unsafeRun)();
            
            $a = $handle1->await();
            $b = $handle2->await();
            
            return $f($a, $b);
        });
    }
}
