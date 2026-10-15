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

use function Left;

use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\CancellableHandle;
use Phunkie\Effect\Concurrent\ExecutionContext;
use Phunkie\Effect\Concurrent\ParallelExecutionContext;
use Phunkie\Effect\Concurrent\TimeoutException;
use Phunkie\Effect\IO\IO;
use Phunkie\Types\Either;

use function Right;

use RuntimeException;

/**
 * Running effects against each other or against the clock. race, either and timeout start the
 * effects where they can be polled and cancelled: a blocking effect runs in its own context, any
 * other in the context given, the parallel one by default.
 */
trait ConcurrentOps
{
    /**
     * Both results, this effect run first, as a pair.
     *
     * @template B
     * @param IO<B> $other
     * @return IO<array{mixed, B}>
     */
    public function both(IO $other): IO
    {
        return new IO(fn () => [$this->unsafeRunSync(), $other->unsafeRunSync()]);
    }

    /**
     * The result of whichever effect finishes first; the other is cancelled.
     *
     * @template B
     * @param IO<B> $other
     * @return IO<mixed>
     */
    public function race(IO $other, ?ExecutionContext $context = null): IO
    {
        return new IO(fn () => $this->winner($other, $context)[1]);
    }

    /**
     * Like race, but says which one won: Left for this effect, Right for the other.
     *
     * @template B
     * @param IO<B> $other
     * @return IO<Either<mixed, B>>
     */
    public function either(IO $other, ?ExecutionContext $context = null): IO
    {
        return new IO(function () use ($other, $context) {
            [$index, $value] = $this->winner($other, $context);

            return 0 === $index ? Left($value) : Right($value);
        });
    }

    /**
     * The result, provided it arrives within $millis; otherwise the effect is cancelled and a
     * TimeoutException is raised.
     *
     * @return IO<mixed>
     */
    public function timeout(int $millis, ?ExecutionContext $context = null): IO
    {
        return new IO(function () use ($millis, $context) {
            $handle = $this->handleIn($context ?? new ParallelExecutionContext());
            $deadline = hrtime(true) + $millis * 1_000_000;
            while (! $handle->isDone()) {
                if (hrtime(true) >= $deadline) {
                    $handle->cancel();

                    throw new TimeoutException(sprintf('Timed out after %d ms.', $millis));
                }
                usleep(1000);
            }

            return $handle->await();
        });
    }

    /**
     * @return array{0: int, 1: mixed} which effect finished first, 0 for this one, and its result
     */
    private function winner(IO $other, ?ExecutionContext $context): array
    {
        $context ??= new ParallelExecutionContext();
        $handles = [$this->handleIn($context), $other->handleIn($context)];
        while (true) {
            foreach ($handles as $index => $handle) {
                if ($handle->isDone()) {
                    $handles[1 - $index]->cancel();

                    return [$index, $handle->await()];
                }
            }
            usleep(1000);
        }
    }

    private function handleIn(ExecutionContext $context): CancellableHandle
    {
        $run = $this->unsafeRun;
        $handle = $run instanceof Blocker ? $run() : $context->executeAsync($run);
        if (! $handle instanceof CancellableHandle) {
            throw new RuntimeException('race, either and timeout need an execution context whose handles can be polled and cancelled, such as ParallelExecutionContext; a blocking effect brings its own context.');
        }

        return $handle;
    }
}
