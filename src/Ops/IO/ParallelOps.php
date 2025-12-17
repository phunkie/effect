<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Ops\IO;

use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;

/**
 * @mixin IO
 * @template A
 */
trait ParallelOps
{
    /**
     * Combines two parallel computations using a combining function.
     * Starts execution of both computations immediately (if in a parallel context).
     *
     * @template B
     * @template C
     * @param Parallel<B> $fb The second computation
     * @param callable(A, B): C $f The combining function
     * @return IO<C>
     */
    public function parMap2(Parallel $fb, callable $f): IO
    {
        return io(function () use ($fb, $f) {
            $runA = $this->unsafeRun;
            if (! $runA instanceof Blocker || ! $runA->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            $runB = $fb->getUnsafeRun();
            if (! $runB instanceof Blocker || ! $runB->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            $handle1 = $runA();
            $handle2 = $runB();

            $a = $handle1->await();
            $b = $handle2->await();

            return $f($a, $b);
        });
    }

    /**
     * Combines three parallel computations using a combining function.
     *
     * @template B
     * @template C
     * @param Parallel<B> $fb The second computation
     * @param Parallel<C> $fc The third computation
     * @param callable(A, B, C): IO $f The combining function
     * @return IO
     */
    public function parMap3(Parallel $fb, Parallel $fc, callable $f): IO
    {
        return io(function () use ($fb, $fc, $f) {
            $runA = $this->unsafeRun;
            if (! $runA instanceof Blocker || ! $runA->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            $runB = $fb->getUnsafeRun();
            if (! $runB instanceof Blocker || ! $runB->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            $runC = $fc->getUnsafeRun();
            if (! $runC instanceof Blocker || ! $runC->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Third effect Blocker isn't in a parallel context");
            }

            $handle1 = $runA();
            $handle2 = $runB();
            $handle3 = $runC();

            $a = $handle1->await();
            $b = $handle2->await();
            $c = $handle3->await();

            return $f($a, $b, $c);
        });
    }

    /**
     * Combines multiple parallel computations using a combining function.
     *
     * @template B
     * @template C
     * @param array<Parallel<B>> $fbs Array of computations
     * @param callable(B ...$args): C $f The combining function
     * @return IO<C>
     */
    public function parMapN(array $fbs, callable $f): IO
    {
        return io(function () use ($fbs, $f) {
            $runA = $this->unsafeRun;
            if (! $runA instanceof Blocker || ! $runA->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            $runners = [];
            foreach ($fbs as $fb) {
                $runner = $fb->getUnsafeRun();
                if (! $runner instanceof Blocker || ! $runner->blockingContext() instanceof ParallelExecutionContext) {
                    throw new \Exception("Effect Blocker isn't in a parallel context");
                }
                $runners[] = $runner;
            }

            $handles = array_map(fn (Blocker $runner) => $runner(), $runners);

            $results = array_map(fn ($handle) => $handle->await(), $handles);

            return $f(...$results);
        });
    }
}
