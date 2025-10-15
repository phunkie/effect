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
     * @template B
     * @param callable(A): B $f
     * @return IO<B>
     */
    public function map(callable $f): IO
    {
        return io(function () use ($f) {
            return $f(($this->unsafeRun)());
        });
    }

    /**
     * @template B
     * @param callable(A): B $f
     * @return callable(IO): IO<B>
     */
    public function lift($f): callable
    {
        return function ($fa) use ($f): IO {
            return $fa->map($f);
        };
    }

    /**
     * @template B
     * @param B $b
     * @return IO<B>
     */
    public function as(mixed $b): IO
    {
        return $this->map(function () use ($b) {
            return $b;
        });
    }

    /**
     * @return IO<Unit>
     */
    public function void(): IO
    {
        return $this->as(Unit());
    }

    /**
     * @template B
     * @param callable(A): B $f
     * @return IO<Pair<A, B>>
     */
    /**
     * @param callable $f
     */
    public function zipWith($f): IO
    {
        return $this->map(function ($a) use ($f): Pair {
            return Pair($a, $f($a));
        });
    }

    /**
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
