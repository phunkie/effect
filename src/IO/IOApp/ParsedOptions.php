<?php

namespace Phunkie\Effect\IO\IOApp;

use Phunkie\Types\Either;

class ParsedOptions
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private readonly array $values)
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
