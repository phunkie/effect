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
     * @template B
     * @template C
     * @param Parallel<B> $fb
     * @param callable(A, B): C $f
     * @return IO<C>
     */
    public function parMap2(Parallel $fb, callable $f): IO
    {
        return io(function () use ($fb, $f) {
            if (! $this->unsafeRun instanceof Blocker && ! $this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            if (! $fb->unsafeRun instanceof Blocker && ! $fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            $handle1 = ($this->unsafeRun)();
            $handle2 = ($fb->unsafeRun)();

            $a = $handle1->await();
            $b = $handle2->await();

            return $f($a, $b);
        });
    }

    /**
     * @template B
     * @template C
     * @param Parallel<B> $fb
     * @param Parallel<C> $fc
     * @param callable(A, B, C): IO $f
     * @return IO
     */
    public function parMap3(Parallel $fb, Parallel $fc, callable $f): IO
    {
        return io(function () use ($fb, $fc, $f) {
            if (! $this->unsafeRun instanceof Blocker && ! $this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            if (! $fb->unsafeRun instanceof Blocker && ! $fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("Second effect Blocker isn't in a parallel context");
            }

            if (! $fc->unsafeRun instanceof Blocker && ! $fc->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
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

    /**
     * @template B
     * @template C
     * @param array<Parallel<B>> $fbs
     * @param callable(B ...$args): C $f
     * @return IO<C>
     */
    public function parMapN(array $fbs, callable $f): IO
    {
        return io(function () use ($fbs, $f) {
            if (! $this->unsafeRun instanceof Blocker && ! $this->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                throw new \Exception("First effect Blocker isn't in a parallel context");
            }

            foreach ($fbs as $fb) {
                if (! $fb->unsafeRun instanceof Blocker && ! $fb->unsafeRun->blockingContext() instanceof ParallelExecutionContext) {
                    throw new \Exception("Effect Blocker isn't in a parallel context");
                }
            }

            $handles = array_map(fn ($fb) => ($fb->unsafeRun)(), $fbs);

            $results = array_map(fn ($handle) => $handle->await(), $handles);

            return $f(...$results);
        });
    }
}
