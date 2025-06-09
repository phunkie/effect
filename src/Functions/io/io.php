<?php

namespace Phunkie\Effect\Functions\io;

use Phunkie\Effect\IO\IO;

/**
 * Creates an IO that will acquire a resource, use it, and then release it.
 * 
 * @template T
 * @template R
 * @param IO<T> $acquire The IO that acquires the resource
 * @param callable(T): IO<R> $use The function that uses the resource
 * @param callable(T): IO<void> $release The function that releases the resource
 * @return IO<R>
 */
function bracket(IO $acquire, callable $use, callable $release): IO
{
    return $acquire->flatMap(function($resource) use ($use, $release) {
        return $use($resource)->flatMap(function($result) use ($resource, $release) {
            return $release($resource)->map(fn() => $result);
        });
    });
} 
