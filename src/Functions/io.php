<?php

namespace Phunkie\Effect\Functions\io;

use Phunkie\Effect\IO\IO;

function io(callable $unsafeRun): IO
{
    return new IO($unsafeRun);
} 
