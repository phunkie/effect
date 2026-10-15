<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO;

use Closure;

use function Phunkie\Effect\Functions\io\bracket;

/**
 * A bracket as a value: something acquired for the duration of a use step and released after it,
 * whatever way the step ends. Resources compose: flatMap nests them, so the inner one is released
 * first, and use() turns the whole thing back into one IO.
 *
 * @template A
 */
final class Resource
{
    /**
     * @param Closure(callable(A): IO<mixed>): IO<mixed> $use
     */
    private function __construct(private readonly Closure $use)
    {
    }

    /**
     * A resource acquired by an IO and released by a function of what was acquired.
     *
     * @template B
     * @param IO<B> $acquire
     * @param callable(B): IO<mixed> $release
     * @return Resource<B>
     */
    public static function make(IO $acquire, callable $release): self
    {
        return new self(fn (callable $f): IO => bracket($acquire, $f, $release));
    }

    /**
     * A value that needs no acquiring or releasing.
     *
     * @template B
     * @param B $value
     * @return Resource<B>
     */
    public static function pure(mixed $value): self
    {
        return new self(fn (callable $f): IO => $f($value));
    }

    /**
     * An IO's result, as a resource that needs no releasing.
     *
     * @template B
     * @param IO<B> $io
     * @return Resource<B>
     */
    public static function eval(IO $io): self
    {
        return new self(fn (callable $f): IO => $io->flatMap($f));
    }

    /**
     * Acquire, run $f on what was acquired, release, as one IO.
     *
     * @template B
     * @param callable(A): IO<B> $f
     * @return IO<B>
     */
    public function use(callable $f): IO
    {
        return ($this->use)($f);
    }

    /**
     * @template B
     * @param callable(A): B $f
     * @return Resource<B>
     */
    public function map(callable $f): self
    {
        return new self(fn (callable $g): IO => $this->use(fn ($a) => $g($f($a))));
    }

    /**
     * A resource that depends on this one: acquired inside it and released before it.
     *
     * @template B
     * @param callable(A): Resource<B> $f
     * @return Resource<B>
     */
    public function flatMap(callable $f): self
    {
        return new self(fn (callable $g): IO => $this->use(fn ($a) => $f($a)->use($g)));
    }
}
