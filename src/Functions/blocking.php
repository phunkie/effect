<?php

namespace Phunkie\Effect\Functions\blocking;

use Phunkie\Effect\Concurrent\Blocker;
use Phunkie\Effect\Concurrent\ExecutionContext;
use Phunkie\Effect\IO\IO;

use function Phunkie\Effect\Functions\io\io;

const blocking = '\Phunkie\Effect\Functions\blocking\blocking';
function blocking(\Closure $thunk, ?ExecutionContext $context = null): IO
{
    return io(new Blocker($thunk, $context));
}
