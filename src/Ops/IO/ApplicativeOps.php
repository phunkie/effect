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
use Phunkie\Types\Kind;

/**
 * @template A
 */
trait ApplicativeOps
{
    /**
     * @template B
     * @param B $a
     * @return IO<B>
     */
    public function pure(mixed $a): IO
    {
        return io(function () use ($a) {
            return $a;
        });
    }

    /**
     * @template B
     * @param Kind<callable(A):B>|IO<callable(A):B> $f
     * @return IO<B>
     */
    public function apply(Kind|IO $f): IO
    {
        return io(function () use ($f) {
            $g = $this->unsafeRun();

            if (is_callable($g)) {
                return $g($f->unsafeRun());
            }

            throw new \TypeError("Internal Error: IO did not produce a callable.");
        });
    }

    /**
     * @template B
     * @template C
     * @param Kind<B>|IO<B> $fb
     * @param callable(mixed,B):C $f
     * @return IO<C>
     */
    public function map2(Kind|IO $fb, callable $f): IO
    {
        $curried = new IO(function () use ($f) {
            return function ($a) use ($f) {
                return function ($b) use ($f, $a) {
                    return $f($a, $b);
                };
            };
        });

        $partial = $curried->apply($this);

        return $partial->apply($fb);
    }
}
