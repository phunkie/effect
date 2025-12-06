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

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;
use Phunkie\Types\Pair;
use Phunkie\Types\Unit;

/**
 * @template A
 */
trait FunctorOps
{
    /**
     * Maps a function over the IO value.
     *
     * @template B
     * @param callable(A): B $f The function to apply
     * @return IO<B>
     */
    public function map(callable $f): IO
    {
        return io(function () use ($f) {
            return $f(($this->unsafeRun)());
        });
    }

    /**
     * Lifts a function to operate on IO values.
     *
     * @template B
     * @param callable(A): B $f The function to lift
     * @return callable(IO): IO<B>
     */
    public function lift($f): callable
    {
        return function ($fa) use ($f): IO {
            return $fa->map($f);
        };
    }

    /**
     * Replaces the IO value with a constant value.
     *
     * @template B
     * @param B $b The constant value
     * @return IO<B>
     */
    public function as(mixed $b): IO
    {
        return $this->map(function () use ($b) {
            return $b;
        });
    }

    /**
     * Discards the IO value and returns Unit.
     *
     * @return IO<Unit>
     */
    public function void(): IO
    {
        return $this->as(Unit());
    }

    /**
     * Paris the IO value with the result of applying a function to it.
     *
     * @template B
     * @param callable(A): B $f The function to produce the second element
     * @return IO<Pair<A, B>>
     */
    public function zipWith($f): IO
    {
        return $this->map(function ($a) use ($f): Pair {
            return Pair($a, $f($a));
        });
    }

    /**
     * Invariant map (not used for IO, but part of Functor in some contexts).
     * IO is covariant, so this delegates to map.
     *
     * @template B
     * @param callable(A): B $f
     * @param callable(B): A $g
     * @return IO<B>
     */
    public function imap(callable $f, callable $g): IO
    {
        return $this->map($f);
    }
}
