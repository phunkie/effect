<?php

namespace Phunkie\Effect\Ops;

use Phunkie\Types\Kind;
use Phunkie\Effect\IO\IO;
use Phunkie\Cats\Applicative;

trait ApplicativeOps
{
    /**
     * @template B
     * @param B $a
     * @return Kind<B>|Applicative<B>|IO<B>
     */
    public function pure($a): Applicative
    {
        return new IO(function () use ($a) {
            return $a;
        });
    }

    /**
     * @template B
     * @param Kind<callable(mixed):B>|IO<callable(mixed):B> $f
     * @return Kind<B>|Applicative<B>|IO<B>
     */
    public function apply(Kind | IO $f): Kind | IO
    {
        return new IO(function () use ($f) {
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
     * @return Kind<C>|Applicative<B>|IO<C>
     */
    public function map2(Kind | IO $fb, callable $f): Kind | IO
    {
        $curried = new IO(function () use ($f) {
            return function ($a) use ($f) {
                return function ($b) use ($f, $a) {
                    return $f($a, $b);
                };
            };
        });

        $patial = $curried->apply($this);

        return $patial->apply($fb);
    }
}
