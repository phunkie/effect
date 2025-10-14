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

/**
 * Creates an IO that will acquire a resource, use it, and then release it.
 * Ensures the release is always called, even if use fails.
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
    return new IO(function () use ($acquire, $use, $release) {
        try {
            $resource = $acquire->unsafeRun();
            try {
                $result = $use($resource)->unsafeRun();
                $release($resource)->unsafeRun();
                return $result;
            } catch (\Throwable $e) {
                $release($resource)->unsafeRun();
                throw $e;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    });
}
