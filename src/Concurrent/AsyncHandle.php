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
 * Represents a handle to an asynchronous computation.
 *
 * An AsyncHandle allows waiting for the result of an asynchronous operation,
 * similar to a Future or Promise in other languages.
 *
 * @template A
 */
interface AsyncHandle
{
    /**
     * Blocks until the asynchronous computation is complete and returns the result.
     *
     * @return A The result of the computation
     */
    public function await(): mixed;
}
