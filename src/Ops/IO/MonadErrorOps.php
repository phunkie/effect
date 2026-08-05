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

use Phunkie\Effect\IO\IO;
use Throwable;

/**
 * MonadError combinators for IO.
 *
 * A lazy IO<A> has no empty value to filter into, so it deliberately has no
 * withFilter — a for-comprehension guard has nothing to fall through to. The
 * intent behind a guard is instead written explicitly here, through the error
 * channel: ensure raises when a predicate fails, recoverable via attempt().
 * This mirrors cats-effect, where filtering an IO is done with ensure/ensureOr
 * rather than with for-comprehension guard syntax.
 *
 * @template A
 */
trait MonadErrorOps
{
    /**
     * Raises $error when the produced value does not satisfy $predicate.
     *
     * Stays lazy: the check runs when the returned IO is run, and the raised
     * error is recoverable through attempt() / handleError().
     *
     * @param callable(A):bool $predicate The condition the value must satisfy
     * @param Throwable $error The error to raise when it does not
     * @return IO<A>
     */
    public function ensure(callable $predicate, Throwable $error): IO
    {
        return new IO(function () use ($predicate, $error) {
            $a = ($this->unsafeRun)();

            if (! $predicate($a)) {
                throw $error;
            }

            return $a;
        });
    }

    /**
     * Like ensure, but the error is computed from the offending value.
     *
     * @param callable(A):bool $predicate The condition the value must satisfy
     * @param callable(A):Throwable $error Builds the error from the value that failed
     * @return IO<A>
     */
    public function ensureOr(callable $predicate, callable $error): IO
    {
        return new IO(function () use ($predicate, $error) {
            $a = ($this->unsafeRun)();

            if (! $predicate($a)) {
                throw $error($a);
            }

            return $a;
        });
    }
}
