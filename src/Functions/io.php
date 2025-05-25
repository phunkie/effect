<?php

namespace Phunkie\Effect\Functions\io;

use Phunkie\Effect\IO\IO;

/**
 * Creates an IO from either a callable or a plain value.
 * 
 * @template A
 * @param callable|A $value
 * @return IO<A>
 */

 const io = "\\Phunkie\\Effect\\Functions\\io\\io";
 const IO = "\\Phunkie\\Effect\\Functions\\io\\io";
function io($value): IO
{
    if (is_callable($value)) {
        return new IO($value);
    }

    return new IO(fn() =>  $value);
} 
