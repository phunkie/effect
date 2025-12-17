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

use function Phunkie\Effect\Functions\ioapp\arguments;
use function Phunkie\Effect\Functions\ioapp\option;

use Phunkie\Effect\IO\IOApp\Error;
use Phunkie\Effect\IO\IOApp\Options;
use Phunkie\Effect\IO\IOApp\ParsedOptions;
use Phunkie\Types\NonEmptyList;
use Phunkie\Validation\Validation;

/**
 * Base class for IO applications.
 *
 * Extend this class to create your own IO application.
 * The run method must return an IO that will be executed when the application starts.
 * The return value of the IO will be used as the application's exit code.
 *
 * Example Usage:
 * ```php
 * class MyApp extends IOApp
 * {
 *     public function define(): Validation
 *     {
 *         return arguments(
 *             option('f', 'file', 'Input file', Required),
 *             option('v', 'verbose', 'Verbose output', NoInput)
 *         );
 *     }
 *
 *     public function run(?array $args = []): IO
 *     {
 *         return io(function() use ($args) {
 *             $this->parse($args)->fold(
 *                 fn($errors) => $this->handleErrors($errors),
 *                 fn($opts) => $this->program($opts)
 *             );
 *         });
 *     }
 * }
 * ```
 */
abstract class IOApp
{
    /**
     * @return IO<int>
     */
    abstract public function run(?array $args = []): IO;

    /**
     * Defines the CLI arguments for the application.
     * Override this method to provide your own definitions.
     *
     * @return Validation<NonEmptyList<Error>, Options>
     */
    protected function define(): Validation
    {
        return arguments();
    }

    /**
     * Parses the CLI arguments based on the definitions from define().
     *
     * Example:
     * ```php
     * $result = $this->parse($argv);
     * $result->fold(
     *     fn($errors) => echo "Invalid arguments", // $errors is NonEmptyList<Error>
     *     fn($options) => $options->has('verbose') // $options is ParsedOptions
     * );
     * ```
     *
     * @param array $args The raw arguments (usually $argv)
     * @return Validation<NonEmptyList<Error>, ParsedOptions>
     */
    protected function parse(array $args): Validation
    {
        return $this->define()->map(fn ($options) => $options->parse($args));
    }
}
