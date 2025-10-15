<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO;

use Phunkie\Cats\Applicative;
use Phunkie\Cats\Functor;
use Phunkie\Cats\Monad;
use Phunkie\Effect\Cats\Parallel;
use Phunkie\Effect\Concurrent\AsyncHandle;
use Phunkie\Effect\Ops\IO\ApplicativeOps;
use Phunkie\Effect\Ops\IO\FunctorOps;
use Phunkie\Effect\Ops\IO\MonadOps;
use Phunkie\Effect\Ops\IO\ParallelOps;
use Phunkie\Types\Kind;
use Phunkie\Validation\Validation;
use Throwable;

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

    public function unsafeRun(): mixed
    {
        return ($this->unsafeRun)();
    }

    public function unsafeRunSync(): mixed
    {
        $handle = ($this->unsafeRun)();

        if ($handle instanceof AsyncHandle) {
            return $handle->await();
        }

        return $handle;
    }

    public function handleError(callable $handler): IO
    {
        return new IO(function () use ($handler) {
            try {
                return ($this->unsafeRun)();
            } catch (Throwable $e) {
                return $handler($e);
            }
        });
    }

    /**
     * @return IO<Validation<Throwable, A>>
     */
    public function attempt(): IO
    {
        return new IO(fn () => Attempt($this->unsafeRun));
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
