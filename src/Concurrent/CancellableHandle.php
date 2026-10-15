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

/**
 * A handle to a computation that is running on its own, so it can be asked whether it has
 * finished without waiting for it, and stopped when its result is no longer wanted.
 * race, either and timeout need handles of this kind.
 *
 * @template A
 * @extends AsyncHandle<A>
 */
interface CancellableHandle extends AsyncHandle
{
    public function isDone(): bool;

    public function cancel(): void;
}
