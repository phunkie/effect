<?php

namespace Phunkie\Effect\Ops;

use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

trait ParallelOps
{
    public function parMap2(Parallel $fb, callable $f): Parallel
    {
        return new static(function() use ($fb, $f) {
            if (!$this->unsafeRun instanceof Blocker && !$this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            if (!$fb->unsafeRun instanceof Blocker && !$fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            $handle1 = ($this->unsafeRun)();
            $handle2 = ($fb->unsafeRun)();

            $a = $handle1->await();
            $b = $handle2->await();
            
            return $f($a, $b);
        });
    }

    public function parMap3(Parallel $fb, Parallel $fc, callable $f): Parallel
    {
        return new static(function() use ($fb, $fc, $f) {
            if (!$this->unsafeRun instanceof Blocker && !$this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            if (!$fb->unsafeRun instanceof Blocker && !$fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            if (!$fc->unsafeRun instanceof Blocker && !$fc->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Third effect Blocker isn't in a parallel context");
            }

            $handle1 = ($this->unsafeRun)();
            $handle2 = ($fb->unsafeRun)();
            $handle3 = ($fc->unsafeRun)();

            $a = $handle1->await();
            $b = $handle2->await();
            $c = $handle3->await();

            return $f($a, $b, $c);
        });
    }

    public function parMapN(array $fbs, callable $f): Parallel
    {
        return new static(function() use ($fbs, $f) {
            if (!$this->unsafeRun instanceof Blocker && !$this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            foreach ($fbs as $fb) {
                if (!$fb->unsafeRun instanceof Blocker && !$fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                    throw new \Exception("Effect Blocker isn't in a parallel context");
                }
            }

            $handles = array_map(fn($fb) => ($fb->unsafeRun)(), $fbs);

            $results = array_map(fn($handle) => $handle->await(), $handles);

            return $f(...$results);
        });
    }
}
