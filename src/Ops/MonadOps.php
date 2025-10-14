<?php

namespace Phunkie\Effect\Ops;

use Phunkie\Types\Kind;
use Phunkie\Effect\IO\IO;
use Phunkie\Cats\Monad;

trait MonadOps
{
    /**
     * @template B
     * @param callable(mixed):Kind<B>|IO<B> $f
     * @return Kind<B>|IO<B>
     */
    public function flatMap(callable $f): Kind | IO
    {
        return new IO(function () use ($f) {
            $a = ($this->unsafeRun)();
            return $f($a)->unsafeRun();
        });
    }

    /**
     * @return Kind<B>|IO<B> where B is the type parameter of the inner IO
     */
    public function flatten(): Kind | IO
    {
        return $this->flatMap(function ($x) {
            return $x;
        });
    }
}
