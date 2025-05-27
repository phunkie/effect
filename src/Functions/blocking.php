<?php

namespace Phunkie\Effect\Functions\blocking;

use Phunkie\Effect\Concurrent\Blocking;
use Phunkie\Effect\Concurrent\ExecutionContext;
use Phunkie\Effect\IO\IO;

use function Phunkie\Effect\Functions\io\io;

function blocking(\Closure $thunk, ?ExecutionContext $context = null): IO
{
    return io(new Blocking($thunk, $context));
} 
