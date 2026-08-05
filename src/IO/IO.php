<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO;

use Phunkie\Cats\Applicative;
use Phunkie\Cats\Functor;
use Phunkie\Cats\Monad;
use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\AsyncHandle;
use Phunkie\Effect\Concurrent\ExecutionContext;
use Phunkie\Effect\Concurrent\FiberExecutionContext;
use Phunkie\Effect\Ops\IO\ApplicativeOps;
use Phunkie\Effect\Ops\IO\FunctorOps;
use Phunkie\Effect\Ops\IO\MonadErrorOps;
use Phunkie\Effect\Ops\IO\MonadOps;
use Phunkie\Effect\Ops\IO\ParallelOps;
use Phunkie\Types\Kind;
use Phunkie\Validation\Validation;
use Throwable;

/**
 * The IO Monad.
 *
 * IO represents a computation that performs side effects.
 * It is lazily evaluated, meaning the side effects are not performed until
 * `unsafeRun()` or `unsafeRunSync()` is called.
 *
 * It is the core data type of the effect system, supporting:
 * - Sequential execution (flatMap)
 * - Parallel execution (via Parallel type class)
 * - Error handling
 *
 * @template-covariant A
 */
class IO implements Functor, Applicative, Monad, Parallel, Kind
{
    use FunctorOps;
    use ApplicativeOps;
    use MonadOps;
    use MonadErrorOps;
    use ParallelOps;

    private $unsafeRun;

    /**
     * @param callable(): A $unsafeRun The effectful computation
     */
    public function __construct(callable $unsafeRun)
    {
        $this->unsafeRun = $unsafeRun;
    }

    /**
     * Executes the effect and returns the result.
     *
     * WARNING: This method performs side effects. It should ideally only be called
     * at the "end of the world" (e.g., in the main entry point of the application).
     *
     * @return A
     * @throws Throwable
     */
    public function unsafeRun(): mixed
    {
        return ($this->unsafeRun)();
    }

    /**
     * Returns the underlying unsafe runner callable.
     *
     * @return callable(): A
     */
    public function getUnsafeRun(): callable
    {
        return $this->unsafeRun;
    }

    /**
     * Executes the effect synchronously, awaiting any async handles.
     *
     * @return A
     * @throws Throwable
     */
    public function unsafeRunSync(): mixed
    {
        $handle = ($this->unsafeRun)();

        if ($handle instanceof AsyncHandle) {
            return $handle->await();
        }

        return $handle;
    }

    /**
     * Handles an error that occurred during the execution of this IO.
     *
     * @template B
     * @param callable(Throwable): B $handler Function to recover from the error
     * @return IO<A|B>
     */
    public function handleError(callable $handler): IO
    {
        return new IO(function () use ($handler) {
            try {
                return ($this->unsafeRun)();
            } catch (Throwable $e) {
                return $handler($e);
            }
        });
    }

    /**
     * Attempts to execute the effect, capturing any error in a Validation.
     *
     * This mirrors the `attempt` behavior in Scala/Cats, where the result
     * is lifted into an Either (here Validation) to handle errors as values.
     *
     * @return IO<Validation<\Throwable, A>> IO containing either Success(A) or Failure(Throwable)
     */
    public function attempt(): IO
    {
        $run = $this->unsafeRun;

        return new IO(
            /** @return Validation<\Throwable, A> */
            fn () => Attempt($run)
        );
    }

    /**
     * Starts this IO in the background and returns a handle to await its result.
     *
     * This forks the computation into a background fiber (or specified execution context),
     * allowing the main program to continue without blocking. The returned AsyncHandle
     * can be awaited later to retrieve the result.
     *
     * Example:
     * ```php
     * $handle = sendEmail($user)->start()->unsafeRun();
     * // ... do other work ...
     * $result = $handle->await();  // Wait for email to finish
     * ```
     *
     * @param ExecutionContext|null $context The execution context (defaults to FiberExecutionContext)
     * @return IO<AsyncHandle<A>> IO that produces a handle to the forked computation
     * @phpstan-ignore-next-line generics.variance (AsyncHandle is invariant by design)
     */
    public function start(?ExecutionContext $context = null): IO
    {
        $run = $this->unsafeRun;
        $context = $context ?? new FiberExecutionContext();

        return new IO(fn () => $context->executeAsync($run));
    }

    /**
     * Returns the arity of the IO type constructor.
     *
     * @return int Always 1 for IO<A>
     */
    public function getTypeArity(): int
    {
        return 1;
    }

    /**
     * Returns the type variable names for IO.
     *
     * @return array<string> Always ['A']
     */
    public function getTypeVariables(): array
    {
        return ['A'];
    }
}
