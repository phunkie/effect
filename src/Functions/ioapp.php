<?php

/*
 * This file is part of Phunkie Effect, A functional effect system for PHP inspired by Cats Effect.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phunkie\Effect\Functions\ioapp;

use Phunkie\Effect\IO\IOApp\Error;
use Phunkie\Effect\IO\IOApp\OptionDefinition;
use Phunkie\Effect\IO\IOApp\OptionFormat;
use Phunkie\Effect\IO\IOApp\Options;
use Phunkie\Types\Either;
use Phunkie\Validation\Validation;

const arguments = "\\Phunkie\\Effect\\Functions\\ioapp\\arguments";
const option = "\\Phunkie\\Effect\\Functions\\ioapp\\option";

const Optional = OptionFormat::Optional;
const Required = OptionFormat::Required;
const Negatable = OptionFormat::Negatable;
const ArrayValues = OptionFormat::ArrayValues;
const NoInput = OptionFormat::NoInput;

function option(string $p1, string|null $p2 = null, string|OptionFormat|null $p3 = null, OptionFormat $p4 = OptionFormat::Optional): Either
{
    try {
        return Right(new OptionDefinition($p1, $p2, $p3, $p4));
    } catch (\Throwable $e) {
        return Left(new Error($e->getMessage()));
    }
}

function arguments(Either ...$definitions): Validation
{
    /** @var Error[] $failures */
    $failures = [];
    /** @var OptionDefinition[] $validDefinitions */
    $validDefinitions = [];

    foreach ($definitions as $d) {
        if ($d->isLeft()) {
            $failures[] = $d->get();
        } else {
            $validDefinitions[] = $d->get();
        }
    }

    if (count($failures) > 0) {
        return Failure(Nel(...$failures));
    }

    return Success(Options::create(...$validDefinitions));
}
