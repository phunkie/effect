<?php

namespace Phunkie\Effect\Functions\blocking;

use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ExecutionContext;

use function Phunkie\Effect\Functions\io\io;

use Phunkie\Effect\IO\IO;

const blocking = '\Phunkie\Effect\Functions\blocking\blocking';
function blocking(\Closure $thunk, ?ExecutionContext $context = null): IO
{
    return io(new Blocker($thunk, $context));
}
