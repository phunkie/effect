<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\IO\IOApp;

use Phunkie\Types\Either;

/**
 * Result of the options parsing process.
 */
class ParsedOptions
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private readonly array $values, public readonly array $args = [])
    {
    }

    /**
     * @return Either<Error, Input>
     */
    public function fetch(string $option): Either
    {
        if (array_key_exists($option, $this->values)) {
            return Right(new Input($this->values[$option]));
        }

        return Left(new Error("Option $option not found"));
    }

    public function has(string $option): bool
    {
        return array_key_exists($option, $this->values);
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}
