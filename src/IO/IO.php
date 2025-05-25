<?php

namespace Phunkie\Effect\IO;

use Phunkie\Effect\Ops\FunctorOps;
use Phunkie\Effect\Ops\ApplicativeOps;
use Phunkie\Effect\Ops\MonadOps;
use Phunkie\Cats\Functor;
use Phunkie\Cats\Applicative;
use Phunkie\Cats\Monad;
use Phunkie\Types\Kind;

/**
 * @template A
 */
class IO implements Functor, Applicative, Monad, Kind
{
    use FunctorOps;
    use ApplicativeOps;
    use MonadOps;

    private $unsafeRun;

    public function __construct(callable $unsafeRun)
    {
        $this->unsafeRun = $unsafeRun;
    }

    public function unsafeRun()
    {
        try {
            return ($this->unsafeRun)();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function handleError(callable $handler): IO
    {
        return new IO(function() use ($handler) {
            try {
                return ($this->unsafeRun)();
            } catch (\Throwable $e) {
                return $handler($e);
            }
        });
    }

    public function getTypeArity(): int
    {
        return 1;
    }

    public function getTypeVariables(): array
    {
        return ['A'];
    }
}
