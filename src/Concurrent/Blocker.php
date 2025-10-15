<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Concurrent;

class Blocker
{
    public function __construct(
        private readonly \Closure $thunk,
        private ?ExecutionContext $context = null
    ) {
        if ($this->context === null) {
            $this->context = new FiberExecutionContext();
        }
    }

    public function __invoke(): AsyncHandle
    {
        return $this->context->executeAsync($this->thunk);
    }

    public function runSync(): mixed
    {
        return $this->context?->execute($this->thunk);
    }

    public function blockingContext(): ExecutionContext
    {
        return $this->context ?? new FiberExecutionContext();
    }
}
