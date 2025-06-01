<?php

namespace Phunkie\Effect\IO;

use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\AsyncHandle;
use Phunkie\Effect\Ops\FunctorOps;
use Phunkie\Effect\Ops\ApplicativeOps;
use Phunkie\Effect\Ops\MonadOps;
use Phunkie\Effect\Ops\ParallelOps;
use Phunkie\Cats\Functor;
use Phunkie\Cats\Applicative;
use Phunkie\Cats\Monad;
use Phunkie\Types\Kind;

/**
 * @template A
 */
class IO implements Functor, Applicative, Monad, Parallel, Kind
{
    use FunctorOps;
    use ApplicativeOps;
    use MonadOps;
    use ParallelOps;

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

    public function unsafeRunSync()
    {
        try {
            $handle = ($this->unsafeRun)();
            
            if ($handle instanceof AsyncHandle) {
                return $handle->await();
            }
            return $handle;

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
