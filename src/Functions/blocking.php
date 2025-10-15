<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Functions\blocking;

use Closure;
use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ExecutionContext;

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;

const blocking = '\Phunkie\Effect\Functions\blocking\blocking';
function blocking(Closure $thunk, ?ExecutionContext $context = null): IO
{
    return io(new Blocker($thunk, $context));
}
