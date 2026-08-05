<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\PatternMatching\Referenced;

use Phunkie\Effect\IO\IO as IOType;
use Phunkie\PatternMatching\Referenced\GenericReferenced;

/**
 * Creates a pattern that matches an effect IO and binds the thunk it wraps.
 *
 * This is effect's own IO pattern, distinct from phunkie's Referenced\IO, which
 * targets Phunkie\Cats\IO. It builds on phunkie's GenericReferenced, so it needs
 * phunkie 1.2.
 *
 * Example:
 * ```php
 * $on = pmatch(new IO(fn () => 42));
 * $result = match (true) {
 *     $on(IO($thunk)) => $thunk()  // $thunk is the wrapped effect, so 42
 * };
 * ```
 *
 * @param mixed $thunk Variable that receives the thunk wrapped by the IO
 * @return GenericReferenced Pattern matching an effect IO
 */
function IO(&$thunk): GenericReferenced
{
    return new GenericReferenced(IOType::class, $thunk);
}
