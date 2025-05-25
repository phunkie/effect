<?php

namespace Phunkie\Effect\Ops;

use Phunkie\Types\Unit;
use Phunkie\Types\Pair;
use Phunkie\Types\Kind;
use Phunkie\Effect\IO\IO;

trait FunctorOps
{
    /**
     * @template T
     * @template U
     * @param callable(T): U $f
     * @return Kind<U> | IO<U>
     */
    public function map(callable $f): Kind | IO
    {
        return new static(function() use ($f) {
            return $f(($this->unsafeRun)());
        });
    }

    /**
     * @template B
     * @param callable(mixed): B $f
     * @return callable(Kind): Kind<B> | callable(IO): IO<B>
     */
    public function lift($f): callable
    {
        return function($fa) use ($f): Kind | IO {
            return $fa->map($f);
        };
    }

    /**
     * @template B
     * @param B $b
     * @return Kind<B> | IO<B>
     */
    public function as($b): Kind | IO
    {
        return $this->map(function() use ($b) {
            return $b;
        });
    }

    /**
     * @return Kind<Unit>|IO<Unit>
     */
    public function void(): Kind | IO
    {
        return $this->as(Unit());
    }

    /**
     * @template B
     * @param callable(mixed): B $f
     * @return Kind<Pair>|IO<Pair>
     */
    public function zipWith($f): Kind | IO
    {
        return $this->map(function($a) use ($f): Pair {
            return Pair($a, $f($a));
        });
    }

    /**
     * @template B
     * @param callable(mixed): B $f
     * @param callable(B): mixed $g
     * @return Kind<B>|IO<B>
     */
    public function imap(callable $f, callable $g): Kind | IO
    {
        return $this->map($f);
    }
} 
