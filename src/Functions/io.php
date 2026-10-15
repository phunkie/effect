<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Functions\io;

use Phunkie\Effect\IO\ExitCase;
use Phunkie\Effect\IO\IO;
use Throwable;

/**
 * Creates an IO effect from a value or a callable.
 *
 * If the input is a callable, it is wrapped in an IO as a thunk (lazy execution).
 * If the input is a value, it is wrapped in an IO that returns that value (pure).
 *
 * @template A
 * @param callable(): A|A $value The side-effecting function or pure value
 * @return IO<A>
 */
const io = "\\Phunkie\\Effect\\Functions\\io\\io";
const IO = "\\Phunkie\\Effect\\Functions\\io\\io";
function io($value): IO
{
    if (is_callable($value)) {
        return new IO($value);
    }

    return new IO(fn () => $value);
}

/**
 * Acquire a resource, use it, release it, whatever way the use step ends.
 *
 * @template A
 * @template B
 * @param IO<A> $acquire
 * @param callable(A): IO<B> $use
 * @param callable(A): IO<mixed> $release
 * @return IO<B>
 */
function bracket(IO $acquire, callable $use, callable $release): IO
{
    return bracketE($acquire, $use, fn ($resource, ?Throwable $error) => $release($resource));
}

/**
 * bracket whose release step is told how the use step ended: ExitCase::Completed, or ExitCase::Error.
 *
 * @template A
 * @template B
 * @param IO<A> $acquire
 * @param callable(A): IO<B> $use
 * @param callable(A, ExitCase): IO<mixed> $release
 * @return IO<B>
 */
function bracketCase(IO $acquire, callable $use, callable $release): IO
{
    return bracketE($acquire, $use, fn ($resource, ?Throwable $error) => $release($resource, null === $error ? ExitCase::Completed : ExitCase::Error));
}

/**
 * bracket whose release step is handed the error the use step raised, or null when it completed.
 * The error is rethrown once the release step has run.
 *
 * @template A
 * @template B
 * @param IO<A> $acquire
 * @param callable(A): IO<B> $use
 * @param callable(A, Throwable|null): IO<mixed> $release
 * @return IO<B>
 */
function bracketE(IO $acquire, callable $use, callable $release): IO
{
    return new IO(function () use ($acquire, $use, $release) {
        $resource = $acquire->unsafeRun();

        try {
            $result = $use($resource)->unsafeRun();
        } catch (Throwable $e) {
            $release($resource, $e)->unsafeRun();

            throw $e;
        }
        $release($resource, null)->unsafeRun();

        return $result;
    });
}

/**
 * A list of effects as one effect of their results, run in order, keys kept.
 *
 * @template A
 * @param array<IO<A>> $ios
 * @return IO<array<A>>
 */
function sequence(array $ios): IO
{
    return new IO(fn () => array_map(fn (IO $io) => $io->unsafeRunSync(), $ios));
}

/**
 * Each value through $f, the resulting effects sequenced.
 *
 * @template A
 * @template B
 * @param array<A> $values
 * @param callable(A): IO<B> $f
 * @return IO<array<B>>
 */
function traverse(array $values, callable $f): IO
{
    return sequence(array_map($f, $values));
}
