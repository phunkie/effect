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

/**
 * @template A
 */
trait MonadOps
{
    /**
     * Binds a function to the IO value, returning a new IO.
     *
     * @template B
     * @param callable(A):IO<B> $f The function to bind
     * @return IO<B>
     */
    public function flatMap(callable $f): IO
    {
        return new IO(function () use ($f) {
            $a = ($this->unsafeRun)();

            return $f($a)->unsafeRun();
        });
    }

    /**
     * Flattens a nested IO structure (IO<IO<A>> -> IO<A>).
     *
     * @return IO<A> where A is the type parameter of the inner IO
     */
    public function flatten(): IO
    {
        return $this->flatMap(fn ($x) => $x);
    }
}
